<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\ResetPassword;
use App\Entity\User;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ResetPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){ // Constructeur de l'entity manager permettant de dialoger avec la BDD
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/mot-de-passe-oublie", name="reset_password")
     */
    public function index(Request $request): Response
    {
        if ($this->getUser()) // Redirection si l'utilisateur est connecté
        {
            return $this->redirectToRoute('home');
        }

        if ($request->get('email')){
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $request->get('email')]);
            if ($user)
            {
                // Création de l'entité ResetPassword pour enregistrer en base la demande de modification
                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt(new \DateTime());
                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();

                //Envoi de l'email à l'utilisateur avec un lien permettant de mettre à jour son mot de passe
                $url = $this->generateUrl('update_password', [
                    'token' => $reset_password->getToken()
                ]); // Création de l'url pour la page de reset comprenant le token
                $content = "Bonjour ".$user->getFirstname() .", <br/> Vous avez demandé à réinitialiser votre mot de passe sur la boutique Symfony. <br> <br>";
                $content .= "Merci de bien vouloir cliquer sur le lien suivant pour <a href='".$url."'>mettre à jour votre mot de passe</a>.";
                $mail = new Mail();
                $mail->send($user->getEmail(),$user->getFirstname().' '.$user->getLastname(), 'Réinitialiser votre mot de passe sur la boutique Symfony !', $content);
                $this->addFlash('notice', 'Vous allez recevoir un mail pour réinitialiser votre mot de passe');
            }else{
                $this->addFlash('notice', 'Cet utilisateur est inconnu');
            }
        }

        return $this->render('reset_password/index.html.twig');
    }

    /**
     * @Route("/modifier-mon-mot-de-passe/{token}", name="update_password")
     */
    public function update($token, Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneBy(['token' => $token]);

        if(!$reset_password)
        {
            return $this->redirectToRoute('reset_password');
        }

        // Vérifier si le CreatedAt est inférieur au temps de réinitialisation défini
        $now = new \DateTime();
        if ($now > $reset_password->getCreatedAt()->modify('+ 3 hours'))
        {
            $this->addFlash('notice', 'Votre demande de mot de passe est expirée.');
            return $this->redirectToRoute('reset_password');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $new_password = $form->get('new_password')->getData();
            $password = $hasher->hashPassword($reset_password->getUser(), $new_password);

            $reset_password->getUser()->setPassword($password);
            $this->entityManager->flush();
            $this->addFlash('notice', 'Votre mot de passe a bien été mis à jour');

           return $this->redirectToRoute('app_login');

        }

        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView()
        ]);
    }
}

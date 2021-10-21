<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){ // Constructeur de l'entity manager permettant de dialoger avec la BDD
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/inscription", name="register")
     * @param Request $request
     * @param UserPasswordHasherInterface $hasher
     * @return Response
     */
    public function index(Request $request, UserPasswordHasherInterface $hasher /* Interface de hashage */): Response
    {
        $notification = null;

        $user = new User(); // Instantiation de l'entité nécessaire

        $form = $this->createForm(RegisterType::class, $user); // Création du formulaire à partir de RegisterType

        $form->handleRequest($request); // Récupère la requête envoyée par le formulaire

        if ($form->isSubmitted() && $form->isValid()){ // Vérifie si les valeurs rentrées correspondent aux contraintes et si le formulaire est envoyé
            $user = $form->getData(); // passe les données envoyées par le formulaire dans l'objet user

            $searchEmail = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

            if(!$searchEmail){
                $password = $hasher->hashPassword($user,$user->getPassword()); // Permet de hasher le password entré dans le formulaire

                $user->setPassword($password); // Modifie la password avec le hash généré

                $this->entityManager->persist($user); // Fige la data de l'entité user
                $this->entityManager->flush(); // Exécute la persistance et l'ajoute à la BDD

                // Envoi d'un mail à l'utilisateur
                $mail = new Mail();
                $content = "Bonjour ".$user->getFirstname().",<br/> Bienvenue sur la boutique symfony n'hésitez pas à acheter des trucs ! <br/> <br/>";
                $mail->send($user->getEmail(), $user->getFirstname(),'Bienvenue sur la boutique Symfony !', $content);

                // Notification d'inscription
                $notification ="Votre inscription s'est correctement déroulée, vous pouvez maintenant vous connecter";
            } else {
                $notification = "L'email que vous avez renseigner xiste déjà";
            }


        }

        return $this->render('register/index.html.twig',[
            'form' => $form->createView(), // Créer la vue du formulaire ci-dessus
            'notification' => $notification,
        ]);
    }
}

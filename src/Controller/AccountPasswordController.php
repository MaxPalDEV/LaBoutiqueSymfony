<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AccountPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){ // Constructeur de l'entity manager permettant de dialoger avec la BDD
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/compte/modifier-mon-passe", name="account_password")
     */
    public function index(Request $request, UserPasswordHasherInterface $hasher): Response
    {
        $notification = null;

        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);

        $form->handleRequest($request);
        $old_pwd = $form->get('old_password')->getData();
        if ($form->isSubmitted() && $form->isValid()){
            if ($hasher->isPasswordValid($user, $old_pwd)){
                $new_pwd = $form->get('new_password')->getData();
                $password = $hasher->hashPassword($user, $new_pwd);

                $user->setPassword($password);

                $this->entityManager->flush(); // Exécute la persistance et l'ajoute à la BDD
                $notification = "Votre mot de passe a bien été mis à jour";
            }else{
                $notification = "Votre mot de passe actuell est incorrect";
            }
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}

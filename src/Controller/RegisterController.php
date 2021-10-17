<?php

namespace App\Controller;

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
        $user = new User(); // Instantiation de l'entité nécessaire

        $form = $this->createForm(RegisterType::class, $user); // Création du formulaire à partir de RegisterType

        $form->handleRequest($request); // Récupère la requête envoyée par le formulaire

        if ($form->isSubmitted() && $form->isValid()){ // Vérifie si les valeurs rentrées correspondent aux contraintes et si le formulaire est envoyé
            $user = $form->getData(); // passe les données envoyées par le formulaire dans l'objet user

            $password = $hasher->hashPassword($user,$user->getPassword()); // Permet de hasher le password entré dans le formulaire

            $user->setPassword($password); // Modifie la password avec le hash généré

            $this->entityManager->persist($user); // Fige la data de l'entité user
            $this->entityManager->flush(); // Exécute la persistance et l'ajoute à la BDD
        }

        return $this->render('register/index.html.twig',[
            'form' => $form->createView() // Créer la vue du formulaire ci-dessus
        ]);
    }
}

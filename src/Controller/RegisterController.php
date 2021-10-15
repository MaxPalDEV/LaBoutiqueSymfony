<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    /**
     * @Route("/inscription", name="register")
     */
    public function index(): Response
    {
        $user = new User(); // Instantiation de l'entité nécessaire

        $form = $this->createForm(RegisterType::class, $user); // Création du formulaire à partir de RegisterType

        return $this->render('register/index.html.twig',[
            'form' => $form->createView() // Créer la vue du formulaire ci-dessus
        ]);
    }
}

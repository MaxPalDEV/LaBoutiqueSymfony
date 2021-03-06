<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Address;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountAddressController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){ // Constructeur de l'entity manager permettant de dialoger avec la BDD
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/compte/address", name="account_address")
     */
    public function index(): Response
    {

        return $this->render('account/address.html.twig',[
        ]);
    }

    /**
     * @Route("/compte/ajouter-une-adresse", name="account_address_add")
     */
    public function add(Cart $cart, Request $request)
    {
        $address= new Address();
        $form = $this->createForm(AddressType::class, $address); // Création du formulaire

        $form->handleRequest($request); // Récupération de la requête

        if($form->isSubmitted() && $form->isValid()){
            $address->setUser($this->getUser());
            $this->entityManager->persist($address);
            $this->entityManager->flush();
            if ($cart->get()){ // Si le panier contient quelque chose
                return $this->redirectToRoute('order');
            }else{
                return $this->redirectToRoute('account_address');
            }

        }

        return $this->render('account/address_add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/compte/modifier-une-adresse/{id}", name="account_address_edit")
     */
    public function edit(Request $request, $id)
    {
        $address= $this->entityManager->getRepository(Address::class)->findOneBy(['id' => $id]);

        if (!$address || $address->getUser() != $this->getUser())
        {
            return $this->redirectToRoute('account_address');
        }


        $form = $this->createForm(AddressType::class, $address); // Création du formulaire

        $form->handleRequest($request); // Récupération de la requête

        if($form->isSubmitted() && $form->isValid()){
            $this->entityManager->flush();
            return $this->redirectToRoute('account_address');
        }

        return $this->render('account/address_add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/compte/supprimer-une-adresse/{id}", name="account_address_delete")
     */
    public function delete($id)
    {
        $address= $this->entityManager->getRepository(Address::class)->findOneBy(['id' => $id]);

        if (!$address || $address->getUser() == $this->getUser())
        {
            $this->entityManager->remove($address);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('account_address');


    }
}

<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Classe\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){ // Constructeur de l'entity manager permettant de dialoger avec la BDD
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_validate")
     */
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['stripeSessionId' => $stripeSessionId]);

        if (!$order || $order->getUser() != $this->getUser()) // Vérifie que la commande existe et qu'elle correspond à l'utilisateur current
        {
            return $this->redirectToRoute('home');
        }

        if (!$order->getIsPaid()) // Si la commande est en statut non payé
        {
            $cart->remove(); //Vider le panier utilisateur

            $order->setIsPaid(1);
            $this->entityManager->flush();

            // Envoi d'un mail à l'utilisateur
            $mail = new Mail();
            $content = "Bonjour ".$order->getUser()->getFirstname().",<br/> Merci pour votre commande ! <br/> <br/> Elle porte le numéro ".$order->getReference()." et comprend des articles pour un montant total de ".$order->getTotal()."€ + frais de ports de ".$order->getCarrierPrice()."€";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(),'Merci pour votre commande dans la boutique Symfony', $content);

        }
        return $this->render('order_success/index.html.twig', [
            'order' => $order
        ]);
    }
}

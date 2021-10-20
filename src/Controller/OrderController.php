<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Form\OrderType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){ // Constructeur de l'entity manager permettant de dialoger avec la BDD
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/commande", name="order")
     */
    public function index(Cart $cart, Request $request): Response
    {
        if(!$this->getUser()->getAddresses()->getValues()) // Si aucune adresse n'existe pour l'utilisateur
        {
            return $this->redirectToRoute('account_address_add'); // Redirection vers la création d'adresses
        }

        $form = $this->createForm(OrderType::class, null,[
            'user' => $this->getUser() // Permet de récupérer l'utilisateur côté formulaire pour n'avoir que les adresses liés à l'utilisateur courant
        ]);

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'cart' => $cart->getFull() // Envoi du panier pour le récap de la commande
        ]);
    }

    /**
     * @Route("/commande/recapitulatif", name="order_recap", methods={"POST"})
     * @param Cart $cart
     * @param Request $request
     * @return Response
     */
    public function add(Cart $cart, Request $request): Response
    {
        if(!$this->getUser()->getAddresses()->getValues()) // Si aucune adresse n'existe pour l'utilisateur
        {
            return $this->redirectToRoute('account_address_add'); // Redirection vers la création d'adresses
        }

        $form = $this->createForm(OrderType::class, null,[
            'user' => $this->getUser() // Permet de récupérer l'utilisateur côté formulaire pour n'avoir que les adresses liés à l'utilisateur courant
        ]);

        $form->handleRequest($request); // Récupération de la requête

        if($form->isSubmitted() && $form->isValid()){
            $date = new \DateTime();
            $carrier = $form->get('carriers')->getData(); // Récupère les informations du transporteur sélectionné dans le formulaire
            $delivery = $form->get('addresses')->getData(); // Récupère les informations de l'adresse sélectionnée dans le formulaire
            $delivery_content = $delivery->getFirstname().' '.$delivery->getLastname();
            $delivery_content .= '<br/>'.$delivery->getPhone();

            if ($delivery->getCompany()){
                $delivery_content .= '<br/>'.$delivery->getCompany();
            }

            $delivery_content .= '<br/>'.$delivery->getAddress();
            $delivery_content .= '<br/>'.$delivery->getPostal().' '.$delivery->getCity();
            $delivery_content .= '<br/>'.$delivery->getCountry();

            // Enregistrer la commande Order()
            $order = new Order();
            $order->setUser($this->getUser());
            $order->setCreatedAt($date);
            $order->setCarrierName($carrier->getName());
            $order->setCarrierPrice($carrier->getPrice());
            $order->setDelivery($delivery_content);
            $order->setIsPaid(0);

            $this->entityManager->persist($order);

            // Enregistrer la commande OrderDetails()
            foreach ($cart->getFull() as $product){
                $orderDetails = new OrderDetails();
                $orderDetails->setMyOrder($order); // Lien
                $orderDetails->setProduct($product['product']->getName());
                $orderDetails->setQuantity($product['quantity']);
                $orderDetails->setPrice($product['product']->getPrice());
                $orderDetails->setTotal($product['product']->getPrice() * $product['quantity']);
                $this->entityManager->persist($orderDetails);
            }

            $this->entityManager->flush();

            return $this->render('order/add.html.twig', [
                'form' => $form->createView(),
                'cart' => $cart->getFull(), // Envoi du panier pour le récap de la commande
                'carrier' => $carrier,
                'delivery' => $delivery_content
            ]);
        }

        return $this->redirectToRoute('cart');
    }
}

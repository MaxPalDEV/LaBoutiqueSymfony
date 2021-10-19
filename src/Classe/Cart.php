<?php

namespace App\Classe;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Cart
{
    private $session;
    private $entityManager;

    public function __construct(SessionInterface $session, EntityManagerInterface $entityManager)
    {
        $this->session = $session; // implémente le session interface utilisable dans la classe
        $this->entityManager = $entityManager;
    }

    /**
     * Fonction d'ajout dans le panier
     * @param $id
     */
    public function add($id)
    {
        $cart = $this->session->get('cart', []); // Stocke l'objet cart enregistré

        if (!empty($cart[$id])) // Si produit correspondant à l'id est déjà dans le panier
        {
            $cart[$id]++; // Augmente la quantité
        } else {
            $cart[$id] = 1; // Ajoute au panier
        }

        $this->session->set('cart', $cart);
    }

    /**
     * Fonction de decrease dans le panier
     * @param $id
     */
    public function decrease($id)
    {
        $cart = $this->session->get('cart', []); // Stocke l'objet cart enregistré

        if ($cart[$id] > 1){
            $cart[$id]--;

        }else{
            unset($cart[$id]);
        }
        $this->session->set('cart', $cart);
    }

    /**
     * Récupère le contenu du panier
     * @return mixed
     */
    public function get()
    {
        return $this->session->get('cart');
    }

    /**
     * Supprime le contenu du panier
     * @return mixed
     */
    public function remove()
    {
        return $this->session->remove('cart');
    }

    /**
     * Fonction de suppression dun item du panier
     * @param $id
     * @return mixed
     */
    public function delete($id){
        $cart = $this->session->get('cart', []);

        unset($cart[$id]);

        return $this->session->set('cart', $cart);;
    }

    /**
     * Fonction de récupération de l'intégralité du panier
     * @return array
     */
    public function getFull(){
        $cartComplete = [];

        if ($this->get()){
            foreach ($this->get() as $id => $quantity){
                $product_object = $this->entityManager->getRepository(Product::class)->findOneBy(["id" => $id]); // Va chercher le produit dans la base de données

                if (!$product_object) // Si le produit n'existe pas
                {
                    $this->delete($id); // Suppression du produit
                    continue; // Quitte le foreach
                }

                $cartComplete[] = [
                    'product' => $product_object,
                    'quantity' => $quantity
                ];
            }
        }

        return $cartComplete;
    }
}
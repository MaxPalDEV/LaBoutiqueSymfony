<?php

namespace App\Controller;

use App\Classe\Filter;
use App\Entity\Product;
use App\Form\FilterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){ // Constructeur de l'entity manager permettant de dialoger avec la BDD
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/nos-produits", name="products")
     */
    public function index(Request $request): Response
    {

        // Affichage du formulaire des filtres
        $search = new Filter();
        $form = $this->createForm(FilterType::class, $search);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $products = $this->entityManager->getRepository(Product::class)->findWithSearch($search);
        }else{
            // Recherche de l'ensemble des produits
            $products = $this->entityManager->getRepository(Product::class)->findAll();
        }

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/produit/{slug}", name="product")
     */
    public function show($slug): Response
    {
        $product = $this->entityManager->getRepository(Product::class)->findOneBy(['slug' => $slug]); // Vient chercher le produit par son slug

        if (!$product){
            $this->redirectToRoute('products'); // Renvoi aux produits si auucun produit n'est trouvé
        }

        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }
}

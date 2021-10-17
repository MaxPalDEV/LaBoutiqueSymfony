<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }


    public function configureFields(string $pageName): iterable // Configure les champs à afficher dans la création
    {
        return [
            TextField::new('name'),
            SlugField::new('slug')->setTargetFieldName('name'), // Définit la target du slug
            ImageField::new('illustration')
                ->setBasePath('uploads/') // Définit le dossier d'enregistrement des images
                ->setUploadDir('public/uploads/') // Chemin vers le dossier d'upload
                ->setUploadedFileNamePattern('[randomhash].[extension]') // Définition de nom d'enregisrement du fichier
                ->setRequired(false), // Set le requirement de l'image
            TextField::new('subtitle'),
            TextareaField::new('description'),
            MoneyField::new('price')->setCurrency('EUR'),
            AssociationField::new('category') // Définit le choix de la catégorie (Ajouter une fonction __tostring pour l'entité)
        ];
    }
}

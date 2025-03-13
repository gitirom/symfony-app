<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductsController extends AbstractController
{
    /**
     * @Route("/products", name="products_index")
     */
    public function index(): Response
    {
        // You can fetch products from the database here
        $products = [
            ['id' => 1, 'name' => 'Product 1', 'price' => 100, 'image' => 'https://shop.etivaz-aop.ch/wp-content/uploads/2021/01/pull.jpg'],
            ['id' => 2, 'name' => 'Product 2', 'price' => 200, 'image' => 'https://shop.etivaz-aop.ch/wp-content/uploads/2021/01/pull.jpg'],
            ['id' => 3, 'name' => 'Product 3', 'price' => 200, 'image' => 'https://shop.etivaz-aop.ch/wp-content/uploads/2021/01/pull.jpg'],
            ['id' => 4, 'name' => 'Product 4', 'price' => 200, 'image' => 'https://shop.etivaz-aop.ch/wp-content/uploads/2021/01/pull.jpg'],
            ['id' => 5, 'name' => 'Product 5', 'price' => 200, 'image' => 'https://shop.etivaz-aop.ch/wp-content/uploads/2021/01/pull.jpg'],
            ['id' => 6, 'name' => 'Product 6', 'price' => 200, 'image' => 'https://shop.etivaz-aop.ch/wp-content/uploads/2021/01/pull.jpg'],
        ];

        return $this->render('products/index.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * @Route("/products/{id}", name="products_show")
     */
    public function show(int $id): Response
    {
        // You can fetch a single product from the database here
        $product = ['id' => $id, 'name' => 'Product ' . $id, 'price' => 100 * $id];

        return $this->render('products/show.html.twig', [
            'product' => $product,
        ]);
    }
}
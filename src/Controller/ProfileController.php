<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_USER')]
#[Route('/profile')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'app_profile')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/products', name: 'app_profile_products')]
    public function products(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy(['user' => $this->getUser()]);
        
        return $this->render('profile/products.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/settings', name: 'app_profile_settings')]
    public function settings(): Response
    {
        return $this->render('profile/settings.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/products/new', name: 'app_profile_products_new')]
    public function newProduct(
        Request $request, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        if (!file_exists($this->getParameter('products_directory'))) {
            mkdir($this->getParameter('products_directory'), 0777, true);
        }

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Handle file upload
                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                    try {
                        $imageFile->move(
                            $this->getParameter('products_directory'),
                            $newFilename
                        );
                        $product->setImage($newFilename);
                    } catch (\Exception $e) {
                        $this->addFlash('error', '❌ Failed to upload image. Please try again.');
                        return $this->redirectToRoute('app_profile_products_new');
                    }
                } else {
                    $this->addFlash('error', '❌ Please upload a product image.');
                    return $this->redirectToRoute('app_profile_products_new');
                }

                $product->setUser($this->getUser());
                // Remove this line since createdAt is set in constructor
                // $product->setCreatedAt(new \DateTimeImmutable());
                
                $entityManager->persist($product);
                $entityManager->flush();

                $this->addFlash('success', '✅ Product "' . $product->getName() . '" was created successfully!');
                return $this->redirectToRoute('app_profile_products');

            } catch (\Exception $e) {
                $this->addFlash('error', '❌ An error occurred while creating the product. Please try again.');
                return $this->redirectToRoute('app_profile_products_new');
            }
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', '❌ Please check your input and try again.');
        }

        return $this->render('profile/product_form.html.twig', [
            'form' => $form->createView(),
            'is_edit' => false,
        ]);
    }

    #[Route('/products/{id}/edit', name: 'app_profile_products_edit')]
    public function editProduct(
        Request $request,
        int $id,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $product = $productRepository->find($id);
        
        if (!$product) {
            $this->addFlash('error', '❌ Product not found.');
            return $this->redirectToRoute('app_profile_products');
        }

        // Check if the current user owns this product
        if ($product->getUser() !== $this->getUser()) {
            $this->addFlash('error', '❌ You can only edit your own products.');
            return $this->redirectToRoute('app_profile_products');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Handle file upload if new image is provided
                $imageFile = $form->get('imageFile')->getData();
                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                    try {
                        $imageFile->move(
                            $this->getParameter('products_directory'),
                            $newFilename
                        );
                        
                        // Delete old image if exists
                        if ($product->getImage()) {
                            $oldImagePath = $this->getParameter('products_directory').'/'.$product->getImage();
                            if (file_exists($oldImagePath)) {
                                unlink($oldImagePath);
                            }
                        }
                        
                        $product->setImage($newFilename);
                    } catch (\Exception $e) {
                        $this->addFlash('error', '❌ Failed to upload image. Please try again.');
                        return $this->redirectToRoute('app_profile_products_edit', ['id' => $product->getId()]);
                    }
                }

                $entityManager->flush();
                $this->addFlash('success', '✅ Product "' . $product->getName() . '" was updated successfully!');
                return $this->redirectToRoute('app_profile_products');

            } catch (\Exception $e) {
                $this->addFlash('error', '❌ An error occurred while updating the product. Please try again.');
                return $this->redirectToRoute('app_profile_products_edit', ['id' => $product->getId()]);
            }
        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', '❌ Please check your input and try again.');
        }

        return $this->render('profile/product_form.html.twig', [
            'form' => $form->createView(),
            'is_edit' => true,
            'product' => $product,
        ]);
    }

    #[Route('/products/{id}/delete', name: 'app_profile_products_delete', methods: ['POST'])]
    public function deleteProduct(
        Request $request,
        int $id,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $product = $productRepository->find($id);
        
        if (!$product) {
            $this->addFlash('error', '❌ Product not found.');
            return $this->redirectToRoute('app_profile_products');
        }

        // Check if the current user owns this product
        if ($product->getUser() !== $this->getUser()) {
            $this->addFlash('error', '❌ You can only delete your own products.');
            return $this->redirectToRoute('app_profile_products');
        }

        // Verify CSRF token
        if (!$this->isCsrfTokenValid('delete-product-'.$product->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', '❌ Invalid security token.');
            return $this->redirectToRoute('app_profile_products');
        }

        try {
            // Delete the image file if it exists
            if ($product->getImage()) {
                $imagePath = $this->getParameter('products_directory').'/'.$product->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            $entityManager->remove($product);
            $entityManager->flush();

            $this->addFlash('success', '✅ Product "' . $product->getName() . '" was deleted successfully!');
        } catch (\Exception $e) {
            $this->addFlash('error', '❌ An error occurred while deleting the product.');
        }

        return $this->redirectToRoute('app_profile_products');
    }
}







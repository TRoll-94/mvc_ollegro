<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use App\Form\CategorySelectType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('PUBLIC_ACCESS')]
class StoreController extends AbstractController
{

    #[Route('/', name: 'app_store_index')]
    public function num(Request $request, UserRepository $userRepository, CategoryRepository $categoryRepository,
                        ProductRepository $productRepository,
                        LoggerInterface $logger
    ): Response
    {
        $category_selected = $request->get('category_select')['category'] ?? null;
        if ($category_selected!=null) {
            $products = $productRepository->findBy(['category' => $category_selected]);
        } else {
            $products = $productRepository->findAll();
        }

        $category_select = $categoryRepository->findAll();
        $category_select_form = $this->createForm(CategorySelectType::class, $category_select, options: ['data_class'=>null]);
        $category_select_form->handleRequest($request);

        $user = $this->getUser();
        $is_anon = !$user instanceof UserInterface;

        return $this->render('store/main.html.twig', [
            'is_anon' => $is_anon,
            'category_select_form' => $category_select_form,
            'products' => $products,
        ]);
    }

    #[Route('/product/{id}', name: 'app_store_product_index')]
    public function product_page(Request $request, Product $product, UserRepository $userRepository,
                                 CategoryRepository $categoryRepository,
                                    ProductRepository $productRepository,
                            LoggerInterface $logger
    ): Response
    {
        $user = $this->getUser();
        $is_anon = !$user instanceof UserInterface;
        $other_products = $productRepository->productsWithTheSameSku($product);

        return $this->render('store/product_details.html.twig', [
            'is_anon' => $is_anon,
            'product' => $product,
            'other_products' => $other_products,
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED')]
    #[Route('product/buy/{id}', name: "app_store_product_buy")]
    public function product_buy(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $cart = new Cart();
        $cart -> setProduct($product);
        $cart -> setOwner($user);
        $entityManager -> persist($cart);
        $entityManager -> flush();

        $this->addFlash('success_buy', 'The purchase was successful, please wait for the shipment');
        $product->setTotal($product->getTotal()-1);
        $product->setTotalReserved($product->getTotalReserved()+1);
        $entityManager->persist($product);
        $entityManager->flush();

        return $this->redirectToRoute('app_store_product_index', ['id'=>$product->getId()]);
    }

}
<?php

namespace App\Controller;

use App\Form\CategorySelectType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
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
}
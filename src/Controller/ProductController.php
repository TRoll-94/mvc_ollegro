<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ProductValidationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductRepository $productRepository,
        ProductValidationService $productValidationService,
        LoggerInterface $logger,
    ): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        $data = $request->get('product');
        $is_valid = $productValidationService->validate($product, $data, function ($key, $msg) {
            $this->addFlash($key, $msg);
        });

        if ($is_valid && $form->isSubmitted() && $form->isValid()) {
            $productRepository->save($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, ProductRepository $productRepository,
        ProductValidationService $productValidationService): Response
    {
        $isCheck = $request->request->get('_type', false);
        if ($isCheck) {
            $product = new Product();
        }
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        $data = $request->get('product');
        $is_valid = $productValidationService->validate($product, $data, function ($key, $msg) {
            $this->addFlash($key, $msg);
        });

        if ($is_valid && $form->isSubmitted() && $form->isValid()) {
            $productRepository->save($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product, true);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}

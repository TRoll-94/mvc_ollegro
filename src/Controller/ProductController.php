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
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use function Symfony\Component\Translation\t;

#[IsGranted('ROLE_MERCHANT')]
#[Route('/merchant/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        $user = $this->getUser();
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findByUser($user),
            'merchant' => $this->getUser()
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
            'merchant' => $this->getUser()
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'merchant' => $this->getUser()
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, ProductRepository $productRepository,
        ProductValidationService $productValidationService): Response
    {
        $user = $this->getUser();
        if (!$productRepository->isProductOwner($product, $user)) {
            throw $this->createAccessDeniedException('Access denied');
        }
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
            'merchant' => $this->getUser()
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Product $product, ProductRepository $productRepository,
        LoggerInterface $logger): Response
    {
        $user = $this->getUser();
        if (!$productRepository->isProductOwner($product, $user)) {
            throw $this->createAccessDeniedException('Access denied');
        }
        $logger->error("-----------------------------------------------");
        $logger->error("RS: {$productRepository->countPurchases($product)}");
        if ($productRepository->countPurchases($product) != 0) {
            $this->addFlash('product_delete', 'Submit all purchases first');
            return $this->redirectToRoute('app_product_show', ['id'=>$product->getId()], Response::HTTP_SEE_OTHER);
        }
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product, true);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/csv', name: 'app_product_to_csv', methods: ['GET'])]
    public function exportProductsToCsv(Request $request, ProductRepository $productRepository): Response
    {
        $user = $this->getUser();
        $response = new StreamedResponse(function () use ($user, $productRepository) {
            $handle = fopen('php://output', 'w');

            $headers = ['ID', 'SKU', 'Name', 'Price', 'Description', 'Total', 'Total Reserved'];
            fputcsv($handle, $headers);

            $products = $productRepository->findByUser($user);

            foreach ($products as $product) {
                $data = [
                    $product->getId(),
                    $product->getSku(),
                    $product->getName(),
                    $product->getPrice(),
                    $product->getDescription(),
                    $product->getTotal(),
                    $product->getTotalReserved(),
                ];

                fputcsv($handle, $data);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="products.csv"');

        return $response;
    }

}

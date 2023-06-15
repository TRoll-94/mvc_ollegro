<?php

namespace App\Controller;

use App\Entity\ProductProperty;
use App\Form\ProductPropertyType;
use App\Repository\ProductPropertyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MERCHANT')]
#[Route('/merchant/product/property')]
class ProductPropertyController extends AbstractController
{
    #[Route('/', name: 'app_product_property_index', methods: ['GET'])]
    public function index(ProductPropertyRepository $productPropertyRepository): Response
    {
        return $this->render('product_property/index.html.twig', [
            'product_properties' => $productPropertyRepository->findAll(),
            'merchant' => $this->getUser()
        ]);
    }

    #[Route('/new', name: 'app_product_property_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductPropertyRepository $productPropertyRepository): Response
    {
        $productProperty = new ProductProperty();
        $form = $this->createForm(ProductPropertyType::class, $productProperty);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productPropertyRepository->save($productProperty, true);

            return $this->redirectToRoute('app_product_property_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product_property/new.html.twig', [
            'product_property' => $productProperty,
            'form' => $form,
            'merchant' => $this->getUser()
        ]);
    }

    #[Route('/{id}', name: 'app_product_property_show', methods: ['GET'])]
    public function show(ProductProperty $productProperty): Response
    {
        return $this->render('product_property/show.html.twig', [
            'product_property' => $productProperty,
            'merchant' => $this->getUser()
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_property_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProductProperty $productProperty, ProductPropertyRepository $productPropertyRepository): Response
    {
        $form = $this->createForm(ProductPropertyType::class, $productProperty);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productPropertyRepository->save($productProperty, true);

            return $this->redirectToRoute('app_product_property_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product_property/edit.html.twig', [
            'product_property' => $productProperty,
            'form' => $form,
            'merchant' => $this->getUser()
        ]);
    }

    #[Route('/{id}', name: 'app_product_property_delete', methods: ['POST'])]
    public function delete(Request $request, ProductProperty $productProperty, ProductPropertyRepository $productPropertyRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$productProperty->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('app_product_property_index', [], Response::HTTP_SEE_OTHER);
        }

        $total_products = $productPropertyRepository->getProductCount($productProperty->getId());
        if ($total_products!=0) {
            $this->addFlash('verify_product_property_delete', "Cannot delete product property because it has products: {$total_products}");
            return $this->redirectToRoute('app_product_property_index', ['id'=>$productProperty->getId()], Response::HTTP_SEE_OTHER);
        }

        $productPropertyRepository->remove($productProperty, true);
        return $this->redirectToRoute('app_product_property_index', [], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MERCHANT')]
#[Route('/merchant/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'app_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
            'merchant' => $this->getUser()
        ]);
    }

    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CategoryRepository $categoryRepository): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categoryRepository->save($category, true);

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form,
            'merchant' => $this->getUser()
        ]);
    }

    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
            'merchant' => $this->getUser()
        ]);
    }
    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, CategoryRepository $categoryRepository): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categoryRepository->save($category, true);

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
            'merchant' => $this->getUser()
        ]);
    }
    #[Route('/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(
        Request $request, Category $category, CategoryRepository $categoryRepository,
    ): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        $total_products = $categoryRepository->getProductCount($category->getId());
        if ($total_products!=0) {
            $this->addFlash('verify_category_delete', "Cannot delete group because it has products: {$total_products}");
            return $this->redirectToRoute('app_category_delete', ['id'=>$category->getId()], Response::HTTP_SEE_OTHER);
        }
        $total_properties = $categoryRepository->getProductPropertiesCount($category->getId());
        if ($total_properties!=0) {
            $this->addFlash('verify_category_delete', "Cannot delete group because it has products properties: {$total_properties}");
            return $this->redirectToRoute('app_category_delete', ['id'=>$category->getId()], Response::HTTP_SEE_OTHER);
        }

        $categoryRepository->remove($category, true);
        return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
    }
}

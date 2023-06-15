<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_MERCHANT')]
class MerchantController extends AbstractController
{
    #[Route('/merchant/', name: 'app_merchant_index')]
    public function num(Request $request, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        return $this->render('merchant/main.html.twig', [
            'merchant' => $user,
            'merchant_repo' => $userRepository
        ]);
    }

    #[Route('/merchant/purchases', name: 'app_merchant_purchase_index')]
    public function purchase_index(Request $request, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        return $this->render('merchant/purchases.html.twig', [
            'purchases' => $userRepository->findCartsByUser($user),
            'merchant' => $user
        ]);
    }

    #[Route('/merchant/purchases/{id}/realise', name: 'app_merchant_purchase_realise')]
    public function purchase_realise(Request $request, Cart $cart, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $this->addFlash('success', 'The purchase was successful send!');
        $product = $cart->getProduct();
        $product->setTotalReserved($product->getTotalReserved()-1);
        $entityManager->persist($product);
        $entityManager->flush();
        $entityManager->remove($cart);
        $entityManager->flush();

        return $this->redirectToRoute('app_merchant_purchase_index');
    }

}
<?php

namespace App\Controller;

use App\Repository\UserRepository;
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
}
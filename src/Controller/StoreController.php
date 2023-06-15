<?php

namespace App\Controller;

use App\Repository\UserRepository;
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
    public function num(Request $request, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $is_anon = !$user instanceof UserInterface;

        return $this->render('store/main.html.twig', [
            'is_anon' => $is_anon,
        ]);
    }
}
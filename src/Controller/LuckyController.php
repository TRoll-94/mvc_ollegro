<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use App\Service\MessageGenerator;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class LuckyController extends AbstractController
{
    #[Route('/lucky/{n1}/{n2}')]
    public function number(
        int $n1,
        int $n2,
        #[MapQueryParameter] string $name,
        MessageGenerator $messageGenerator,
        MailerInterface $mailer,
        LoggerInterface $logger
    ): JsonResponse
    {
        $number = random_int($n1, $n2);
        if (!$name) $name = 'Not set!';

        $email = (new Email())
            ->from('mm@forsalescrm.com')
            ->to('ezhiktv50@gmail.com')
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        try {
            $logger->error("STAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAARt");
            $mailer->send($email);
            $logger->error("EEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEEE");
        } catch (TransportExceptionInterface $e) {
            $logger->error($e);
        }

        return $this->json([
            'name' => $name,
            'min' => $n1,
            'max' => $n2,
            'happy' => $messageGenerator->getHappyMessage(),
            'result' => $number,
        ]);
    }

    #[Route('/lucky/')]
    #[IsGranted('IS_AUTHENTICATED')]
    public function num(): JsonResponse
    {
        $user = $this->getUser();
        return $this->json([
            'name' => $user->getName(),
            'rules' => $user->getRoles(),
        ]);
    }
}

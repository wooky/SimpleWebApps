<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
  #[Route('/', name: 'index', methods: ['GET'])]
  public function index(): Response
  {
    return $this->render('index/index.html.twig');
  }

  #[Route('/navbar/{ulid}', name: 'navbar', methods: ['GET'])]
  public function navbar(string $ulid = ''): Response
  {
    return $this->render('index/navbar.html.twig', ['ulid' => $ulid]);
  }
}

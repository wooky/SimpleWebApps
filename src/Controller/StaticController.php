<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(name: 'static_')]
class StaticController extends AbstractController
{
  #[Route('/calculator', name: 'calculator', methods: ['GET'])]
  public function calculator(): Response
  {
    return $this->render('calculator/index.html.twig');
  }
}

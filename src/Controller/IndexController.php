<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use SimpleWebApps\Entity\User;
use SimpleWebApps\EventBus\EventStreamRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function assert;
use function is_string;

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

  #[Route('/events', name: 'events', methods: ['GET'])]
  public function events(Request $request, EventStreamRenderer $renderer): Response
  {
    $topicsStr = $request->query->get('topics');
    assert(is_string($topicsStr) && !empty($topicsStr));
    $topics = explode(',', $topicsStr);
    $user = $this->getUser();
    assert($user instanceof User);
    $userId = $user->getId();
    assert(null !== $userId);

    return $renderer->createResponse((string) $userId, $topics);
  }
}

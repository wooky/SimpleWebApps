<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use SimpleWebApps\Auth\AuthenticatedUser;
use SimpleWebApps\EventBus\EventStreamRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

use function assert;
use function is_string;

class IndexController extends AbstractController
{
  #[Route('/', name: 'index', methods: ['GET'])]
  public function index(): Response
  {
    return $this->render('index/index.html.twig');
  }

  #[Route('/navbar/{username}', name: 'navbar', methods: ['GET'])]
  #[Cache(maxage: 3600)]
  public function navbar(string $username = ''): Response
  {
    return $this->render('index/navbar.html.twig', ['username' => $username]);
  }

  #[Route('/events', name: 'events', methods: ['GET'])]
  public function events(
    Request $request,
    EventStreamRenderer $renderer,
    #[CurrentUser()] AuthenticatedUser $authenticatedUser,
  ): Response {
    $topicsStr = $request->query->get('topics');
    assert(is_string($topicsStr) && !empty($topicsStr));
    $topics = explode(',', $topicsStr);

    return $renderer->createResponse($authenticatedUser, $topics);
  }
}

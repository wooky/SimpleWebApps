<?php

namespace SimpleWebApps\Controller;

use SimpleWebApps\Entity\User;
use SimpleWebApps\EventBus\EventBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/events', name: 'events_')]
class EventsController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(EventBusInterface $eventBus): Response
    {
        $user = $this->getUser();
        assert($user instanceof User);
        $userId = $user->getId();
        assert($userId !== null);
        $response = new StreamedResponse(function() use ($eventBus, $userId) {
            \session_write_close();
            @\ob_flush();
            \flush();
            foreach ($eventBus->get($userId) as $payload) {
                echo 'data: ' . $payload . "\n\n";
                @\ob_flush();
                \flush();
            }
        });
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');
        return $response;
    }
}

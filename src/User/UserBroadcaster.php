<?php

declare(strict_types=1);

namespace SimpleWebApps\User;

use SimpleWebApps\Entity\User;
use SimpleWebApps\EventBus\Event;
use SimpleWebApps\EventBus\EventBusInterface;
use SimpleWebApps\EventBus\EventScope;
use SimpleWebApps\Repository\UserRepository;
use SimpleWebApps\User\Event\UsernameChangedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Twig\Environment;
use Twig\TemplateWrapper;

#[AsEventListener(event: UsernameChangedEvent::class, method: 'onUsernameChanged')]
final readonly class UserBroadcaster
{
  private const STREAM_TEMPLATE = 'user/stream.html.twig';

  private TemplateWrapper $template;

  public function __construct(
    private UserRepository $userRepository,
    private EventBusInterface $eventBus,
    Environment $twig,
  ) {
    $this->template = $twig->load(self::STREAM_TEMPLATE);
  }

  public function onUsernameChanged(UsernameChangedEvent $event): void
  {
    $affectedUsers = $this->userRepository->getAllInterestedParties($event->user);
    $userIds = array_map(fn (User $user) => (string) $user->getId(), $affectedUsers);
    $payload = $this->template->renderBlock('username_changed', ['user' => $event->user]);
    $this->eventBus->post(new Event($userIds, Event::MESSAGE, $payload, EventScope::AllTopics));
  }
}

<?php

declare(strict_types=1);

namespace SimpleWebApps\User;

use Psr\Log\LoggerInterface;
use SimpleWebApps\Common\TurboStreamAction;
use SimpleWebApps\Common\TurboStreamRenderer;
use SimpleWebApps\Entity\User;
use SimpleWebApps\EventBus\Event;
use SimpleWebApps\EventBus\EventBusInterface;
use SimpleWebApps\EventBus\EventScope;
use SimpleWebApps\Repository\UserRepository;
use SimpleWebApps\User\Event\UsernameChangedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: UsernameChangedEvent::class, method: 'onUsernameChanged')]
final class UserBroadcaster
{
  public function __construct(
    private UserRepository $userRepository,
    private EventBusInterface $eventBus,
    private TurboStreamRenderer $renderer,
    private LoggerInterface $logger,
  ) {
    // Do nothing.
  }

  public function onUsernameChanged(UsernameChangedEvent $event): void
  {
    $affectedUsers = $this->userRepository->getAllInterestedParties($event->user);
    $userIds = array_map(fn (User $user) => (string) $user->getId(), $affectedUsers);
    $payload = $this->renderer->renderTwigComponentClass(
      TurboStreamAction::Replace,
      UsernameComponent::htmlClass($event->user),
      UsernameComponent::NAME,
      ['user' => $event->user],
    );
    $this->eventBus->post(new Event($userIds, TurboStreamRenderer::MESSAGE, $payload, EventScope::AllTopics));
  }
}

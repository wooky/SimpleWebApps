<?php

declare(strict_types=1);

namespace SimpleWebApps\Relationship;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use SimpleWebApps\Common\TurboStreamAction;
use SimpleWebApps\Common\TurboStreamRenderer;
use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\EventBus\Event;
use SimpleWebApps\EventBus\EventBusInterface;
use SimpleWebApps\Relationship\Box\AbstractRelationshipBox;
use SimpleWebApps\Relationship\Box\RelationshipFromBox;
use SimpleWebApps\Relationship\Box\RelationshipToBox;
use SimpleWebApps\Relationship\Event\RelationshipActivatedEvent;
use SimpleWebApps\Relationship\Event\RelationshipRemovedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEntityListener(event: Events::postPersist, method: 'onRelationshipCreated', entity: Relationship::class)]
#[AsEventListener(event: RelationshipActivatedEvent::class, method: 'onRelationshipActivated')]
#[AsEventListener(event: RelationshipRemovedEvent::class, method: 'onRelationshipRemoved')]
final class RelationshipBroadcaster
{
  public const TOPIC = 'relationships';

  public function __construct(
    private EventBusInterface $eventBus,
    private TurboStreamRenderer $renderer,
  ) {
    // Do nothing.
  }

  public function onRelationshipCreated(Relationship $relationship): void
  {
    $this->eventBus->post(new Event(
      [(string) $relationship->getFromUser()?->getId()],
      self::TOPIC,
      $this->renderer->renderTwigComponentId(
        TurboStreamAction::Before,
        'from-users-bottom',
        RelationshipFromBox::NAME,
        ['relationship' => $relationship],
      ),
    ));
    $this->eventBus->post(new Event(
      [(string) $relationship->getToUser()?->getId()],
      self::TOPIC,
      $this->renderer->renderTwigComponentId(
        TurboStreamAction::Before,
        'to-users-bottom',
        RelationshipToBox::NAME,
        ['relationship' => $relationship],
      ),
    ));
  }

  public function onRelationshipActivated(RelationshipActivatedEvent $event): void
  {
    $this->eventBus->post(new Event(
      [(string) $event->relationship->getFromUser()?->getId()],
      self::TOPIC,
      $this->renderer->renderTwigComponentId(
        TurboStreamAction::Replace,
        RelationshipFromBox::htmlId((string) $event->relationship->getId()),
        RelationshipFromBox::NAME,
        ['relationship' => $event->relationship],
      ),
    ));
    $this->eventBus->post(new Event(
      [(string) $event->relationship->getToUser()?->getId()],
      self::TOPIC,
      $this->renderer->renderTwigComponentId(
        TurboStreamAction::Replace,
        RelationshipToBox::htmlId((string) $event->relationship->getId()),
        RelationshipToBox::NAME,
        ['relationship' => $event->relationship],
      ),
    ));
  }

  public function onRelationshipRemoved(RelationshipRemovedEvent $event): void
  {
    $this->eventBus->post(new Event(
      [
        (string) $event->relationship->getFromUser()?->getId(),
        (string) $event->relationship->getToUser()?->getId(),
      ],
      self::TOPIC,
      $this->renderer->renderId(
        TurboStreamAction::Remove,
        AbstractRelationshipBox::htmlId((string) $event->id),
        '',
        [],
      ),
    ));
  }
}

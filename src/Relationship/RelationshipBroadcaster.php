<?php

declare(strict_types=1);

namespace SimpleWebApps\Relationship;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\EventBus\Event;
use SimpleWebApps\EventBus\EventBusInterface;
use SimpleWebApps\Relationship\Event\RelationshipActivatedEvent;
use SimpleWebApps\Relationship\Event\RelationshipRemovedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Twig\Environment;
use Twig\TemplateWrapper;

#[AsEntityListener(event: Events::postPersist, method: 'onRelationshipCreated', entity: Relationship::class)]
#[AsEventListener(event: RelationshipActivatedEvent::class, method: 'onRelationshipActivated')]
#[AsEventListener(event: RelationshipRemovedEvent::class, method: 'onRelationshipRemoved')]
final readonly class RelationshipBroadcaster
{
  public const TOPIC = 'relationships';

  private const STREAM_TEMPLATE = 'relationships/stream.html.twig';

  private TemplateWrapper $template;

  public function __construct(
    private EventBusInterface $eventBus,
    Environment $twig,
  ) {
    $this->template = $twig->load(self::STREAM_TEMPLATE);
  }

  public function onRelationshipCreated(Relationship $relationship): void
  {
    $this->eventBus->post(new Event(
      [(string) $relationship->getFromUser()?->getId()],
      self::TOPIC,
      $this->template->renderBlock('relationship_created_from', [
        'relationship' => $relationship,
      ]),
    ));
    $this->eventBus->post(new Event(
      [(string) $relationship->getToUser()?->getId()],
      self::TOPIC,
      $this->template->renderBlock('relationship_created_to', [
        'relationship' => $relationship,
      ]),
    ));
  }

  public function onRelationshipActivated(RelationshipActivatedEvent $event): void
  {
    $this->eventBus->post(new Event(
      [(string) $event->relationship->getFromUser()?->getId()],
      self::TOPIC,
      $this->template->renderBlock('relationship_activated_from', [
        'relationship' => $event->relationship,
      ]),
    ));
    $this->eventBus->post(new Event(
      [(string) $event->relationship->getToUser()?->getId()],
      self::TOPIC,
      $this->template->renderBlock('relationship_activated_to', [
        'relationship' => $event->relationship,
      ]),
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
      $this->template->renderBlock('relationship_removed', [
        'id' => $event->id,
      ]),
    ));
  }
}

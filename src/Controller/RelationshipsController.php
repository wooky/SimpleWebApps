<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use SimpleWebApps\Auth\AuthenticatedUser;
use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Form\InviteFormType;
use SimpleWebApps\Repository\RelationshipRepository;
use SimpleWebApps\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Ulid;

use function assert;

#[Route('/relationships', name: self::CONTROLLER_SHORT_NAME)]
class RelationshipsController extends AbstractController
{
  public const CONTROLLER_SHORT_NAME = 'relationships';

  public const ROUTE_INDEX_NAME = '_index';
  public const ROUTE_INVITE_NAME = '_invite';
  public const ROUTE_APPROVE_NAME = '_approve';
  public const ROUTE_DELETE_NAME = '_delete';

  private const ROUTE_INDEX_PATH = '/';
  private const ROUTE_INVITE_PATH = '/invite';
  private const ROUTE_APPROVE_PATH = '/{id}/approve';
  private const ROUTE_DELETE_PATH = '/{id}/delete';

  /**
   * @SuppressWarnings(PHPMD.ElseExpression)
   */
  #[Route(self::ROUTE_INDEX_PATH, name: self::ROUTE_INDEX_NAME, methods: ['GET'])]
  public function index(
    RelationshipRepository $relationshipRepository,
    #[CurrentUser] AuthenticatedUser $user,
  ): Response {
    $relationships = $relationshipRepository->findBidirectionalRelationships($user->user);
    $fromUser = [];
    $toUser = [];
    foreach ($relationships as $relationship) {
      if ($relationship->getFromUser() === $user->user) {
        $fromUser[] = $relationship;
      } else {
        $toUser[] = $relationship;
      }
    }

    return $this->render('relationships/index.html.twig', [
        'from_user' => $fromUser,
        'to_user' => $toUser,
    ]);
  }

  #[Route(self::ROUTE_INVITE_PATH, name: self::ROUTE_INVITE_NAME, methods: ['GET', 'POST'])]
  public function invite(
    Request $request,
    UserRepository $userRepository,
    RelationshipRepository $relationshipRepository,
    #[CurrentUser] AuthenticatedUser $authenticatedUser,
  ): Response {
    $form = $this->createForm(InviteFormType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $toUserField = $form->get(InviteFormType::TO_USER);
      $toUserId = $toUserField->getData();
      assert($toUserId instanceof Ulid);
      $fromUser = $authenticatedUser->user;
      if ($fromUser->getId()->equals($toUserId)) {
        $toUserField->addError(new FormError('relationships.error.to_self'));
      }

      $toUser = $userRepository->find($toUserId);
      if (!$toUser) {
        $toUserField->addError(new FormError('relationships.error.user_not_found'));
      } elseif ($relationshipRepository->findOneBy(['fromUser' => $fromUser, 'toUser' => $toUser])) {
        $toUserField->addError(new FormError('relationships.error.duplicate'));
      }

      if ($form->isValid()) {
        $capability = $form->get(InviteFormType::CAPABILITY)->getData();
        assert($capability instanceof RelationshipCapability);
        $relationship = (new Relationship())
            ->setFromUser($fromUser)
            ->setToUser($toUser)
            ->setCapability($capability)
        ;

        $relationshipRepository->save($relationship, true);

        return $this->redirectToRoute(self::CONTROLLER_SHORT_NAME.self::ROUTE_INVITE_NAME);
      }
    }

    return $this->render('relationships/invite.html.twig', [
        'form' => $form,
    ]);
  }

  #[Route(self::ROUTE_APPROVE_PATH, name: self::ROUTE_APPROVE_NAME, methods: ['POST'])]
  public function approve(
    /* Request $request, */
    Relationship $relationship,
    RelationshipRepository $relationshipRepository,
  ): Response {
    if ($relationship->getToUser() !== $this->getUser()) {
      throw $this->createAccessDeniedException();
    }

    // TODO CSRF is not working if relationship box was created from a stream.
    // $token = $request->request->get('_token');
    // assert(is_string($token) || null === $token);
    // if ($this->isCsrfTokenValid('approve'.((string) $relationship->getId()), $token)) {
    $relationship->setActive(true);
    $relationshipRepository->save($relationship, true);
    // }

    return $this->redirectToRoute(self::CONTROLLER_SHORT_NAME.self::ROUTE_INDEX_NAME, [], Response::HTTP_SEE_OTHER);
  }

  #[Route(self::ROUTE_DELETE_PATH, name: self::ROUTE_DELETE_NAME, methods: ['DELETE'])]
  public function delete(
    // Request $request,
    Relationship $relationship,
    RelationshipRepository $relationshipRepository,
  ): Response {
    // TODO CSRF is not working if relationship box was created from a stream.
    // $token = $request->request->get('_token');
    // assert(is_string($token) || null === $token);
    // if ($this->isCsrfTokenValid('delete'.((string) $relationship->getId()), $token)) {
    $this->verifyRelationshipBelongsToUser($relationship);
    $relationshipRepository->remove($relationship, true);
    // }

    return $this->redirectToRoute(self::CONTROLLER_SHORT_NAME.self::ROUTE_INDEX_NAME, [], Response::HTTP_SEE_OTHER);
  }

  private function verifyRelationshipBelongsToUser(Relationship $relationship): void
  {
    if ($relationship->getFromUser() !== $this->getUser() && $relationship->getToUser() !== $this->getUser()) {
      throw $this->createAccessDeniedException();
    }
  }
}

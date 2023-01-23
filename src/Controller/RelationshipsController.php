<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use function assert;
use function is_string;

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\Relationship;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Form\InviteFormType;
use SimpleWebApps\Repository\RelationshipRepository;
use SimpleWebApps\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Ulid;

#[Route('/relationships', name: 'relationships_')]
class RelationshipsController extends AbstractController
{
  /**
   * @SuppressWarnings(PHPMD.ElseExpression)
   */
  #[Route('/', name: 'index', methods: ['GET'])]
  public function index(RelationshipRepository $relationshipRepository): Response
  {
    $user = $this->getUser();
    assert($user instanceof User);
    $relationships = $relationshipRepository->findBidirectionalRelationships($user);
    $fromUser = [];
    $toUser = [];
    foreach ($relationships as $relationship) {
      if ($relationship->getFromUser() === $user) {
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

  #[Route('/invite', name: 'invite', methods: ['GET', 'POST'])]
  public function invite(Request $request, UserRepository $userRepository, RelationshipRepository $relationshipRepository): Response
  {
    $form = $this->createForm(InviteFormType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $toUserField = $form->get(InviteFormType::TO_USER);
      $toUserId = $toUserField->getData();
      $fromUser = $this->getUser();
      assert($toUserId instanceof Ulid && $fromUser instanceof User);
      if ($fromUser->getId() === $toUserId) {
        $toUserField->addError(new FormError('Cannot create relationship with yourself.'));
      }

      $toUser = $userRepository->find($toUserId);
      if (!$toUser) {
        $toUserField->addError(new FormError('User not found.'));
      } elseif ($relationshipRepository->findOneBy(['fromUser' => $fromUser, 'toUser' => $toUser])) {
        $toUserField->addError(new FormError('Relationship already exists'));
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

        return $this->redirectToRoute('relationships_invite');
      }
    }

    return $this->render('relationships/invite.html.twig', [
        'form' => $form,
    ]);
  }

  #[Route('/{id}/approve', name: 'approve', methods: ['POST'])]
  public function approve(Request $request, Relationship $relationship, RelationshipRepository $relationshipRepository): Response
  {
    if ($relationship->getToUser() !== $this->getUser()) {
      throw $this->createAccessDeniedException();
    }

    $token = $request->request->get('_token');
    assert(is_string($token) || null === $token);
    if ($this->isCsrfTokenValid('approve'.((string) $relationship->getId()), $token)) {
      $relationship->setActive(true);
      $relationshipRepository->save($relationship, true);
    }

    return new Response();
  }

  #[Route('/{id}/delete', name: 'pre_delete', methods: ['GET'])]
  public function preDelete(Relationship $relationship): Response
  {
    $id = $relationship->getId();

    return $this->render('modal/pre_delete.html.twig', [
        'id' => $id,
        'subject' => 'relationships.subject',
        'delete_path' => $this->generateUrl('relationships_delete', ['id' => $id]),
    ]);
  }

  #[Route('/{id}/delete', name: 'delete', methods: ['DELETE'])]
  public function delete(Request $request, Relationship $relationship, RelationshipRepository $relationshipRepository): Response
  {
    $token = $request->request->get('_token');
    assert(is_string($token) || null === $token);
    if ($this->isCsrfTokenValid('delete'.((string) $relationship->getId()), $token)) {
      $this->verifyRelationshipBelongsToUser($relationship);
      $relationshipRepository->remove($relationship, true);
    }

    return $this->redirectToRoute('relationships_index', [], Response::HTTP_SEE_OTHER);
  }

  private function verifyRelationshipBelongsToUser(Relationship $relationship): void
  {
    if ($relationship->getFromUser() !== $this->getUser() && $relationship->getToUser() !== $this->getUser()) {
      throw $this->createAccessDeniedException();
    }
  }
}

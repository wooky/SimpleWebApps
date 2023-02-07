<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use function assert;
use function is_string;

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Entity\WeightRecord;
use SimpleWebApps\EventBus\EventStreamRenderer;
use SimpleWebApps\Form\WeightRecordType;
use SimpleWebApps\Repository\WeightRecordRepository;
use SimpleWebApps\WeightTracker\WeightRecordBroadcaster;
use SimpleWebApps\WeightTracker\WeightTrackerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/weight-tracker', name: 'weight_tracker_')]
class WeightTrackerController extends AbstractController
{
  #[Route('/', name: 'index', methods: ['GET'])]
  public function index(): Response
  {
    $user = $this->getUser();
    assert($user instanceof User);

    return $this->render('weight_tracker/index.html.twig');
  }

  #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
  public function new(Request $request, WeightRecordRepository $weightRecordRepository): Response
  {
    $user = $this->getUser();
    assert($user instanceof User);
    $weightRecord = (new WeightRecord())->setOwner($user);
    $form = $this->createNewEditForm($request, $weightRecord);

    if ($form->isSubmitted() && $form->isValid()) {
      $this->denyAccessUnlessGranted(RelationshipCapability::Write->value, $weightRecord);
      $weightRecordRepository->save($weightRecord, true);

      return $this->closeModalOrRedirect($request, 'weight_tracker_index');
    }

    return $this->render('modal/new.html.twig', [
        'form' => $form,
        'subject' => 'weight_tracker.subject',
    ]);
  }

  #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
  public function edit(Request $request, WeightRecord $weightRecord, WeightRecordRepository $weightRecordRepository): Response
  {
    if (!$this->isGranted(RelationshipCapability::Write->value, $weightRecord)) {
      return $this->render('modal/forbidden.html.twig', [
          'subject' => 'weight_tracker.subject',
      ])
          ->setStatusCode(Response::HTTP_FORBIDDEN);
    }

    $form = $this->createNewEditForm($request, $weightRecord);

    if ($form->isSubmitted() && $form->isValid()) {
      $weightRecordRepository->save($weightRecord, true);

      return $this->closeModalOrRedirect($request, 'weight_tracker_index');
    }

    $id = $weightRecord->getId();

    return $this->render('modal/edit.html.twig', [
        'id' => $id,
        'form' => $form,
        'subject' => 'weight_tracker.subject',
        'pre_delete_path' => $this->generateUrl('weight_tracker_pre_delete', ['id' => $id]),
    ]);
  }

  #[Route('/{id}/delete', name: 'pre_delete', methods: ['POST'])]
  public function preDelete(WeightRecord $weightRecord): Response
  {
    $id = $weightRecord->getId();

    return $this->render('modal/pre_delete.html.twig', [
        'id' => $id,
        'subject' => 'weight_tracker.subject',
        'delete_path' => $this->generateUrl('weight_tracker_delete', ['id' => $id]),
    ]);
  }

  #[Route('/{id}/delete', name: 'delete', methods: ['DELETE'])]
  public function delete(Request $request, WeightRecord $weightRecord, WeightRecordRepository $weightRecordRepository): Response
  {
    $token = $request->request->get('_token');
    assert(is_string($token) || null === $token);
    if ($this->isCsrfTokenValid('delete'.((string) $weightRecord->getId()), $token)) {
      $this->denyAccessUnlessGranted(RelationshipCapability::Write->value, $weightRecord);
      $weightRecordRepository->remove($weightRecord, true);
    }

    return $this->closeModalOrRedirect($request, 'weight_tracker_index');
  }

  #[Route('/events', name: 'events', methods: ['GET'])]
  public function events(WeightTrackerService $weightTrackerService, EventStreamRenderer $renderer): Response
  {
    $user = $this->getUser();
    assert($user instanceof User);
    $userId = $user->getId();
    assert(null !== $userId);
    $initialPayload = json_encode($weightTrackerService->getRenderableDataSets($user));

    return $renderer->createResponse((string) $userId, [WeightRecordBroadcaster::TOPIC], [$initialPayload]);
  }

  private function closeModalOrRedirect(Request $request, string $route): Response
  {
    if ('app-modal' === $request->headers->get('Turbo-Frame')) {
      return $this->render('modal/close.html.twig');
    }

    return $this->redirectToRoute($route, status: Response::HTTP_SEE_OTHER);
  }

  private function createNewEditForm(
        Request $request,
        WeightRecord $weightRecord,
    ): FormInterface {
    $form = $this->createForm(WeightRecordType::class, $weightRecord, [
        WeightRecordType::IS_OWNER_DISABLED => null !== $weightRecord->getIdOrNull(),
    ]);
    $form->handleRequest($request);

    return $form;
  }
}

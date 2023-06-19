<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Controller\Mixin\CrudMixin;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Entity\WeightRecord;
use SimpleWebApps\Form\WeightRecordType;
use SimpleWebApps\Repository\WeightRecordRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/weight-tracker', name: self::CONTROLLER_SHORT_NAME)]
class WeightTrackerController extends AbstractController
{
  /** @use CrudMixin<WeightRecord> */
  use CrudMixin;

  public const CONTROLLER_SHORT_NAME = 'weight_tracker';

  #[Route(self::ROUTE_INDEX_PATH, name: self::ROUTE_INDEX_NAME, methods: ['GET'])]
  public function index(): Response
  {
    return $this->render('weight_tracker/index.html.twig');
  }

  #[Route(self::ROUTE_NEW_PATH, name: self::ROUTE_NEW_NAME, methods: ['GET', 'POST'])]
  public function new(
    Request $request,
    WeightRecordRepository $weightRecordRepository,
    #[CurrentUser] User $user,
  ): Response {
    return $this->crudNewAndClose($request, $weightRecordRepository, (new WeightRecord())->setOwner($user));
  }

  #[Route(self::ROUTE_EDIT_PATH, name: self::ROUTE_EDIT_NAME, methods: ['GET', 'POST'])]
  #[IsGranted(RelationshipCapability::Write->value, 'weightRecord', message: self::CONTROLLER_SHORT_NAME)]
  public function edit(
    Request $request,
    WeightRecord $weightRecord,
    WeightRecordRepository $weightRecordRepository,
  ): Response {
    return $this->crudEdit($request, $weightRecordRepository, $weightRecord);
  }

  #[Route(self::ROUTE_DELETE_PATH, name: self::ROUTE_DELETE_NAME, methods: ['DELETE'])]
  #[IsGranted(RelationshipCapability::Write->value, 'weightRecord', message: self::CONTROLLER_SHORT_NAME)]
  public function delete(
    Request $request,
    WeightRecord $weightRecord,
    WeightRecordRepository $weightRecordRepository,
  ): Response {
    return $this->crudDelete($request, $weightRecordRepository, $weightRecord);
  }

  protected function createNewEditForm(Request $request, $entity): FormInterface
  {
    $form = $this->createForm(WeightRecordType::class, $entity, [
      WeightRecordType::IS_OWNER_DISABLED => null !== $entity->getIdOrNull(),
    ]);
    $form->handleRequest($request);

    return $form;
  }

  protected static function getControllerShortName(): string
  {
    return self::CONTROLLER_SHORT_NAME;
  }
}

<?php

declare(strict_types=1);

namespace SimpleWebApps\Controller;

use SimpleWebApps\Entity\WeightRecord;
use SimpleWebApps\Form\WeightRecordType;
use SimpleWebApps\Repository\WeightRecordRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @extends AbstractCrudController<WeightRecord>
 */
#[Route('/weight-tracker', name: self::CONTROLLER_SHORT_NAME)]
class WeightTrackerController extends AbstractCrudController
{
  protected const CONTROLLER_SHORT_NAME = 'weight_tracker';
  protected const FORM_TYPE = WeightRecordType::class;

  #[Route(self::ROUTE_INDEX_PATH, name: self::ROUTE_INDEX_NAME, methods: ['GET'])]
  public function index(): Response
  {
    return $this->render('weight_tracker/index.html.twig');
  }

  #[Route(self::ROUTE_NEW_PATH, name: self::ROUTE_NEW_NAME, methods: ['GET', 'POST'])]
  public function new(Request $request, WeightRecordRepository $weightRecordRepository): Response
  {
    // TODO catch UniqueConstraintViolationException and set 'weight_record.date_exists' message
    return $this->crudNew($request, $weightRecordRepository, new WeightRecord());
  }

  #[Route(self::ROUTE_EDIT_PATH, name: self::ROUTE_EDIT_NAME, methods: ['GET', 'POST'])]
  public function edit(Request $request, WeightRecord $weightRecord, WeightRecordRepository $weightRecordRepository): Response
  {
    return $this->crudEdit($request, $weightRecordRepository, $weightRecord);
  }

  #[Route(self::ROUTE_DELETE_PATH, name: self::ROUTE_PREDELETE_NAME, methods: ['POST'])]
  public function preDelete(WeightRecord $weightRecord): Response
  {
    return $this->crudPreDelete($weightRecord);
  }

  #[Route(self::ROUTE_DELETE_PATH, name: self::ROUTE_DELETE_NAME, methods: ['DELETE'])]
  public function delete(Request $request, WeightRecord $weightRecord, WeightRecordRepository $weightRecordRepository): Response
  {
    return $this->crudDelete($request, $weightRecordRepository, $weightRecord);
  }
}

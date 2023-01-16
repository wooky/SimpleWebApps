<?php

namespace SimpleWebApps\Controller;

use SimpleWebApps\Auth\RelationshipCapability;
use SimpleWebApps\Entity\User;
use SimpleWebApps\Entity\WeightRecord;
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
    public function index(WeightTrackerService $weightTrackerService): Response
    {
        /** @var User */ $user = $this->getUser();
        return $this->render('weight_tracker/index.html.twig', [
            'chart_topics' => WeightRecordBroadcaster::getTopics($user),
            'points' => $weightTrackerService->getRenderableDataSets($user),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, WeightRecordRepository $weightRecordRepository): Response
    {
        /** @var User */ $user = $this->getUser();
        $weightRecord = (new WeightRecord())->setOwner($user);
        $form = $this->createNewEditForm($request, $weightRecord);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted(RelationshipCapability::Write->value, $weightRecord);
            $weightRecordRepository->save($weightRecord, true);

            return $this->closeModalOrRedirect($request, 'weight_tracker_index');
        }

        return $this->render('weight_tracker/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, WeightRecord $weightRecord, WeightRecordRepository $weightRecordRepository): Response
    {
        if (!$this->isGranted(RelationshipCapability::Write->value, $weightRecord)) {
            return $this->render('weight_tracker/forbidden.html.twig')
                ->setStatusCode(Response::HTTP_FORBIDDEN);
        }

        $form = $this->createNewEditForm($request, $weightRecord);

        if ($form->isSubmitted() && $form->isValid()) {
            $weightRecordRepository->save($weightRecord, true);

            return $this->closeModalOrRedirect($request, 'weight_tracker_index');
        }

        return $this->render('weight_tracker/edit.html.twig', [
            'id' => $weightRecord->getId(),
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'pre_delete', methods: ['POST'])]
    public function preDelete(WeightRecord $weightRecord): Response
    {
        return $this->render('weight_tracker/pre_delete.html.twig', [
            'id' => $weightRecord->getId(),
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['DELETE'])]
    public function delete(Request $request, WeightRecord $weightRecord, WeightRecordRepository $weightRecordRepository): Response
    {
        /** @var ?string */ $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete'.$weightRecord->getId(), $token)) {
            $this->denyAccessUnlessGranted(RelationshipCapability::Write->value, $weightRecord);
            $weightRecordRepository->remove($weightRecord, true);
        }

        return $this->closeModalOrRedirect($request, 'weight_tracker_index');
    }

    private function closeModalOrRedirect(Request $request, string $route): Response
    {
        if ($request->headers->get('Turbo-Frame') === "app-modal") {
            return $this->render('_reusable/modal_close.html.twig');
        }

        return $this->redirectToRoute($route, status: Response::HTTP_SEE_OTHER);
    }

    private function createNewEditForm(
        Request $request,
        WeightRecord $weightRecord,
    ): FormInterface
    {
        $form = $this->createForm(WeightRecordType::class, $weightRecord, [
            WeightRecordType::IS_OWNER_DISABLED => $weightRecord->getIdOrNull() !== null,
        ]);
        $form->handleRequest($request);
        return $form;
    }
}

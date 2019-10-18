<?php

namespace App\Controller;

use App\Form\DataTransformer\AnnotationTransformer;
use App\Services\LegacyEnvironment;
use App\Utils\PortfolioService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Form\Type\AnnotationType;
use App\Utils\AnnotationService;
use App\Utils\ItemService;
use App\Utils\ReaderService;

/**
 * Class AnnotationController
 * @package App\Controller
 * @Security("is_granted('ITEM_ENTER', roomId)")
 */
class AnnotationController extends AbstractController
{
    /**
     * @Route("/room/{roomId}/annotation/feed/{linkedItemId}/{start}/{firstTagId}/{secondTagId}")
     * @Template()
     * @param AnnotationService $annotationService
     * @param ItemService $itemService
     * @param ReaderService $readerService
     * @param int $roomId
     * @param int $linkedItemId
     * @param int $max
     * @param int $start
     * @param int $firstTagId
     * @param int $secondTagId
     * @return array
     */
    public function feedAction(
        AnnotationService $annotationService,
        ItemService $itemService,
        ReaderService $readerService,
        int $roomId,
        int $linkedItemId,
        int $max = 10,
        int $start = 0,
        int $firstTagId = null,
        int $secondTagId = null
    ) {
        // get annotation list from manager service 
        $annotations = $annotationService->getListAnnotations($roomId, $linkedItemId, $max, $start);

        if ($firstTagId && $secondTagId) {
            $portfolioService = $this->get(PortfolioService::class);
            $cellCoordinates = $portfolioService->getCellCoordinatesForTagIds($linkedItemId, $firstTagId, $secondTagId);
            if (!empty($cellCoordinates)) {
                $annotationIds = $portfolioService->getAnnotationIdsForPortfolioCell($linkedItemId, $cellCoordinates[0], $cellCoordinates[1]);
                $portfolioAnnotations = [];
                if ($annotationIds) {
                    foreach ($annotationIds as $annotationId) {
                        $portfolioAnnotations[] = $itemService->getTypedItem($annotationId);
                    }
                }
                $annotations = $portfolioAnnotations;
            }
        }

        $readerList = [];
        foreach ($annotations as $item) {
            $readerList[$item->getItemId()] = $readerService->getChangeStatus($item->getItemId());
        }


        return [
            'roomId' => $roomId,
            'annotations' => $annotations,
            'readerList' => $readerList,
        ];
    }

    /**
     * @Route("/room/{roomId}/annotation/feed/{linkedItemId}/{start}")
     * @Template()
     * @param AnnotationService $annotationService
     * @param ReaderService $readerService
     * @param int $roomId
     * @param int $linkedItemId
     * @param int $max
     * @param int $start
     * @return array
     */
    public function feedPrintAction(
        AnnotationService $annotationService,
        ReaderService $readerService,
        int $roomId,
        int $linkedItemId,
        int $max = 10,
        int $start = 0
    ) {
        // get annotation list from manager service 
        $annotations = $annotationService->getListAnnotations($roomId, $linkedItemId, $max, $start);

        $readerList = [];
        foreach ($annotations as $item) {
            $readerList[$item->getItemId()] = $readerService->getChangeStatus($item->getItemId());
        }

        return [
            'roomId' => $roomId,
            'annotations' => $annotations,
            'readerList' => $readerList,
        ];
    }

    /**
     * @Route("/room/{roomId}/annotation/{itemId}/edit")
     * @Method({"GET", "POST"})
     * @Template()
     * @Security("is_granted('ITEM_EDIT', itemId)")
     * @param ItemService $itemService
     * @param AnnotationTransformer $transformer
     * @param LegacyEnvironment $environment
     * @param int $roomId
     * @param int $itemId
     * @param Request $request
     * @return array|RedirectResponse
     */
    public function editAction(
        ItemService $itemService,
        AnnotationTransformer $transformer,
        LegacyEnvironment $environment,
        int $roomId,
        int $itemId,
        Request $request
    ) {
        $item = $itemService->getTypedItem($itemId);
        $formData = $transformer->transform($item);
        $form = $this->createForm(AnnotationType::class, $formData);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $legacyEnvironment = $environment->getEnvironment();
                $readerManager = $legacyEnvironment->getReaderManager();
                $noticedManager = $legacyEnvironment->getNoticedManager();

                $item = $transformer->applyTransformation($item, $form->getData());
                $item->save();

                $readerManager->markRead($itemId, 0);
                $noticedManager->markNoticed($itemId, 0);
            }

            return $this->redirectToRoute('app_annotation_success', [
                'roomId' => $roomId,
                'itemId' => $itemId,
            ]);
        }

        return [
            'itemId' => $itemId,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/room/{roomId}/annotation/{itemId}/success")
     * @Method({"GET"})
     * @Template()
     * @Security("is_granted('ITEM_EDIT', itemId)")
     * @param ItemService $itemService
     * @param int $itemId
     * @return array
     */
    public function successAction(
        ItemService $itemService,
        int $itemId
    ) {
        $item = $itemService->getTypedItem($itemId);
        return [
            'annotation' => $item,
        ];
    }

    /**
     * @Route("/room/{roomId}/annotation/{itemId}/create/{firstTagId}/{secondTagId}")
     * @Method({"POST"})
     * @Template()
     * @Security("is_granted('ITEM_ANNOTATE', itemId)")
     * @param ItemService $itemService
     * @param AnnotationService $annotationService
     * @param Request $request
     * @param int $roomId
     * @param int $itemId
     * @param int $firstTagId
     * @param int $secondTagId
     * @return RedirectResponse
     */
    public function createAction(
        ItemService $itemService,
        AnnotationService $annotationService,
        Request $request,
        int $roomId,
        int $itemId,
        int $firstTagId = null,
        int $secondTagId = null
    ) {
        $item = $itemService->getTypedItem($itemId);
        $itemType = $item->getItemType();

        $form = $this->createForm(AnnotationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $data = $form->getData();

                // create new annotation
                $annotationId = $annotationService->addAnnotation($roomId, $itemId, $data['description']);

                $routeArray = [];
                $routeArray['roomId'] = $roomId;
                $routeArray['itemId'] = $itemId;
                $routeArray['_fragment'] = 'description' . $annotationId;
                if ($itemType == 'portfolio') {
                    $routeArray['portfolioId'] = $itemId;
                    $routeArray['firstTagId'] = $firstTagId;
                    $routeArray['secondTagId'] = $secondTagId;

                    $portfolioService = $this->get(PortfolioService::class);
                    $cellCoordinates = $portfolioService->getCellCoordinatesForTagIds($itemId, $firstTagId, $secondTagId);
                    if (!empty($cellCoordinates)) {
                        $portfolioService->setPortfolioAnnotation($itemId, $annotationId, $cellCoordinates[0], $cellCoordinates[1]);
                    }
                }

                return $this->redirectToRoute('app_'.$itemType.'_detail', $routeArray);
            }
        }
        return $this->redirectToRoute('app_'.$itemType.'_detail', array('roomId' => $roomId, 'itemId' => $itemId));
    }

    /**
     * @Route("/room/{roomId}/annotation/{itemId}/delete")
     * @Method({"GET"})
     * @Security("is_granted('ITEM_EDIT', itemId)")
     * @param ItemService $itemService
     * @param int $itemId
     * @return JsonResponse
     */
    public function deleteAction(
        ItemService $itemService,
        int $itemId
    ) {
        $item = $itemService->getTypedItem($itemId);
        $item->delete();
        $response = new JsonResponse();
        $response->setData([
            'deleted' => true,
        ]);
        return $response;
    }
}

<?php

namespace CommsyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

use CommsyBundle\Form\Type\PortalAnnouncementsType;
use CommsyBundle\Form\Type\PortalTermsType;
use CommsyBundle\Form\Type\RoomCategoriesEditType;
use CommsyBundle\Entity\RoomCategories;

use CommsyBundle\Event\CommsyEditEvent;

class PortalController extends Controller
{
    /**
     * @Route("/portal/{roomId}/room/categories/{roomCategoryId}")
     * @Template()
     * @Security("is_granted('ITEM_MODERATE', roomId)")
     */
    public function roomcategoriesAction($roomId, $roomCategoryId = null, Request $request)
    {
        $portalId = $roomId;

        $legacyEnvironment = $this->get('commsy_legacy.environment')->getEnvironment();

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('CommsyBundle:RoomCategories');

        if ($roomCategoryId) {
            $roomCategory = $repository->findOneById($roomCategoryId);
        } else {
            $roomCategory = new RoomCategories();
            $roomCategory->setContextId($portalId);
        }

        $editForm = $this->createForm(RoomCategoriesEditType::class, $roomCategory, []);

        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            // tells Doctrine you want to (eventually) save the Product (no queries yet)

            if ($editForm->getClickedButton()->getName() == 'delete') {
                $roomCategoriesService = $this->get('commsy.roomcategories_service');
                $roomCategoriesService->removeRoomCategory($roomCategory);
            } else {
                $em->persist($roomCategory);
            }

            // actually executes the queries (i.e. the INSERT query)
            $em->flush();

            return $this->redirectToRoute('commsy_portal_roomcategories', [
                'roomId' => $roomId,
            ]);
        }

        $roomCategories = $repository->findBy(array('context_id' => $portalId));

        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch('commsy.edit', new CommsyEditEvent(null));

        return [
            'editForm' => $editForm->createView(),
            'roomId' => $portalId,
            'roomCategories' => $roomCategories,
            'roomCategoryId' => $roomCategoryId,
            'item' => $legacyEnvironment->getCurrentPortalItem(),
        ];
    }

    /**
     * @Route("/portal/{roomId}/announcements")
     * @Template()
     * @Security("is_granted('ITEM_MODERATE', roomId)")
     */
    public function announcementsAction($roomId, Request $request) {
        $legacyEnvironment = $this->get('commsy_legacy.environment')->getEnvironment();

        $portalItem = $legacyEnvironment->getCurrentPortalItem();

        $portalAnnouncementData = [];
        $portalAnnouncementData['text'] = $portalItem->getServerNewsText();
        $portalAnnouncementData['link'] = $portalItem->getServerNewsLink();
        $portalAnnouncementData['show'] = $portalItem->showServerNews();
        $portalAnnouncementData['title'] = $portalItem->getServerNewsTitle();
        $portalAnnouncementData['showServerInfos'] = $portalItem->showNewsFromServer();

        $announcementsForm = $this->createForm(PortalAnnouncementsType::class, $portalAnnouncementData, []);

        $announcementsForm->handleRequest($request);
        if ($announcementsForm->isValid()) {
            if ($announcementsForm->getClickedButton()->getName() == 'save') {
                $formData = $announcementsForm->getData();
                $portalItem->setServerNewsText($formData['text']);
                $portalItem->setServerNewsLink($formData['link']);
                $portalItem->setServerNewsTitle($formData['title']);
                if ($formData['show']) {
                    $portalItem->setShowServerNews();
                }
                else {
                    $portalItem->setDontShowServerNews();
                }
                if ($formData['showServerInfos']) {
                    $portalItem->setShowNewsFromServer();
                }
                else {
                    $portalItem->setDontShowNewsFromServer();
                }
                $portalItem->save();
            }
        }

        return [
            'form' => $announcementsForm->createView(),
        ];
    }

    /**
     * @Route("/portal/{roomId}/terms")
     * @Template()
     * @Security("is_granted('ITEM_MODERATE', roomId)")
     */
    public function termsAction($roomId, Request $request) {
        $legacyEnvironment = $this->get('commsy_legacy.environment')->getEnvironment();

        $portalItem = $legacyEnvironment->getCurrentPortalItem();

        $portalTerms = $portalItem->getAGBTextArray();
        $portalTerms['status'] = $portalItem->getAGBStatus();

        $termsForm = $this->createForm(PortalTermsType::class, $portalTerms, []);

        $termsForm->handleRequest($request);
        if ($termsForm->isValid()) {
            if ($termsForm->getClickedButton()->getName() == 'save') {
                $formData = $termsForm->getData();

                $portalItem->setAGBTextArray(array_filter($formData, function($key) {
                    return $key == 'DE' || $key == 'EN';
                }, ARRAY_FILTER_USE_KEY));
                $portalItem->setAGBStatus($formData['status']);
                $portalItem->save();
            }
        }

        return [
            'form' => $termsForm->createView(),
        ];

    }

    /**
     * @Route("/portal/{roomId}/legacysettings")
     * @Template()
     */
    public function legacysettingsAction($roomId, Request $request)
    {
        return $this->redirect('/?cid='.$roomId.'&mod=configuration&fct=index');
    }
}

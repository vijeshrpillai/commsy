<?php

/*
 * This file is part of CommSy.
 *
 * (c) Matthias Finck, Dirk Fust, Oliver Hankel, Iver Jackewitz, Michael Janneck,
 * Martti Jeenicke, Detlev Krause, Irina L. Marinescu, Timo Nolte, Bernd Pape,
 * Edouard Simon, Monique Strauss, Jose Mauel Gonzalez Vazquez, Johannes Schultze
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Utils;

use App\Security\Authorization\Voter\ItemVoter;
use App\Services\LegacyEnvironment;
use cs_annotation_item;
use cs_environment;
use cs_item;
use cs_item_manager;
use cs_list;
use cs_userroom_item;
use Symfony\Bundle\SecurityBundle\Security;

class ItemService
{
    private readonly cs_environment $legacyEnvironment;
    private readonly cs_item_manager $itemManager;

    public function __construct(
        private readonly Security $security,
        LegacyEnvironment $legacyEnvironment
    ) {
        $this->legacyEnvironment = $legacyEnvironment->getEnvironment();
        $this->itemManager = $this->legacyEnvironment->getItemManager();
    }

    /**
     * @param int $itemId
     */
    public function getItem($itemId): ?cs_item
    {
        return $this->itemManager->getItem($itemId);
    }

    /**
     * @param int $itemId
     * @param int (optional) $versionId
     */
    public function getTypedItem($itemId, $versionId = null): ?cs_item
    {
        $item = $this->getItem($itemId);

        if ($item && is_object($item)) {
            $type = $item->getItemType();

            if ('label' == $type) {
                $labelManager = $this->legacyEnvironment->getLabelManager();
                $labelItem = $labelManager->getItem($item->getItemID());
                $type = $labelItem->getLabelType();
            }
            $manager = $this->legacyEnvironment->getManager($type);

            if (!$manager) {
                return null;
            }

            if (null === $versionId) {
                return $manager->getItem($item->getItemID());
            } else {
                if (method_exists($manager, 'getItemByVersion')) {
                    return $manager->getItemByVersion($itemId, $versionId);
                }
            }
        }

        return null;
    }

    public function getLinkedItemIdArray($itemId)
    {
        $item = $this->getTypedItem($itemId);
        $linkedItems = $item->getAllLinkItemList()->to_array();

        $linkedItemIdArray = [];

        foreach ($linkedItems as $key => $value) {
            $linkedItemIdArray[] = $value->getSecondLinkedItemID();
        }

        return $linkedItemIdArray;
    }

    public function getEditorsForItem($item)
    {
        $user = $this->legacyEnvironment->getCurrentUserItem();
        $link_modifier_item_manager = $this->legacyEnvironment->getLinkModifierItemManager();
        $user_manager = $this->legacyEnvironment->getUserManager();
        $modifiers = $link_modifier_item_manager->getModifiersOfItem($item->getItemID());
        $modifier_array = [];
        foreach ($modifiers as $modifier_id) {
            $modificator = $user_manager->getItem($modifier_id);
            $modifier_array[] = $modificator;
        }

        return $modifier_array;
    }

    public function getAdditionalEditorsForItem($item)
    {
        $modifier_array = $this->getEditorsForItem($item);
        $additional_modifier_array = [];
        foreach ($modifier_array as $modifier) {
            if ($modifier->getItemId() != $item->getCreatorId()) {
                $additional_modifier_array[] = $modifier;
            }
        }

        return $additional_modifier_array;
    }

    public function getItemFileList($itemId)
    {
        $item = $this->getTypedItem($itemId);

        if (isset($item)) {
            if ($item->isA('material')) {
                $file_list = $item->getFileListWithFilesFromSections();
            } elseif ($item->isA('discussion')) {
                $file_list = $item->getFileListWithFilesFromArticles();
            } elseif ($item->isA('todo')) {
                $file_list = $item->getFileListWithFilesFromSteps();
            } else {
                $file_list = $item->getFileList();
            }

            if ($item->isA('section')) {
                $material_item = $item->getLinkedItem();
                $file_list2 = $material_item->getFileList();
                if (isset($file_list2) and !empty($file_list2) and $file_list2->getCount() > 0) {
                    $file_list->addList($file_list2);
                }
            }

            if (!empty($file_list)) {
                $file_array = $file_list->to_Array();

                $file_name_array = [];
                foreach ($file_array as $file) {
                    $file_name_array[htmlentities((string) $file->getDisplayName(), ENT_NOQUOTES, 'UTF-8')] = $file;
                }

                return $file_name_array;
            }
        }

        return [];
    }

    /**
     * Returns all searchable items contained in rooms specified by the given room IDs.
     *
     * @param int[] $contextIds array of room IDs for rooms whose items shall be returned
     *
     * @return cs_item[]
     */
    public function getSearchableItemsForContextIds(array $contextIds): array
    {
        if (empty($contextIds)) {
            return [];
        }

        $itemManager = $this->itemManager;
        $searchableTypes = [
            CS_ANNOUNCEMENT_TYPE,
            CS_DATE_TYPE,
            CS_DISCUSSION_TYPE,
            CS_LABEL_TYPE, // groups, topics & institutions
            CS_MATERIAL_TYPE,
            CS_TODO_TYPE,
            CS_USER_TYPE,
            cs_userroom_item::ROOM_TYPE_USER,
        ];

        $itemManager->resetLimits();
        $itemManager->setNoIntervalLimit();
        $itemManager->setTypeArrayLimit($searchableTypes);
        $itemManager->setContextArrayLimit($contextIds);

        $itemManager->select();

        /** @var cs_list $itemList */
        $itemList = $itemManager->get();

        return $itemList->to_array();
    }

    /**
     * @param cs_item[] cs_item array
     * @param bool $withAnnotations Should related annotations also marked read?
     */
    public function markRead($items, $withAnnotations = true)
    {
        $readerManager = $this->legacyEnvironment->getReaderManager();

        foreach ($items as $item) {
            $readerManager->markRead($item->getItemID(), $item->getVersionID());

            // annotations
            if ($withAnnotations) {
                $annotations = $item->getAnnotationList();
                if (!empty($annotations)) {
                    /** @var cs_annotation_item $annotationItem */
                    $annotationItem = $annotations->getFirst();

                    while ($annotationItem) {
                        $readerManager->markRead($annotationItem->getItemId(), '0');

                        $annotationItem = $annotations->getNext();
                    }
                }
            }
        }
    }

    public function getAllowedActionsForItems(array $items): array
    {
        $allowedActions = [];

        foreach ($items as $item) {
            /** @var cs_item $item */
            if ($this->security->isGranted('ITEM_EDIT', $item->getItemID())) {
                $allowedActions[$item->getItemID()] = ['markread', 'mark', 'categorize', 'hashtag', 'activate', 'deactivate', 'save'];

                if ($this->security->isGranted(ItemVoter::FILE_LOCK, $item->getItemID())) {
                    $allowedActions[$item->getItemID()][] = 'delete';
                }
            } else {
                $allowedActions[$item->getItemID()] = ['markread', 'mark', 'save'];
            }
        }

        return $allowedActions;
    }
}

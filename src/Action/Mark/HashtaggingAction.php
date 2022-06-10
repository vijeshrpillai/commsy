<?php
/**
 * Created by PhpStorm.
 * User: cschoenf
 * Date: 24.07.18
 * Time: 15:28
 */

namespace App\Action\Mark;


use App\Action\ActionInterface;
use App\Http\JsonDataResponse;
use App\Services\MarkedService;
use App\Services\LegacyEnvironment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class HashtaggingAction implements ActionInterface
{
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * @var MarkedService
     */
    private MarkedService $markedService;

    public function __construct(
        TranslatorInterface $translator,
        LegacyEnvironment $legacyEnvironment,
        MarkedService $markedService
    ) {
        $this->translator = $translator;
        $this->markedService = $markedService;
    }

    public function execute(\cs_room_item $roomItem, array $items): Response
    {
        $ids = [];
        foreach ($items as $item) {
            $ids[] = $item->getItemId();
        }

        $this->markedService->hashtagEntries($roomItem->getItemID(), $ids);

        return new JsonDataResponse([
            'message' => '<i class=\'uk-icon-justify uk-icon-medium uk-icon-hashtag\'></i> ' . $this->translator->trans('hashtagged %count% entries in list', [
                    '%count%' => count($items),
                ]),
        ]);
    }
}
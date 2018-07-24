<?php
/**
 * Created by PhpStorm.
 * User: cschoenf
 * Date: 03.07.18
 * Time: 15:24
 */

namespace CommsyBundle\Action\MarkRead;


use Commsy\LegacyBundle\Utils\TodoService;

class MarkReadTodo implements MarkReadInterface
{
    /**
     * @var TodoService
     */
    private $todoService;

    public function __construct(TodoService $todoService)
    {
        $this->todoService = $todoService;
    }

    /**
     * @param \cs_item $item
     */
    public function markRead(\cs_item $item): void
    {
        $this->todoService->markTodoReadAndNoticed($item->getItemId());
    }
}
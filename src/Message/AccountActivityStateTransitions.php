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

namespace App\Message;

class AccountActivityStateTransitions
{
    /**
     * @param int[] $ids
     */
    public function __construct(private array $ids)
    {
    }

    /**
     * @return int[]
     */
    public function getIds(): array
    {
        return $this->ids;
    }
}

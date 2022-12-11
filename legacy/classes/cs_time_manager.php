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

/** upper class of the label manager.
 */
include_once 'classes/cs_labels_manager.php';

/** upper class of the time item.
 */
include_once 'classes/cs_time_item.php';

/** class for database connection to the database table "labels"
 * this class implements a database manager for the table "labels". Labels are groups, topics, labels, ...
 */
class cs_time_manager extends cs_labels_manager
{
    /** constructor: cs_time_manager
     * the only available constructor, initial values for internal variables.
     *
     * @param object cs_environment the environment
     */
    public function __construct($environment)
    {
        cs_labels_manager::__construct($environment);
    }

     /** get an empty time item
      *  get an empty label_item.
      *
      *  @return cs_label_item a time label
      */
     public function getNewItem($label_type = '')
     {
         $item = new cs_time_item($this->_environment);
         $item->makeSystemLabel();

         return $item;
     }
}

<?PHP
// $Id$
//
// Release $Name$
//
// Copyright (c)2002-2003 Matthias Finck, Dirk Fust, Oliver Hankel, Iver Jackewitz, Michael Janneck,
// Martti Jeenicke, Detlev Krause, Irina L. Marinescu, Timo Nolte, Bernd Pape,
// Edouard Simon, Monique Strauss, José Manuel González Vázquez
//
//    This file is part of CommSy.
//
//    CommSy is free software; you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation; either version 2 of the License, or
//    (at your option) any later version.
//
//    CommSy is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You have received a copy of the GNU General Public License
//    along with CommSy.

/** upper class of the label item
 */
include_once('classes/cs_label_item.php');
include_once('functions/text_functions.php');

/** class for a label
 * this class implements a commsy label. A label can be a group, a topic, a label, ...
 */
class cs_buzzword_item extends cs_label_item {

   /** constructor
    * the only available constructor, initial values for internal variables
    *
    * @param string label_type type of the label
    */
   public function __construct ( $environment ) {
      $this->cs_label_item($environment,CS_BUZZWORD_TYPE);
   }

   /** save news item
    * this methode save the news item into the database
    */
   function saveMaterialLinksByIDArray($array) {
      $links_manager = $this->_environment->getLinkManager();
      $links_manager->saveLinksMaterialToBuzzword($array,$this->getItemID());
   }
   function saveRubricLinksByIDArray($array,$rubric) {
      $links_manager = $this->_environment->getLinkManager();
      $links_manager->saveLinksRubricToBuzzword($array,$this->getItemID(),$rubric);
   }
}
?>
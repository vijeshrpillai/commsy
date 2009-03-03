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

$this->includeClass(RUBRIC_FORM);

/** class for commsy forms
 * this class implements an interface for the creation of forms in the commsy style
 */
class cs_configuration_homepage_form extends cs_rubric_form {

   /** constructor
    * the only available constructor
    *
    * @param array params array of parameter
    */
   function cs_configuration_homepage_form ($params) {
      $this->cs_rubric_form($params);
   }

   /** init data for form, INTERNAL
    * this methods init the data for the form, for example groups
    */
   function _initForm () {
      $this->_headline = $this->_translator->getMessage('HOMEPAGE_CONFIGURATION_TITLE');
      $this->setHeadline($this->_headline);
      $this->_with_homepage_new = false;
      if (isset($this->_item)) {
         if (!$this->_item->isHomepageLinkActive()) {
            $homepage_manager = $this->_environment->getHomepageManager();
            $root_page = $homepage_manager->getRootPageItem($this->_item->getItemID());
            if ( isset($root_page) ) {
               $root_page_item_id = $root_page->getItemID();
               if ( !empty($root_page_item_id) ) {
                  $this->_with_homepage_new = true;
               }
            }
         }
      } elseif (isset($this->_form_post)) {
        $this->_with_homepage_new = $this->_form_post['with_homepage_new'];
      }
   }

   /** create the form, INTERNAL
    * this methods creates the form with the form definitions
    */
   function _createForm () {

      // form fields
      $this->_form->addHidden('iid','');
      $this->_form->addHidden('with_homepage_new','');
      $this->_form->addCheckbox('homepagelink',1,'',$this->_translator->getMessage('HOMEPAGE_CONFIGURATION_HOMEPAGE'),$this->_translator->getMessage('HOMEPAGE_CONFIGURATION_HOMEPAGE_VALUE'),'');
      if ($this->_with_homepage_new) {
         $this->_form->combine();
         $this->_form->addCheckbox('homepage_new',1,'','',$this->_translator->getMessage('HOMEPAGE_CONFIGURATION_HOMEPAGE_NEW_VALUE'),'');
      }
      $this->_form->addCheckbox('homepage_desc_link',1,'',$this->_translator->getMessage('HOMEPAGE_CONFIGURATION_LINK_ROOMDESC'),$this->_translator->getMessage('HOMEPAGE_CONFIGURATION_LINK_ROOMDESC_VALUE'),'');

      // buttons
      $this->_form->addButtonBar('option',$this->_translator->getMessage('PREFERENCES_SAVE_BUTTON'),'');
   }

   /** loads the selected and given values to the form
    * this methods loads the selected and given values to the form from the material item or the form_post data
    */
   function _prepareValues () {
      $this->_values = array();
      if (isset($this->_item)) {
         $this->_values['iid'] = $this->_item->getItemID();
         $this->_values['homepagelink'] = $this->_item->isHomepageLinkActive();
         if ( $this->_item->showHomepageDescLink() ) {
            $this->_values['homepage_desc_link'] = 1;
         }
      } elseif (isset($this->_form_post)) {
         $this->_values = $this->_form_post;
      }
   }
}
?>
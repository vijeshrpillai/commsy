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
class cs_configuration_room_options_form extends cs_rubric_form {

  /**
   * string - containing the headline of the form
   */
  var $_headline = NULL;
  var $_array_or_color_arrays = array();
  var $_with_logo = NULL;
  var $_community_array = array();
  var $_community_room_array = array();
  var $_shown_community_room_array = array();
  var $_session_community_room_array = array();
  var $_with_bg_image = false;

  /** constructor
    * the only available constructor
    *
    * @param object environment the environment object
    *
    * @author CommSy Development Group
    */
   function cs_configuration_room_options($params) {
      $this->cs_rubric_form($params);
   }

   function setCurrentColor($color){
      $this->_current_color = $color;
   }

   function setCurrentRubric($rubric){
      $this->_current_rubric = $rubric;
   }

   function setColorArray($color_array){
      $this->_color_array = $color_array;
   }

   function setSessionCommunityRoomArray ($value) {
      $this->_session_community_room_array = (array)$value;
   }


   /** init data for form, INTERNAL
    * this methods init the data for the form, for example groups
    *
    * @author CommSy Development Group
    */
   function _initForm () {

      $current_context_item = $this->_environment->getCurrentContextItem();

      /********Zuordnung********/
      $community_room_array = array();
      // links to community room
      $current_portal = $this->_environment->getCurrentPortalItem();
      $current_user = $this->_environment->getCurrentUserItem();
      $community_list = $current_portal->getCommunityList();
      $community_room_array = array();
      $temp_array['text'] = '*'.getMessage('PREFERENCES_NO_COMMUNITY_ROOM');
      $temp_array['value'] = '-1';
      $community_room_array[] = $temp_array;
      $temp_array['text'] = '--------------------';
      $temp_array['value'] = 'disabled';
      $community_room_array[] = $temp_array;
      unset($temp_array);
      if ($community_list->isNotEmpty()) {
         $community_item = $community_list->getFirst();
         while ($community_item) {
            $temp_array = array();
            if ($community_item->isAssignmentOnlyOpenForRoomMembers() ){
               if ( !$community_item->isUser($current_user)) {
                  $temp_array['text'] = $community_item->getTitle();
                  $temp_array['value'] = 'disabled';
               }else{
                  $temp_array['text'] = $community_item->getTitle();
                  $temp_array['value'] = $community_item->getItemID();
               }
            }else{
               $temp_array['text'] = $community_item->getTitle();
               $temp_array['value'] = $community_item->getItemID();
            }
            $community_room_array[] = $temp_array;
            unset($temp_array);
            $community_item = $community_list->getNext();
         }
      }
      $this->_community_room_array = $community_room_array;
      $community_room_array = array();

      if ($this->_environment->inProjectRoom()){
         if (!empty($this->_session_community_room_array)) {
            foreach ( $this->_session_community_room_array as $community_room ) {
               $temp_array['text'] = $community_room['name'];
               $temp_array['value'] = $community_room['id'];
               $community_room_array[] = $temp_array;
            }
         } else{
            $community_room_list = $current_context_item->getCommunityList();
            if ($community_room_list->getCount() > 0) {
               $community_room_item = $community_room_list->getFirst();
               while ($community_room_item) {
                  $temp_array['text'] = $community_room_item->getTitle();
                  $temp_array['value'] = $community_room_item->getItemID();
                  $community_room_array[] = $temp_array;
                  $community_room_item = $community_room_list->getNext();
               }
            }
         }
         $this->_shown_community_room_array = $community_room_array;
      }


      /**********Logo**********/
      $this->_with_logo = $current_context_item->getLogoFilename();
      $this->_with_bg_image = $current_context_item->getBGImageFilename();

      /****Beschreibung*****/
      $this->_languages = $this->_environment->getAvailableLanguageArray();
      if (isset($this->_form_post['description_text'])) {
         $this->_description_text = $this->_form_post['description_text'];
      } else{
         $this->_description_text = $current_context_item->getLanguage();
         if ( $this->_description_text == 'user' ) {
            $this->_description_text = 'de';
         }
      }

      /*******Farben********/
      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_DEFAULT');
      $temp_array['value'] = 'COMMON_COLOR_DEFAULT';
      $this->_array_info_text[] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = '-----';
      $temp_array['value'] = '-1';
      $this->_array_info_text[] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_1');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_1';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_1')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_2');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_2';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_2')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_3');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_3';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_3')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_4');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_4';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_4')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_5');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_5';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_5')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_6');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_6';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_6')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_7');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_7';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_7')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_8');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_8';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_8')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_9');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_9';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_9')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_10');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_10';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_10')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_11');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_11';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_11')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_12');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_12';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_12')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_13');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_13';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_13')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_14');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_14';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_14')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_15');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_15';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_15')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_16');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_16';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_16')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_17');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_17';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_17')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_18');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_18';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_18')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_19');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_19';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_19')] = $temp_array;

      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_20');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_20';
      $array_info_text_temp[getMessage('COMMON_COLOR_SCHEMA_20')] = $temp_array;

      ksort($array_info_text_temp);
      foreach($array_info_text_temp as $entry){
         $this->_array_info_text[] = $entry;
      }
      $temp_array = array();
      $temp_array['text']  = '-----';
      $temp_array['value'] = '-1';
      $this->_array_info_text[] = $temp_array;
      $temp_array = array();
      $temp_array['text']  = getMessage('COMMON_COLOR_SCHEMA_OWN');
      $temp_array['value'] = 'COMMON_COLOR_SCHEMA_OWN';
      $this->_array_info_text[] = $temp_array;

   }

   /** create the form, INTERNAL
    * this methods creates the form with the form definitions
    *
    * @author CommSy Development Group
    */
   function _createForm () {
      if (!$this->_environment->inPrivateRoom()){
         $this->_form->addTextField('title','',$this->_translator->getMessage('COMMON_ROOM_NAME'),'',60,48,true);
      }
      /********Sprache*******/
      $languageArray = array();
      $zaehler = 0;
      $languageArray[$zaehler]['text']  = $this->_translator->getMessage('CONTEXT_LANGUAGE_USER');
      $languageArray[$zaehler]['value'] = 'user';
      $zaehler++;
      $languageArray[$zaehler]['text']  = '-------';
      $languageArray[$zaehler]['value'] = 'disabled';
      $zaehler++;
      $tmpArray = $this->_environment->getAvailableLanguageArray();
      foreach ($tmpArray as $item){
         switch ( mb_strtoupper($item, 'UTF-8') ){
            case 'DE':
               $languageArray[$zaehler]['text']= $this->_translator->getMessage('DE');
               break;
            case 'EN':
               $languageArray[$zaehler]['text']= $this->_translator->getMessage('EN');
               break;
            default:
               break;
         }
         $languageArray[$zaehler]['value']= $item;
         $zaehler++;
      }
      $zaehler++;
      $message = $this->_translator->getMessage('CONTEXT_LANGUAGE_DESC2');
      $this->_form->addSelect('language',
                              $languageArray,
                              '',
                              $this->_translator->getMessage('CONTEXT_LANGUAGE'),
                              $message,
                              0,
                              false,
                              true,
                              false,
                              '',
                              '',
                              '',
                              '',
                              '16',
                              true
                             );

      /********Logo*******/
      $this->_form->addRoomLogo('logo',
                             '',
                             $this->_translator->getMessage('LOGO_UPLOAD'),
                             $this->_translator->getMessage('LOGO_UPLOAD_DESC'),
                             '',
                             false,
                             '4em'
                             );
      $this->_form->addHidden('logo_hidden','');
      $this->_form->addHidden('with_logo',$this->_with_logo);

      /**********Zuordnung**************/
      if ($this->_environment->inProjectRoom()){
         if ( !empty($this->_community_room_array) ) {
            $portal_item = $this->_environment->getCurrentPortalItem();
            $project_room_link_status = $portal_item->getProjectRoomLinkStatus();
            if ($project_room_link_status =='optional'){
               if ( !empty ($this->_shown_community_room_array) ) {
                  $this->_form->addCheckBoxGroup('communityroomlist',$this->_shown_community_room_array,'',getMessage('PREFERENCES_COMMUNITY_ROOMS'),'',false,false);
                  $this->_form->combine();
               }
               if(count($this->_community_room_array) > 2){
                  $this->_form->addSelect('communityrooms',$this->_community_room_array,'',getMessage('PREFERENCES_COMMUNITY_ROOMS'),'', 1, false,false,false,'','','','',16);
                  $this->_form->combine('horizontal');
                  $this->_form->addButton('option',getMessage('PREFERENCES_ADD_COMMUNITY_ROOMS_BUTTON'),'','',100);
               }
            }else{
               if ( !empty ($this->_shown_community_room_array) ) {
                  $this->_form->addCheckBoxGroup('communityroomlist',$this->_shown_community_room_array,'',getMessage('PREFERENCES_COMMUNITY_ROOMS'),'',false,false);
                  $this->_form->combine();
               }
               if(count($this->_community_room_array) > 2){
                  $this->_form->addSelect('communityrooms',$this->_community_room_array,'',getMessage('PREFERENCES_COMMUNITY_ROOMS'),'', 1, false,true,false,'','','','',16);
                  $this->_form->combine('horizontal');
                  $this->_form->addButton('option',getMessage('PREFERENCES_ADD_COMMUNITY_ROOMS_BUTTON'),'','',100);
               }
            }
         }
      }elseif($this->_environment->inCommunityRoom()){
         $radio_values = array();
         $radio_values[0]['text'] = getMessage('COMMON_ASSIGMENT_ON');
         $radio_values[0]['value'] = 'open';
         $radio_values[1]['text'] = getMessage('COMMON_ASSIGMENT_OFF');
         $radio_values[1]['value'] = 'closed';
         $this->_form->addRadioGroup('room_assignment',
                                     getMessage('PREFERENCES_ROOM_ASSIGMENT'),
                                     getMessage('PREFERENCES_ASSIGMENT_OPEN_FOR_GUESTS_DESC'),
                                     $radio_values,
                                     '',
                                     true,
                                     false
                                    );
         unset($radio_values);
      }
      /***************Farben************/
      if ( !empty($this->_form_post['color_choice']) and $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_OWN' ) {
          $this->_form->addEmptyLine();
      }
      $this->_form->addSelect( 'color_choice',
                               $this->_array_info_text,
                               '',
                               $this->_translator->getMessage('CONFIGURATION_COLOR_FORM_CHOOSE_TEXT'),
                               '',
                               '',
                               '',
                               '',
                               true,
                               $this->_translator->getMessage('COMMON_CHOOSE_BUTTON'),
                               'option',
                               '',
                               '',
                               '16',
                               true);
      if ( !empty($this->_form_post['color_choice']) ) {
         if ( $this->_form_post['color_choice']== 'COMMON_COLOR_DEFAULT' ) {
            $this->_form->combine();
            $desc = '<img src="images/commsyicons/color_themes/color_schema_default.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_DEFAULT').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']== 'COMMON_COLOR_SCHEMA_1' ) {
            $this->_form->combine();
            $desc = '<img src="images/commsyicons/color_themes/color_schema_1.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_1').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_3' ) {
            $this->_form->combine();
            $desc = '<img src="images/commsyicons/color_themes/color_schema_3.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_3').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_2' ) {
            $this->_form->combine();
            $desc = '<img src="images/commsyicons/color_themes/color_schema_2.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_2').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_4' ) {
            $this->_form->combine();
            $desc = '<img src="images/commsyicons/color_themes/color_schema_4.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_4').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_5' ) {
            $this->_form->combine();
            $desc = '<img src="images/commsyicons/color_themes/color_schema_5.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_5').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_6' ) {
            $this->_form->combine();
            $desc = '<img src="images/commsyicons/color_themes/color_schema_6.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_6').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_7' ) {
            $this->_form->combine();
            $desc = '<img src="images/commsyicons/color_themes/color_schema_7.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_7').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_8' ) {
            $this->_form->combine();
            $desc = '<img src="images/commsyicons/color_themes/color_schema_8.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_8').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_9' ) {
            $this->_form->combine();
            $desc = '<img src="images/commsyicons/color_themes/color_schema_9.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_9').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_10' ) {
            $this->_form->combine();
            $desc = '<img src="images/commsyicons/color_themes/color_schema_10.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_10').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_11' ) {
            $this->_form->combine();
            $desc = '<img src="images/commsyicons/color_themes/color_schema_11.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_11').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_12'  ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_12.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_12').'" style=" border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_13'  ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_13.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_13').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_14'  ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_14.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_14').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_15'  ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_15.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_15').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_16'  ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_16.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_16').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_17'  ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_17.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_17').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_18'  ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_18.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_18').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_19'  ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_19.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_19').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_20'  ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_20.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_20').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $this->_form_post['color_choice']=='COMMON_COLOR_SCHEMA_OWN' ) {
            $this->_form->addTextField('color_1','',$this->_translator->getMessage('COMMON_COLOR_101'),'','',10);
            $this->_form->addTextField('color_2','',$this->_translator->getMessage('COMMON_COLOR_102'),'','',10);
            $this->_form->addTextField('color_3','',$this->_translator->getMessage('COMMON_COLOR_103'),'','',10);
            $this->_form->addTextField('color_4','',$this->_translator->getMessage('COMMON_COLOR_104'),'','',10);
            $this->_form->addTextField('color_5','',$this->_translator->getMessage('COMMON_COLOR_105'),'','',10);
            $this->_form->addTextField('color_6','',$this->_translator->getMessage('COMMON_COLOR_106'),'','',10);
            $this->_form->addTextField('color_7','',$this->_translator->getMessage('COMMON_COLOR_107'),'','',10);
            $this->_form->addRoomLogo('bgimage',
                             '',
                             $this->_translator->getMessage('BG_IMAGE_UPLOAD'),
                             $this->_translator->getMessage('BG_IMAGE_UPLOAD_DESC'),
                             '',
                             false,
                             '4em'
                             );
            $this->_form->combine();
            $this->_form->addCheckbox('bg_image_repeat',1,'',$this->_translator->getMessage('CONFIGURATION_BGIMAGE_REPEAT'),$this->_translator->getMessage('CONFIGURATION_BGIMAGE_REPEAT'));
            $this->_form->addHidden('bgimage_hidden','');
            $this->_form->addHidden('with_bgimage',$this->_with_logo);
            $this->_form->addText('colorpicker','','<br/><br/><INPUT class=color value=#45D7DD>');
            $this->_form->addEmptyLine();
         }
      } else{
         $this->_form->combine();
         $context_item = $this->_environment->getCurrentContextItem();
         $color = $context_item->getColorArray();
         if ( $color['schema']== 'DEFAULT' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_default.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_DEFAULT').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ($color['schema']== 'SCHEMA_1' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_1.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_1').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_3' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_3.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_3').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_2' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_2.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_2').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_4' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_4.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_4').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_5' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_5.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_5').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_6' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_6.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_6').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_7' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_7.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_7').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_8' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_8.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_8').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_9' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_9.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_9').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_10' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_10.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_10').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_11' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_11.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_11').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_12' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_12.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_12').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_13' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_13.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_13').'" style=" border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_14' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_14.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_14').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_15' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_15.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_15').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_16' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_16.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_16').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_17' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_17.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_17').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_18' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_18.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_18').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_19' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_19.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_19').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_20' ) {
            $desc = '<img src="images/commsyicons/color_themes/color_schema_20.gif" alt="'.$this->_translator->getMessage('COMMON_COLOR_SCHEMA_20').'" style="border:1px solid black; vertical-align: middle;"/>';
            $this->_form->addText('example',$this->_translator->getMessage('COMMON_COLOR_EXAMPLE'),$desc);
         }elseif ( $color['schema']=='SCHEMA_OWN' ) {
            $this->_form->addTextField('color_1','',$this->_translator->getMessage('COMMON_COLOR_101'),'','',10);
            $this->_form->addTextField('color_2','',$this->_translator->getMessage('COMMON_COLOR_102'),'','',10);
            $this->_form->addTextField('color_3','',$this->_translator->getMessage('COMMON_COLOR_103'),'','',10);
            $this->_form->addTextField('color_4','',$this->_translator->getMessage('COMMON_COLOR_104'),'','',10);
            $this->_form->addTextField('color_5','',$this->_translator->getMessage('COMMON_COLOR_105'),'','',10);
            $this->_form->addTextField('color_6','',$this->_translator->getMessage('COMMON_COLOR_106'),'','',10);
            $this->_form->addTextField('color_7','',$this->_translator->getMessage('COMMON_COLOR_107'),'','',10);
            $this->_form->addRoomLogo('bgimage',
                             '',
                             $this->_translator->getMessage('BG_IMAGE_UPLOAD'),
                             $this->_translator->getMessage('BG_IMAGE_UPLOAD_DESC'),
                             '',
                             false,
                             '4em'
                             );
            $this->_form->combine();
            $this->_form->addCheckbox('bg_image_repeat',1,'',$this->_translator->getMessage('CONFIGURATION_BGIMAGE_REPEAT'),$this->_translator->getMessage('CONFIGURATION_BGIMAGE_REPEAT'));
            $this->_form->addHidden('bgimage_hidden','');
            $this->_form->addHidden('with_bgimage',$this->_with_logo);
            $this->_form->addText('colorpicker','','<br/><br/><INPUT class=color value=#45D7DD>');
            $this->_form->addEmptyLine();
         }
      }

      // switch CommSy6 and CommSy7
      if ( $this->_environment->inProjectRoom()
           or $this->_environment->inCommunityRoom()
           or $this->_environment->inPrivateRoom()
           or $this->_environment->inPortal()
           or $this->_environment->inServer()
         ) {
         $value_array = array();
         $temp_array = array();
         $temp_array['text'] = 'CommSy6';
         $temp_array['value'] = '6';
         $value_array[] = $temp_array;
         unset($temp_array);
         $temp_array = array();
         $temp_array['text'] = 'CommSy7';
         $temp_array['value'] = '7';
         $value_array[] = $temp_array;
         unset($temp_array);
         $this->_form->addSelect('design',$value_array,'',$this->_translator->getMessage('CONFIGURATION_COLOR_DESIGN'),'');
      }

      /*****Beschreibung****/
      $languageArray = array();
      $tmpArray = $this->_environment->getAvailableLanguageArray();
      $zaehler = 0;
      foreach ($tmpArray as $item){
         switch ( mb_strtoupper($item, 'UTF-8') ){
            case 'DE':
               $languageArray[$zaehler]['text']= $this->_translator->getMessage('DE');
               break;
            case 'EN':
               $languageArray[$zaehler]['text']= $this->_translator->getMessage('EN');
               break;
            default:
               break;
         }
         $languageArray[$zaehler]['value']= $item;
         $zaehler++;
      }
      $this->_form->addSelect( 'description_text',
                               $languageArray,
                               '',
                               $this->_translator->getMessage('CONFIGURATION_CHOOSE_LANGUAGE'),
                               '',
                               '',
                               '',
                               '',
                               true,
                               $this->_translator->getMessage('COMMON_LANGUAGE_CHOOSE_BUTTON'),
                               'option','','','16',true);

      $this->_form->combine();
      $context_item = $this->_environment->getCurrentContextItem();
      foreach ($this->_languages as $language) {
         if ($language == $this->_description_text){
            $html_status = $context_item->getHtmlTextAreaStatus();
            if ($html_status =='1'){
               $html_status ='2';
            }
            $this->_form->addTextArea('description_'.$language,'','','','','5','virtual',false,false,true,$html_status);
         } else {
            $this->_form->addHidden('description_'.$language,'');
         }
      }

      /******** buttons***********/
      $this->_form->addButtonBar('option',$this->_translator->getMessage('PREFERENCES_SAVE_BUTTON'),'');

   }

   /** loads the selected and given values to the form
    * this methods loads the selected and given values to the form from the material item or the form_post data
    */
   function _prepareValues () {
      $context_item = $this->_environment->getCurrentContextItem();

      $this->_values = array();
      // switch CommSy6 / CommSy7
      if ( $context_item->isDesign7() ) {
         $this->_values['design'] = 7;
      } else {
         $this->_values['design'] = 6;
      }
      $color = $context_item->getColorArray();
      $temp_array = array();
      $temp_array['color_1'] = $color['tabs_background'];
      $temp_array['color_2'] = $color['tabs_focus'];
      $temp_array['color_3'] = $color['tabs_title'];
      $temp_array['color_4'] = $color['content_background'];
      $temp_array['color_5'] = $color['boxes_background'];
      $temp_array['color_6'] = $color['hyperlink'];
      $temp_array['color_7'] = $color['list_entry_even'];
      if ( !empty($this->_form_post) ) {
         $this->_values = $this->_form_post;
         if (empty($this->_values['color_choice'])){
            $this->_values['color_choice'] = 'COMMON_COLOR_'.mb_strtoupper($color['schema'], 'UTF-8');
         }
         if ($this->_values['color_choice']=='COMMON_COLOR_SCHEMA_OWN'){
            for ($i=1; $i<8; $i++){
               if ( !empty($this->_form_post['color_'.$i]) ){
                  $this->_values['color_'.$i] = $this->_form_post['color_'.$i];
               }else{
                  $this->_values['color_'.$i] = $temp_array['color_'.$i];
               }
            }
         }
      } else {
         $color_array = $context_item->getColorArray();
         $this->_values['color_choice'] = 'COMMON_COLOR_'.mb_strtoupper($color['schema'], 'UTF-8');
         $this->_values['color_1'] = $color['tabs_background'];
         $this->_values['color_2'] = $color['tabs_focus'];
         $this->_values['color_3'] = $color['tabs_title'];
         $this->_values['color_5'] = $color['boxes_background'];
         $this->_values['color_7'] = $color['list_entry_even'];
         $this->_values['color_6'] = $color['hyperlink'];
         $this->_values['color_4'] = $color['content_background'];
         $this->_values['title'] = $context_item->getTitle();
         if ($context_item->isPrivateRoom() and $context_item->getTitle() == 'PRIVATEROOM' ){
            $this->_values['title'] = $this->_translator->getMessage('COMMON_PRIVATEROOM');
         }
         if ($context_item->isAssignmentOnlyOpenForRoomMembers()) {
            $this->_values['room_assignment'] = 'closed';
         } else {
            $this->_values['room_assignment'] = 'open';
         }
      }
      if ($context_item->getLogoFilename()){
         $this->_values['logo'] = $context_item->getLogoFilename();
      }
      if ($context_item->getBGImageFilename()){
         $this->_values['bgimage'] = $context_item->getBGImageFilename();
      }
      if ($context_item->issetBGImageRepeat()){
         $this->_values['bg_image_repeat'] = '1';
      }
      $this->_values['language'] = $context_item->getLanguage();
      if ($this->_environment->inProjectRoom()){
         $community_room_array = array();
         if (!empty($this->_session_community_room_array)) {
            foreach ( $this->_session_community_room_array as $community_room ) {
               $community_room_array[] = $community_room['id'];
            }
         }
         $community_room_list = $context_item->getCommunityList();
         if ($community_room_list->getCount() > 0) {
            $community_room_item = $community_room_list->getFirst();
            while ($community_room_item) {
               $community_room_array[] = $community_room_item->getItemID();
               $community_room_item = $community_room_list->getNext();
            }
         }
         if ( isset($this->_form_post['communityroomlist']) ) {
            $this->_values['communityroomlist'] = $this->_form_post['communityroomlist'];
         } else {
            $this->_values['communityroomlist'] = $community_room_array;
         }
      }

      $description_array = $context_item->getDescriptionArray();
      $languages = $this->_environment->getAvailableLanguageArray();
      foreach ($languages as $language) {
         if (!empty($description_array[cs_strtoupper($language)])) {
            $this->_values['description_'.$language] = $description_array[cs_strtoupper($language)];
         } else {
            $this->_values['description_'.$language] = '';
         }
      }

   }

   function _checkValues () {
      $portal_item = $this->_environment->getCurrentPortalItem();
      if (isset($portal_item) ) {
         $project_room_link_status = $portal_item->getProjectRoomLinkStatus();
         if ( isset($this->_form_post['communityrooms']) and $project_room_link_status !='optional'){
            if ( ($this->_form_post['communityrooms'] == -1 or $this->_form_post['communityrooms'] == 'disabled' or $this->_form_post['communityrooms']=='--------------------') and !isset($this->_form_post['communityroomlist']) ){
               $this->_form->setFailure('communityrooms','mandatory');
               $this->_error_array[] = getMessage('COMMON_ERROR_COMMUNITY_ROOM_ENTRY',getMessage('PREFERENCES_COMMUNITY_ROOMS'));
            }
         }
      }
   }

}
?>
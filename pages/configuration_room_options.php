<?PHP
// $Id$
//
// Release $Name$
//
// Copyright (c)2002-2003 Matthias Finck, Dirk Fust, Oliver Hankel, Iver Jackewitz, Michael Janneck,
// Martti Jeenicke, Detlev Krause, Irina L. Marinescu, Timo Nolte, Bernd Pape,
// Edouard Simon, Monique Strauss, Jos� Manuel Gonz�lez V�zquez
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

include_once('functions/curl_functions.php');

if (!empty($_POST['option'])) {
   $command = $_POST['option'];
} else {
   $command = '';
}
$is_saved = false;

$context_item = $environment->getCurrentContextItem();


// Check access rights
if ($current_user->isGuest()) {
   if (!$context_item->isOpenForGuests()) {
      redirect($environment->getCurrentPortalId(),'home','index','');
   } else {
      $params = array() ;
      $params['cid'] = $context_item->getItemId();
      redirect($environment->getCurrentPortalId(),'home','index',$params);
   }
} elseif ( $context_item->isProjectRoom() and !$context_item->isOpen() ) {
   $params = array();
   $params['environment'] = $environment;
   $params['with_modifying_actions'] = true;
   $errorbox = $class_factory->getClass(ERRORBOX_VIEW,$params);
   unset($params);
   $errorbox->setText(getMessage('PROJECT_ROOM_IS_CLOSED', $context_item->getTitle()));
   $page->add($errorbox);
   $command = 'error';
} elseif (!$current_user->isModerator()) {
   $params = array();
   $params['environment'] = $environment;
   $params['with_modifying_actions'] = true;
   $errorbox = $class_factory->getClass(ERRORBOX_VIEW,$params);
   unset($params);
   $errorbox->setText(getMessage('ACCESS_NOT_GRANTED'));
   $page->add($errorbox);
   $command = 'error';
}

if ($command != 'error') { // only if user is allowed to edit colors

   $class_params= array();
   $class_params['environment'] = $environment;
   $form = $class_factory->getClass(CONFIGURATION_ROOM_OPTIONS_FORM,$class_params);
   unset($class_params);
   $params = array();
   $params['environment'] = $environment;
   $params['with_modifying_actions'] = true;
   $form_view = $class_factory->getClass(CONFIGURATION_FORM_VIEW,$params);
   unset($params);

/*   if ( isOption($command, getMessage('PREFERENCES_ADD_COMMUNITY_ROOMS_BUTTON')) ) {
      $post_community_room_array = array();
      $focus_element_onload = 'communityrooms';
      $post_community_room_ids = array();
      $new_community_room_ids = array();
      $new_buzzword_ids = array();
      $current_iid = $context_item->getItemID();
      $post_community_room_array = array();
      $community_old_room_array[] = array();
      if ( isset($_POST['communityroomlist']) ) {
         $post_community_room_ids = $_POST['communityroomlist'];
      }
      if ( !empty($_POST['communityrooms']) and $_POST['communityrooms']!=-1 and $_POST['communityrooms']!='disabled' and !in_array($_POST['communityrooms'],$post_community_room_ids) ) {
         $temp_array = array();
         $community_manager = $environment->getCommunityManager();
         $community_manager->reset();
         $community_item = $community_manager->getItem($_POST['communityrooms']);
         $temp_array['name'] = $community_item->getTitle();
         $temp_array['id'] = $community_item->getItemID();
         $community_room_array[] = $temp_array;
         $new_community_room_ids[] = $temp_array['id'];
      }
      $post_community_room_ids = array_merge($post_community_room_ids, $new_community_room_ids);
      foreach($post_community_room_ids as $ids){
         $community_manager = $environment->getCommunityManager();
         $community_manager->reset();
         $community_item = $community_manager->getItem($ids);
         $temp_array['name'] = $community_item->getTitle();
         $temp_array['id'] = $community_item->getItemID();
         $post_community_room_array[] = $temp_array;
      }
   }*/

   if ( !empty($_POST)) {
      if ($_POST['color_choice']=='-1'){
         $_POST['color_choice'] = '';
      }
      $values = $_POST;
/*      if ( isset($post_community_room_ids) AND !empty($post_community_room_ids) ) {
         $values['communityroomlist'] = $post_community_room_ids;
         $form->setSessionCommunityRoomArray($post_community_room_array);
      }*/
      $form->setFormPost($values);
   } elseif ( isset($context_item) ) {
      $form->setItem($context_item);
   }

   $form->prepareForm();
   $form->loadValues();

   if ( !empty($command) and isOption($command, getMessage('COMMON_CANCEL_BUTTON')) ) {
     redirect($environment->getCurrentContextID(),'configuration', 'index', '');
   }
   // Save item
   elseif ( !empty($command) and isOption($command, getMessage('PREFERENCES_SAVE_BUTTON')) ) {
      $correct = $form->check();
      if ($correct){
         if ( isset($_POST['title']) ) {
            $context_item->setTitle($_POST['title']);
         }
         $color = $context_item->getColorArray();
         if ( isset($_POST['color_choice'])) {
            global $cs_color;
            if ($_POST['color_choice']=='COMMON_COLOR_SCHEMA_1'){
               $color = $cs_color['SCHEMA_1'];
            }elseif ($_POST['color_choice']=='COMMON_COLOR_SCHEMA_2'){
               $color = $cs_color['SCHEMA_2'];
            }elseif ($_POST['color_choice']=='COMMON_COLOR_DEFAULT'){
               $color = $cs_color['DEFAULT'];
            }elseif ($_POST['color_choice']=='COMMON_COLOR_SCHEMA_3'){
               $color = $cs_color['SCHEMA_3'];
            }elseif ($_POST['color_choice']=='COMMON_COLOR_SCHEMA_4'){
               $color = $cs_color['SCHEMA_4'];
            }elseif ($_POST['color_choice']=='COMMON_COLOR_SCHEMA_5'){
               $color = $cs_color['SCHEMA_5'];
            }elseif ($_POST['color_choice']=='COMMON_COLOR_SCHEMA_6'){
               $color = $cs_color['SCHEMA_6'];
            }elseif ($_POST['color_choice']=='COMMON_COLOR_SCHEMA_7'){
               $color = $cs_color['SCHEMA_7'];
            }elseif ($_POST['color_choice']=='COMMON_COLOR_SCHEMA_8'){
               $color = $cs_color['SCHEMA_8'];
            }elseif ($_POST['color_choice']=='COMMON_COLOR_SCHEMA_9'){
               $color = $cs_color['SCHEMA_9'];
            }elseif ($_POST['color_choice']=='COMMON_COLOR_SCHEMA_10'){
               $color = $cs_color['SCHEMA_10'];
            }elseif ($_POST['color_choice']=='COMMON_COLOR_SCHEMA_11'){
               $color = $cs_color['SCHEMA_11'];
            }elseif ($_POST['color_choice']=='COMMON_COLOR_SCHEMA_12'){
               $color = $cs_color['SCHEMA_12'];
            }elseif ($_POST['color_choice']=='COMMON_COLOR_SCHEMA_13'){
               $color = $cs_color['SCHEMA_13'];
            }elseif ($_POST['color_choice']=='COMMON_COLOR_SCHEMA_OWN'){
               if (!empty($_POST['color_1'])){
                  $color['tabs_background'] = $_POST['color_1'];
               }else{
                  $color['tabs_background'] = '#FFFFFF';
               }
               if (!empty($_POST['color_2'])){
                  $color['tabs_focus'] = $_POST['color_2'];
               }else{
                  $color['tabs_focus'] = '#CCCCCC';
               }
               if (!empty($_POST['color_3'])){
                  $color['tabs_title'] = $_POST['color_3'];
               }else{
                  $color['tabs_title'] = '#000000';
               }
               if (!empty($_POST['color_4'])){
                  $color['content_background'] = $_POST['color_4'];
               }else{
                  $color['content_background'] = '#B0B0B0';
               }
               if (!empty($_POST['color_5'])){
                  $color['boxes_background'] = $_POST['color_5'];
               }else{
                  $color['boxes_background'] = '#FFFFFF';
               }
               if (!empty($_POST['color_6'])){
                  $color['hyperlink'] = $_POST['color_6'];
               }else{
                  $color['hyperlink'] = '#1E2273';
               }
               if (!empty($_POST['color_7'])){
                  $color['list_entry_even'] = $_POST['color_7'];
               }else{
                  $color['list_entry_even'] = '#B0B0B0';
               }
               $color['table_background'] = $color['content_background'];
               $color['headline_text'] = $color['tabs_title'];
               $color['list_entry_odd'] = '#FFFFFF';
               $color['date_title'] = '#EC930D';
               $color['info_color'] = '#827F76';
               $color['disabled'] = '#B0B0B0';
               $color['warning'] = '#FC1D12';
               $color['schema']='SCHEMA_OWN';
               // logo: save and/or delete current logo
               if ( isset($_POST['delete_bgimage']) ) {
                  $disc_manager = $environment->getDiscManager();
                  if ( $disc_manager->existsFile($context_item->getBGImageFilename()) ) {
                     $disc_manager->unlinkFile($context_item->getBGImageFilename());
                  }
                  $context_item->setBGImageFilename('');
               }
               if ( !empty($_FILES['bgimage']['name']) ) {
                  $bg_image = $context_item->getBGImageFilename();
                  $disc_manager = $environment->getDiscManager();
                  if ( !empty ($bg_image) ) {
                     if ( $disc_manager->existsFile($context_item->getBGImageFilename()) ) {
                        $disc_manager->unlinkFile($context_item->getBGImageFilename());
                     }
                     $context_item->setBGImageFilename('');
                  }
                  $filename = 'cid'.$environment->getCurrentContextID().'_bgimage_'.$_FILES['bgimage']['name'];
                  $disc_manager->copyFile($_FILES['bgimage']['tmp_name'],$filename,true);
                  $context_item->setBGImageFilename($filename);
               }
               if (!empty($_POST['bg_image_repeat'])){
                  $context_item->setBGImageRepeat();
               }else{
                  $context_item->unsetBGImageRepeat();
               }

            }
         }
         // logo: save and/or delete current logo
         if ( isset($_POST['delete_logo']) ) {
            $disc_manager = $environment->getDiscManager();
            if ( $disc_manager->existsFile($context_item->getLogoFilename()) ) {
               $disc_manager->unlinkFile($context_item->getLogoFilename());
            }
            $context_item->setLogoFilename('');
         }
         if ( !empty($_FILES['logo']['name']) ) {
            $logo = $context_item->getLogoFilename();
            $disc_manager = $environment->getDiscManager();
            if ( !empty ($logo) ) {
               if ( $disc_manager->existsFile($context_item->getLogoFilename()) ) {
                  $disc_manager->unlinkFile($context_item->getLogoFilename());
               }
               $context_item->setLogoFilename('');
            }
            $filename = 'cid'.$environment->getCurrentContextID().'_logo_'.$_FILES['logo']['name'];
            $disc_manager->copyFile($_FILES['logo']['tmp_name'],$filename,true);
            $context_item->setLogoFilename($filename);
         }


/*         $community_room_array = array();
         if ( isset($_POST['communityroomlist']) ) {
            $community_room_array = $_POST['communityroomlist'];
         }
         if ( isset($_POST['communityrooms']) and !in_array($_POST['communityrooms'],$community_room_array) and $_POST['communityrooms'] > 0) {
            $community_room_array[] = $_POST['communityrooms'];
         }
         $context_item->setCommunityListByID($community_room_array);*/
         $context_item->setColorArray($color);

         if ( isset($_POST['language']) ) {
            $language = $_POST['language'];
            if ($_POST['language'] == 'enabled') {
               $language = 'user';
            }
            $old_language = $context_item->getLanguage();
            if ( $old_language != $language ) {
               $context_item->setLanguage($language);
               $environment->unsetSelectedLanguage();
            }
         }
         $context_item->save();
         $environment->setCurrentContextItem($context_item);
          // save room_item
   #      redirect($environment->getCurrentContextID(),'configuration', 'index', '');
         $class_params= array();
         $class_params['environment'] = $environment;
         $form = $class_factory->getClass(CONFIGURATION_ROOM_OPTIONS_FORM,$class_params);
         unset($class_params);
         $form->setItem($context_item);
         $form->prepareForm();
         $form->loadValues();
         $form_view->setItemIsSaved();
         $is_saved = true;
      }
   }


   $form_view->setAction(curl($environment->getCurrentContextID(),$environment->getCurrentModule(),$environment->getCurrentFunction(),''));
   $form_view->setForm($form);
   if ( $environment->inPortal() or $environment->inServer() ){
      $page->addForm($form_view);
   }else{
      $page->add($form_view);
   }
}
?>
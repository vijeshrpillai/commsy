<?php
// $Id$
//
// Release $Name$
//
// Copyright (c)2002-2003 Matthias Finck, Dirk Fust, Oliver Hankel, Iver Jackewitz, Michael Janneck,
// Martti Jeenicke, Detlev Krause, Irina L. Marinescu, Timo Nolte, Bernd Pape,
// Edouard Simon, Monique Strauss, JosÃ© Manuel GonzÃ¡lez VÃ¡zquez
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

if (!empty($_GET['iid'])) {
   $current_item_iid = $_GET['iid'];
} else {
   include_once('functions/error_functions.php');
   trigger_error('An item id must be given.', E_USER_ERROR);
}
// Find out what to do
if ( isset($_POST['delete_option']) ) {
   $delete_command = $_POST['delete_option'];
}elseif ( isset($_GET['delete_option']) ) {
   $delete_command = $_GET['delete_option'];
} else {
   $delete_command = '';
}
if ( isset($_GET['action']) and $_GET['action'] == 'delete' ) {
   $params = $environment->getCurrentParameterArray();
   $page->addDeleteBox(curl($environment->getCurrentContextID(),module2type($environment->getCurrentModule()),'detail',$params));
}
// Cancel editing
if ( isOption($delete_command, getMessage('COMMON_CANCEL_BUTTON')) ) {
   $params = $environment->getCurrentParameterArray();
   $anchor = '';
   if ( isset($_GET['section_action']) and $_GET['section_action'] == 'delete' ) {
     $anchor = 'anchor'.$params['section_iid'];
      unset($params['action']);
      unset($params['section_action']);
      unset($params['section_iid']);
      unset($params['ref_vid']);
   }elseif ( isset($_GET['annotation_action']) and $_GET['annotation_action'] == 'delete' ) {
     $anchor = 'anchor'.$params['annotation_iid'];
      unset($params['action']);
      unset($params['annotation_action']);
      unset($params['annotation_iid']);
   }elseif ( isset($_GET['discarticle_action']) and $_GET['discarticle_action'] == 'delete' ) {
      $anchor = 'anchor'.$params['discarticle_iid'];
      unset($params['action']);
      unset($params['discarticle_action']);
      unset($params['discarticle_iid']);
   }elseif ( isset($_GET['step_action']) and $_GET['step_action'] == 'delete' ) {
      $anchor = 'anchor'.$params['step_iid'];
      unset($params['action']);
      unset($params['step_action']);
      unset($params['step_iid']);
   }elseif ( isset($_GET['del_version']) ) {
      unset($params['action']);
//      $params['version_id'] = $_GET['del_version'];
      unset($params['del_version']);
   }else{
      $params['iid'] = $current_item_iid;
   }
   unset($params['action']);
   redirect($environment->getCurrentContextID(), $environment->getCurrentModule(), 'detail', $params,$anchor);
}
// Delete item
elseif ( isOption($delete_command, getMessage('COMMON_DELETE_BUTTON')) ) {
   if ( isset($_GET['section_action']) and $_GET['section_action'] == 'delete' ) {
      $params = $environment->getCurrentParameterArray();
      $section_manager = $environment->getSectionManager();
      $section_item = $section_manager->getItem($params['section_iid']);
      $params = array();
      $params['iid'] = $current_item_iid;
      $section_item->deleteVersion();
      $material_item = $section_item->getLinkedItem();
      $material_item->setModificationDate(getCurrentDateTimeInMySQL());
      $material_item->save();
      redirect($environment->getCurrentContextID(), $environment->getCurrentModule(), 'detail', $params);
    }elseif ( isset($_GET['annotation_action']) and $_GET['annotation_action'] == 'delete' ) {
      $params = $environment->getCurrentParameterArray();
      $annotation_manager = $environment->getAnnotationManager();
      $annotation_item = $annotation_manager->getItem($params['annotation_iid']);
      $params = array();
      $params['iid'] = $current_item_iid;
      $annotation_item->delete();
      redirect($environment->getCurrentContextID(), $environment->getCurrentModule(), 'detail', $params);
    }elseif ( isset($_GET['discarticle_action']) and $_GET['discarticle_action'] == 'delete' ) {
      $params = $environment->getCurrentParameterArray();
      $discarticle_manager = $environment->getDiscussionArticlesManager();
      $discarticle_item = $discarticle_manager->getItem($params['discarticle_iid']);
      unset($discarticle_manager);
      $discussion_item = $discarticle_item->getLinkedItem();
      $disc_type = $discussion_item->getDiscussionType();
      $delete_discussion = false;
      if ( $disc_type == 'threaded' ) {
         $position = $discarticle_item->getPosition();
         if ($position == 1) {
            $delete_discussion = true;
         }
      }
      $params = array();
      $params['iid'] = $current_item_iid;
      $discarticle_item->delete();
      unset($discarticle_item);
      $discussion_item->setModificationDate(getCurrentDateTimeInMySQL());
      $discussion_item->save();
      $funct = 'detail';
      if ($delete_discussion) {
         $discussion_item->delete();
         unset($discussion_item);
         $funct = 'index';
         $params = array();
      }
      redirect($environment->getCurrentContextID(), $environment->getCurrentModule(), $funct, $params);
    }elseif ( isset($_GET['step_action']) and $_GET['step_action'] == 'delete' ) {
      $params = $environment->getCurrentParameterArray();
      $step_manager = $environment->getStepManager();
      $step_item = $step_manager->getItem($params['step_iid']);
      unset($step_manager);
      $params = array();
      $params['iid'] = $current_item_iid;
      $step_item->delete();
      $funct = 'detail';
      $todo_item = $step_item->getLinkedItem();
      $todo_item->setModificationDate(getCurrentDateTimeInMySQL());
      $todo_item->save();
      unset($step_item);
      redirect($environment->getCurrentContextID(), $environment->getCurrentModule(), $funct, $params);
    }else{
      if ( $environment->getCurrentModule() == CS_MATERIAL_TYPE){
          if ( isset($_GET['del_version']) ) {
             $material_manager = $environment->getMaterialManager();
             $material_version_list = $material_manager->getVersionList($current_item_iid);
             $latest_version_item = $material_version_list->getFirst();
             $old_version_item = $material_version_list->getNext();
             while ($old_version_item ) {
                if ( $_GET['del_version'] == $old_version_item->getVersionID() ) {
                   $old_version_item->delete();
                   break;
                }
                $old_version_item = $material_version_list->getNext();
             }
             $params = array();
             $params['iid'] = $current_item_iid;
             redirect($environment->getCurrentContextID(), 'material', 'detail', $params);
          } else {
             $material_manager = $environment->getMaterialManager();
             $material_version_list = $material_manager->getVersionList($current_item_iid);
             $item = $material_version_list->getFirst();
             $item->delete(CS_ALL); // CS_ALL -> delete all versions of the material
          }
      }else{
         $manager = $environment->getManager(module2type($environment->getCurrentModule()));
         $item = $manager->getItem($current_item_id);
         $item->delete();
      }
      redirect($environment->getCurrentContextID(), $environment->getCurrentModule(), 'index', '');
   }
}

// room archive
elseif ( isOption($delete_command, getMessage('ROOM_ARCHIV_BUTTON')) ) {
   $manager = $environment->getRoomManager();
   $item = $manager->getItem($current_item_id);
   $item->close();
   $item->save();
   if ( $environment->getCurrentModule() == CS_PROJECT_TYPE
        and $environment->inCommunityRoom()
      ) {
      $params = array();
      if (isset($item)) {
         $params['iid'] = $item->getItemID();
         redirect($environment->getCurrentContextID(),CS_PROJECT_TYPE,'detail',$params);
         unset($params);
      } else {
         redirect($environment->getCurrentContextID(),CS_PROJECT_TYPE,'index','');
      }
   } elseif ($environment->getCurrentModule() == CS_MYROOM_TYPE) {
      $params = array();
      if (isset($item)) {
         $params['iid'] = $item->getItemID();
         redirect($environment->getCurrentContextID(),CS_MYROOM_TYPE,'detail',$params);
         unset($params);
      } else {
         redirect($environment->getCurrentContextID(),CS_MYROOM_TYPE,'index','');
      }
   } else {
      $session = $environment->getSessionItem();
      $history = $session->getValue('history');
      redirect($environment->getCurrentContextID(),$environment->getCurrentModule(),$environment->getCurrentFunction(),'');
   }
}
?>
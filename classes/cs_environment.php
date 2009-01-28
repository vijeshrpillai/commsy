<?PHP
// $Id$
//
// Release $Name$
//
// Copyright (c)2002-2003 Matthias Finck, Dirk Fust, Oliver Hankel, Iver Jackewitz, Michael Janneck,
// Martti Jeenicke, Detlev Krause, Irina L. Marinescu, Timo Nolte, Bernd Pape,
// Edouard Simon, Monique Strauss, Jose Mauel Gonzalez Vazquez, Johannes Schultze
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

/** upper class of all managers
 */
include_once('classes/cs_manager.php');
include_once('functions/text_functions.php');

   /** This class returns an instance of a cs_mananger subclass on request.
   *It also contains often needed environment variables.
   */
class cs_environment {
  /**
   * array - containing the objects
   */
   var $instance = array();
  /**
   * cs_user_item - containing the current user
   */
   var $current_user;
   var $_portal_user = NULL;

  /**
   * integer - id of current room
   */
   var $current_context_id = 0;
  /**
   * cs_context_item - current room
   */
   var $current_context = NULL;

  /**
   * object - obejct of server
   */
   var $_current_portal = NULL;

   var $_current_portal_id = 0;

  /**
   * object - obejct of server
   */
   var $_server_item = NULL;

   var $_server_id = 99;

  /**
   * string - current module name
   */
   var $current_module;
  /**
   * string - current function name
   */
   var $current_function;

  /**
   * string - current parameter of the page
   */
   var $_current_parameter_string = NULL;
   var $_current_parameter_array = NULL;

  /**
   * string - selected language of the current user
   */
   var $_selected_language = '';

   var $_browser = NULL;
   var $_browser_version = NULL;

   var $_plugin_class_array = NULL;

   private $session_id = NULL;
   private $session_item = NULL;
   private $_db_mysql_connector = NULL;
   private $_cache_on = true;
   private $_output_mode = 'html';

  /** constructor: cs_environment
   * the only available constructor, initial values for internal variables
   *
   * @author CommSy Development Group
   */


  /** get the current user
   * returns the current user. If there is no current user it will be returned an emtpy user_item.
   *
   * @return object                                cs_user_item
   */
   function getCurrentUserItem () {
      if ( !isset($this->current_user) ) {
         require_once('classes/cs_user_item.php');
         $this->current_user = new cs_user_item($this);
      }
      return $this->current_user;
   }

   function getPortalUserItem () {
      if (!isset($this->_portal_user)) {
         $current_user = $this->getCurrentUserItem();
         if ($current_user->isRoot() or $this->inPortal()) {
            $this->_portal_user = $current_user;
         } else {
            $manager = $this->getUserManager();
            $manager->resetLimits();
            $manager->setContextLimit($this->getCurrentPortalID());
            $manager->setUserIDLimit($current_user->getUserID());
            $manager->setAuthSourceLimit($current_user->getAuthSource());
            $manager->select();
            $list = $manager->get();
            if ($list->isNotEmpty() and $list->getCount() == 1) {
               $this->_portal_user = $list->getFirst();
            }
         }
      }
      return $this->_portal_user;
   }

   function getCurrentUser () {
      return $this->getCurrentUserItem();
   }

   function setCurrentUser ($current_user) {
       $this->setCurrentUserItem($current_user);
   }

   function setCurrentUserItem ($current_user) {
       $this->current_user = $current_user;
   }

   function getCurrentUserID () {
      $current_user = $this->getCurrentUserItem();
      return $current_user->getItemID();
   }

  /** get id of the current room
   * returns the current room id.
   *
   * @return integer        current_context_id
   *
   * @author CommSy Development Group
   */
   function getCurrentContextID() {
      return $this->current_context_id;
   }

  /** set id of the current room
   * sets the current room id.
   *
   * @var integer        id
   */
   function setCurrentContextID($id) {
      $this->current_context_id = $id;
   }

  /** set id of the current room
   * sets the current room as object.
   *
   * @var object value context item
   */
   public function setCurrentContextItem ($value) {
      $this->current_context = $value;
   }

  /** get the current room item
   * current context id must be set
   *
   * @return cs_context_item     room
   */
   function getCurrentContextItem() {
      if ( !is_null($this->current_context_id)
           and $this->current_context_id != 0
           and $this->current_context_id != $this->getServerID()
         ) {
         if (is_null($this->current_context) or $this->current_context->getItemID() != $this->current_context_id) {
            $item_manager = $this->getItemManager();
            $item = $item_manager->getItem($this->current_context_id);
            if ( isset($item) ) {
               $type = $item->getItemType();
               if ($type == CS_PROJECT_TYPE) {
                  $manager = $this->getRoomManager(); // room_manager for caching
               } elseif ($type == CS_COMMUNITY_TYPE) {
                  $manager = $this->getRoomManager(); // room_manager for caching
               } elseif ($type == CS_PRIVATEROOM_TYPE) {
                  $manager = $this->getRoomManager(); // room_manager for caching
               } elseif ($type == CS_GROUPROOM_TYPE) {
                  $manager = $this->getRoomManager(); // room_manager for caching
               } elseif ($type == CS_PORTAL_TYPE) {
                  $manager = $this->getPortalManager();
               } elseif ($type == CS_SERVER_TYPE) {
                  $manager = $this->getServerManager();
               } else {
                  include_once('functions/error_functions.php');
                  trigger_error('wrong type of room ['.$type.']',E_USER_ERROR);
               }
            } else {
               include_once('functions/error_functions.php');
               trigger_error(' can not initiate room -> bug in item table',E_USER_ERROR);
            }
            $this->current_context = $manager->getItem($this->current_context_id);
         }
      } else {
         $this->current_context_id = $this->getServerID();
         $this->current_context = $this->getServerItem();
      }
     return $this->current_context;
   }

  /** get server object
   * returns the server object.
   *
   * @return object server item
   */
   function getServerItem () {
      if (!isset($this->_server_item)) {
         $manager = $this->getServerManager();
         $this->_server_item = $manager->getItem($this->_server_id);
      }
      return $this->_server_item;
   }

   function getServerID () {
      return $this->_server_id;
   }

  /** get server object
   * returns the server object.
   *
   * @return object server item
   */
   function getCurrentPortalItem () {
      if (!isset($this->_current_portal)) {
         if ( empty($this->_current_portal_id) ) {
            $context_item = $this->getCurrentContextItem();
            if ( $context_item->isServer() ) {
               $this->_current_portal = NULL;
            } elseif ( $context_item->isPortal() ) {
               $this->_current_portal = $context_item;
            } else {
               $manager = $this->getPortalManager();
               $this->_current_portal = $manager->getItem($context_item->getContextID());
            }
         } else {
            $manager = $this->getPortalManager();
            $this->_current_portal = $manager->getItem($this->_current_portal_id);
         }
         if ( isset($this->_current_portal) ) {
            $this->_current_portal_id = $this->_current_portal->getItemID();
         }
      }
      return $this->_current_portal;
   }

   function getCurrentPortalID () {
      if ( empty($this->_current_portal_id) ) {
         $this->getCurrentPortalItem();
      }
      return $this->_current_portal_id;
   }

   function setCurrentPortalID ( $value ) {
      $this->_current_portal_id = (int)$value;
   }

  /** get name of the current module
   * returns the current module.
   *
   * @return string  current_module
   *
   * @author CommSy Development Group
   */
   function getCurrentModule() {
      return $this->current_module;
   }

  /** set name of the current module
   * set the current module.
   *
   * @var string        module
   *
   * @author CommSy Development Group
   */
   function setCurrentModule($module) {
      $this->current_module = $module;
   }

  /** get name of the current function
   * returns the current function.
   *
   * @return string        current_function
   *
   * @author CommSy Development Group
   */
   function getCurrentFunction() {
      return $this->current_function;
   }

  /** set name of the current function
   * set the current function.
   *
   * @var string        function
   *
   * @author CommSy Development Group
   */
   function setCurrentFunction($function) {
      $this->current_function = $function;
   }

  /** get string of the current parameter of the page
   * returns the current function.
   *
   * @return string        current parameter
   *
   * @author CommSy Development Group
   */
   function getCurrentParameterString () {
      if ( !isset($this->_current_parameter_string) ) {
         $array = $this->_getCurrentParameterArray();
         if (!empty($array)) {
            $this->_current_parameter_string = implode('&',$array);
         } else {
            $this->_current_parameter_string = '';
         }
      }
      return $this->_current_parameter_string;
   }

   function getCurrentParameterStringWithout ($value) {
      $retour = '';
      $array = $this->_getCurrentParameterArray();
      if (!empty($array)) {
         $result_array = array();
         foreach ($array as $parameter) {
            if ( !stristr($parameter,$value) ) {
               $result_array[] = $parameter;
            }
         }
         $retour = implode('&',$result_array);
      }
      return $retour;
   }

   function getValueOfParameter ($parameter) {
      $value = '';
      $array = $this->_getCurrentParameterArray();
      if (!empty($array)) {
         foreach ( $array as $current_parameter ) {
            if (stristr($current_parameter,$parameter.'=')) {
               $temp_array = explode('=',$current_parameter);
               if ( $temp_array[0] == $parameter ) {
                  $value = $temp_array[1];
               }
            }
         }
      }
      return $value;
   }

   function _getCurrentParameterArray () {
      global $_SERVER;

      if (!isset($this->_current_parameter_array)) {
         $this->_current_parameter_array = array();
         if (isset($_SERVER['QUERY_STRING'])) {
            $retour = explode('&',$_SERVER['QUERY_STRING']);

            // delete cid, mod and fct
            $go_on = true;
            while ($go_on and isset($retour[0])) {
               if (stristr($retour[0],'cid=') or stristr($retour[0],'mod=') or stristr($retour[0],'fct=') ) {
                  array_shift($retour);
               } else {
                  $go_on = false;
               }
            }
            // delete SID or empty array element
            if (count($retour) > 0) {
               $retour2 = array();
               foreach ($retour as $element) {
                  if (!stristr($element,'SID') and !empty($element)) {
                     $retour2[] = $element;
                  }
               }
               $retour = $retour2;
               unset($retour2);
            }
            $this->_current_parameter_array = $retour;
         }
      }
      return $this->_current_parameter_array;
   }

   function getCurrentParameterArrayWithout ($value) {
      $parameter_array = $this->_getCurrentParameterArray();
      $retour = array();
      if ( count($parameter_array) > 0 ) {
         foreach ($parameter_array as $parameter) {
            $temp_parameter_array = explode('=',$parameter);
            if ($value != $temp_parameter_array[0]) {
               $retour[$temp_parameter_array[0]] = $temp_parameter_array[1];
            }
         }
      }
      return $retour;
   }

   function getCurrentPostParameterArray () {
      global $_POST;
      $retour = $_POST;
      return $retour;
   }

   function getCurrentParameterArray () {
      $parameter_array = $this->_getCurrentParameterArray();
      $retour = array();
      if ( count($parameter_array) > 0 ) {
         foreach ($parameter_array as $parameter) {
            $temp_parameter_array = explode('=',$parameter);
            $retour[$temp_parameter_array[0]] = $temp_parameter_array[1];
         }
      }
      $translator = $this->getTranslationObject();
      if (isset($retour['search']) and $retour['search'] == $translator->getMessage('COMMON_SEARCH_IN_ROOM')){
         unset($retour['search']);
      }
      return $retour;
   }

   function setCurrentParameter ( $key, $value ) {
      $this->_current_parameter_array[] = $key.'='.$value;
   }

  /** get instance of cs_ads_log_manager
   *
   * @return cs_ads_log_manager
   * @access public
   */
   function getLogAdsManager() {
      return $this->_getInstance('cs_log_ads_manager');
   }

  /** get instance of cs_announcement_manager
   *
   * @return cs_announcement_manager
   * @access public
   */
   function getAnnouncementManager() {
      return $this->_getInstance('cs_announcement_manager');
   }

  /** get instance of cs_annotation_manager
   *
   * @return cs_annotation_manager
   * @access public
   * @author CommSy Development Group
   */
   function getAnnotationManager() {
      return $this->_getInstance('cs_annotations_manager');
   }

  /** get instance of cs_announcement_manager
   *
   * @return cs_announcement_manager
   * @access public
   */
   function getAuthSourceManager() {
      return $this->_getInstance('cs_auth_source_manager');
   }

  /** get instance of cs_disc_manager
   *
   * @return cs_disc_manager
   * @access public
   */
   function getDiscManager() {
     $name = 'cs_disc_manager';
      if (!isset($this->instance[$name])) {
         require_once('classes/'.$name.'.php');
         $this->instance[$name] = new $name($this->getCurrentPortalID(),$this->getCurrentContextID());
       if (!$this->inServer()) {
          $this->instance[$name]->setPortalID($this->getCurrentPortalID());
          $this->instance[$name]->setContextID($this->getCurrentContextID());
       } else {
          $this->instance[$name]->setServerID($this->getServerID());
       }
      }
      return $this->instance[$name];
   }

  /** get instance of cs_todo_manager
   *
   * @return cs_todo_manager
   * @access public
   */
   function getTodosManager() {
      return $this->_getInstance('cs_todos_manager');
   }

   function getTodoManager () {
      return $this->getTodosManager();
   }

   /** get instance of cs_dates_manager
   *
   * @return cs_dates_manager
   * @access public
   * @author CommSy Development Group
   */
   function getDateManager() {
      return $this->_getInstance('cs_dates_manager');
   }

   /** get instance of cs_dates_manager
   *
   * @return cs_dates_manager
   * @access public
   * @author CommSy Development Group
   */
   function getDatesManager() {
      return $this->_getInstance('cs_dates_manager');
   }

  /** get instance of cs_material_manager
   *
   * @return cs_material_manager
   * @access public
   * @author CommSy Development Group
   */
   function getMaterialManager() {
      return $this->_getInstance('cs_material_manager');
   }

  /** get instance of cs_ftsearch_manager
   *
   * @return cs_ftsearch_manager
   * @access public
   * @author CommSy Development Group
   */
   function getFTSearchManager() {
      return $this->_getInstance('cs_ftsearch_manager');
   }

  /** get instance of cs_section_manager
   *
   * @return cs_section_manager
   * @access public
   * @author CommSy Development Group
   */
   function getSectionManager() {
      return $this->_getInstance('cs_section_manager');
   }

   function getStepManager() {
      return $this->_getInstance('cs_step_manager');
   }

  /** get instance of cs_discussion_manager
   *
   * @return cs_discussion_manager
   * @access public
   * @author CommSy Development Group
   */
   function getDiscussionManager() {
      return $this->_getInstance('cs_discussion_manager');
   }

  /** get instance of cs_discussion_manager, DON't USE !!!
   * USE: getDiscussionArticleManager
   *
   * @return cs_discussion_manager
   * @access public
   * @author CommSy Development Group
   */
   function getDiscussionArticlesManager() {
      return $this->_getInstance('cs_discussionarticles_manager');
   }

  /** get instance of cs_discussion_manager
   *
   * @return cs_discussion_manager
   * @access public
   * @author CommSy Development Group
   */
   function getDiscussionArticleManager() {
      return $this->_getInstance('cs_discussionarticles_manager');
   }

  /** get instance of cs_links_manager
   *
   * @return cs_links_manager
   * @access public
   * @author CommSy Development Group
   */
   function getLinkManager() {
      return $this->_getInstance('cs_links_manager');
   }

  /** get instance of cs_links_manager
   *
   * @return cs_links_manager
   * @access public
   * @author CommSy Development Group
   */
   function getLinkItemManager() {
      return $this->_getInstance('cs_link_manager');
   }

  /** get instance of cs_user_manager
   *
   * @return cs_user_manager
   * @access public
   * @author CommSy Development Group
   */
   function getUserManager() {
      return $this->_getInstance('cs_user_manager');
   }

  /** get instance of cs_labels_manager
   *
   * @return cs_labels_manager
   * @access public
   * @author CommSy Development Group
   */
   function getLabelManager() {
      return $this->_getInstance('cs_labels_manager');
   }

  /** get instance of cs_topic_manager
   *
   * @return cs_topic_manager
   * @access public
   * @author CommSy Development Group
   */
   function getTopicManager() {
      if (!isset($this->instance['topic_manager'])) {
         $topic_manager = $this->_getInstance('cs_topic_manager');
         $topic_manager->resetLimits();
         $this->instance['topic_manager'] = $topic_manager;
      } else {
         #$this->instance['topic_manager']->resetLimits();
      }
      return $this->instance['topic_manager'];
   }

   function getGroupManager() {
      if (!isset($this->instance['group_manager'])) {
         $group_manager = $this->_getInstance('cs_group_manager');
         $group_manager->resetLimits();
         $this->instance['group_manager'] = $group_manager;
      } else {
         #$this->instance['group_manager']->resetLimits();
      }
      return $this->instance['group_manager'];
   }

  /** get instance of cs_institution_manager
   *
   * @return cs_institution_manager
   * @access public
   */
   function getInstitutionManager() {
      if (!isset($this->instance['institution_manager'])) {
         $institution_manager = $this->_getInstance('cs_institution_manager');
         $institution_manager->resetLimits();
         $this->instance['institution_manager'] = $institution_manager;
      } else {
         #$this->instance['institution_manager']->resetLimits();
      }
      return $this->instance['institution_manager'];
   }

   /** get instance of cs_link_modifier_item_manager
   *
   * @return cs_link_modifier_item_manager
   * @access public
   */
   function getLinkModifierItemManager() {
      return $this->_getInstance('cs_link_modifier_item_manager');
   }

  /** get instance of cs_link_item_file_manager
   *
   * @return cs_link_item_file_manager
   * @access public
   */
   function getLinkItemFileManager() {
      return $this->_getInstance('cs_link_item_file_manager');
   }

  /** get instance of cs_community_manager
   *
   * @return cs_Community_manager
   * @access public
   */
   function getCommunityManager () {
      return $this->_getInstance('cs_community_manager');
   }

  /** get instance of cs_privateroom_manager
   *
   * @return cs_PrivateRoom_manager
   * @access public
   */
   function getPrivateRoomManager () {
      return $this->_getInstance('cs_privateroom_manager');
   }

  /** get instance of cs_grouproom_manager
   *
   * @return cs_grouproom_manager
   * @access public
   */
   function getGroupRoomManager () {
      return $this->_getInstance('cs_grouproom_manager');
   }

  /** get instance of cs_privateroom_manager
   *
   * @return cs_PrivateRoom_manager
   * @access public
   */
   function getMyRoomManager () {
      return $this->_getInstance('cs_myroom_manager');
   }

  /** get instance of cs_log_manager
   *
   * @return cs_log_manager
   * @access public
   */
   function getLogManager() {
      return $this->_getInstance('cs_log_manager');
   }

  /** get instance of cs_log_manager
   *
   * @return cs_log_manager
   * @access public
   */
   function getLogArchiveManager() {
      return $this->_getInstance('cs_log_archive_manager');
   }

  /** get instance of cs_log_error_manager
   *
   * @return cs_log_error_manager
   * @access public
   */
   function getLogErrorManager() {
      return $this->_getInstance('cs_log_error_manager');
   }

   /** get instance of cs_project_manager
   *
   * @return cs_project_manager
   * @access public
   * @author CommSy Development Group
   */
   function getProjectManager () {
      return $this->_getInstance('cs_project_manager');
   }

  /** get instance of cs_time_manager
   *
   * @return cs_time_manager
   * @access public
   */
   function getTimeManager() {
      return $this->_getInstance('cs_time_manager');
   }

  /** get instance of cs_buzzword_manager
   *
   * @return cs_buzzword_manager
   * @access public
   */
   function getBuzzwordManager() {
      return $this->_getInstance('cs_buzzword_manager');
   }

  /** get instance of cs_file_manager
   *
   * @return cs_file_manager
   * @access public
   * @author CommSy Development Group
   */
   function getFileManager() {
      return $this->_getInstance('cs_file_manager');
   }

  /** get instance of cs_reader_manager
   *
   * @return cs_reader_manager
   * @access public
   * @author CommSy Development Group
   */
   function getReaderManager() {
      return $this->_getInstance('cs_reader_manager');
   }

  /** get instance of cs_noticed_manager
   *
   * @return cs_noticed_manager
   * @access public
   * @author CommSy Development Group
   */
   function getNoticedManager() {
      return $this->_getInstance('cs_noticed_manager');
   }

  /** get instance of cs_room_manager
   *
   * @return cs_room_manager
   * @access public
   */
   function getRoomManager() {
      return $this->_getInstance('cs_room_manager');
   }

  /** get instance of cs_task_manager
   *
   * @return cs_task_manager
   * @access public
   * @author CommSy Development Group
   */
   function getTaskManager() {
      return $this->_getInstance('cs_tasks_manager');
   }

  /** get instance of cs_tag_manager
   *
   * @return cs_tag_manager
   * @access public
   */
   public function getTagManager () {
      return $this->_getInstance('cs_tag_manager');
   }

  /** get instance of cs_tag2tag_manager
   *
   * @return cs_tag2tag_manager
   * @access public
   */
   public function getTag2TagManager () {
      return $this->_getInstance('cs_tag2tag_manager');
   }

  /** get instance of cs_item_manager
   *
   * @return cs_item_manager
   * @access public
   */
   public function getItemManager() {
      return $this->_getInstance('cs_item_manager');
   }

  /** get instance of cs_server_manager
   *
   * @return cs_server_manager
   * @access public
   */
   function getServerManager() {
      return $this->_getInstance('cs_server_manager');
   }

  /** get instance of cs_portal_manager
   *
   * @return cs_portal_manager
   * @access public
   */
   function getPortalManager() {
      return $this->_getInstance('cs_portal_manager');
   }

  /** get instance of cs_hash_manager
   *
   * @return cs_hash_manager
   * @access public
   */
   function getHashManager() {
      return $this->_getInstance('cs_hash_manager');
   }

  /** get instance of cs_homepage_manager
   *
   * @return cs_homepage_manager
   * @access public
   */
   function getHomepageManager() {
      return $this->_getInstance('cs_homepage_manager');
   }

  /** get instance of cs_homepage_manager
   *
   * @return cs_homepage_manager
   * @access public
   */
   function getETChatManager () {
      return $this->_getInstance('cs_etchat_manager');
   }

  /** get instance of cs_wiki_manager
   *
   * @return cs_wiki_manager
   * @access public
   */
   function getWikiManager() {
      return $this->_getInstance('cs_wiki_manager');
   }

  /** get instance of cs_homepagelink_manager
   *
   * @return cs_homepagelink_manager
   * @access public
   */
   function getHomepageLinkManager() {
      return $this->_getInstance('cs_homepagelink_manager');
   }

   function getExternalIdManager() {
      return $this->_getInstance('cs_external_id_manager');
   }

   /** get instance of cs_file_multi_upload_manager
   *
   * @return cs_file_multi_upload_manager
   * @access public
   */
   function getFileMultiUploadManager() {
      return $this->_getInstance('cs_file_multi_upload_manager');
   }

  /** get instance of cs_session_manager
   *
   * @return cs_session_manager
   * @access public
   */
   function getSessionManager() {
      global $c_auth; // unsch�n (TBD)
      if (!isset($this->instance['cs_session_manager'])) {
         require_once('classes/cs_session_manager.php');
         $this->instance['cs_session_manager'] = new cs_session_manager($this->getDBConnector(),$c_auth);
      }
      return $this->instance['cs_session_manager'];
   }

   function getSession () {
      global $session; // (TBD) !!!!!!!
      return $session;
   }

   function getSessionItem () {
      $retour = NULL;
      if ( isset($this->_session_item) and !empty($this->_session_item) ) {
         $retour = $this->_session_item;
      } elseif ( isset($this->_session_id) and !empty($this->_session_id) ) {
         $session_manager = $this->getSessionManager();
         $session_item = $session_manager->get($this->_session_id);
         if ( isset($session_item) and !empty($session_item) ) {
            $retour = $session_item;
            $this->_session_item = $session_item;
         }
      } else {
         global $session; // (TBD) !!!!!!!
         $retour = $session;
      }
      return $retour;
   }

   function setSessionItem ($value) {
      $this->_session_item = $value;
   }

   function setSessionID ($value) {
      $this->_session_id = $value;
   }

  /** get instance of a class, INTERNAL
   * returns a single instance of a class. a reference to the returned object must
   * be assigned, otherwise a copy is created.<br />
   * Example:<br />
   * $news_manager =<b>&</b> $enviroment->_getInstance('cs_news_manager');
   *
   * @param string           name      name of the class to be instantiated
   * @return cs_manager
   * @access private
   */
   function _getInstance($name) {
      if ( !isset($this->instance[$name]) ) {
         require_once('classes/'.$name.'.php');
         $this->instance[$name] = new $name($this);
      }
      $this->instance[$name]->resetLimits();
      if ( !$this->_cache_on ) {
         $this->instance[$name]->resetData();
         $this->instance[$name]->setCacheOff();
      }
      return $this->instance[$name];
   }

   /** get Instance of the authentication object
    * returns an object for authentication users in commsy
    *
    * @return cs_object authentication object for commsy
    */
   function getAuthenticationObject () {
      if (!isset($this->instance['auth_object'])) {
         $portal = $this->getCurrentPortalItem();
         if (!empty($portal) and $portal->isPortal()) {
            $auth_source_list = $portal->getAuthSourceList();
         } else {
            $server = $this->getServerItem();
            $auth_source_list = $server->getAuthSourceList();
         }
         include_once('classes/cs_authentication.php');
         $this->instance['auth_object'] = new cs_authentication($this);
         $this->instance['auth_object']->setAuthSourceList($auth_source_list);
      }
      return $this->instance['auth_object'];
   }

  /** get instance of cs_XXX_manager by item_type
   *
   * @param string type of an item
   *
   * @return cs_XXX_manager
   * @access public
   */
   function getManager ($type) {
      if (!empty($type)) {
         if ($type == CS_DATE_TYPE) {
            return $this->getDateManager();
         } elseif ($type == CS_TODO_TYPE or $type == 'todos') {
            return $this->getTodosManager();
         } elseif ($type == 'contact' or $type == 'contacts') {
            return $this->getUserManager();
         } elseif ($type == CS_USER_TYPE or $type == 'users') {
            return $this->getUserManager();
         } elseif ($type == CS_MATERIAL_TYPE or $type == 'materials') {
            return $this->getMaterialManager();
         } elseif ($type == CS_ANNOTATION_TYPE or $type == 'annotations') {
            return $this->getAnnotationManager();
         } elseif ($type == 'discussion' or $type == 'discussions') {
            return $this->getDiscussionManager();
         } elseif ($type == 'discarticle' or $type == 'discarticles') {
            return $this->getDiscussionArticlesManager();
         } elseif ($type == 'announcements' or $type == CS_ANNOUNCEMENT_TYPE) {
            return $this->getAnnouncementManager();
         } elseif ($type == 'institution' or $type == 'institutions') {
            return $this->getInstitutionManager();
         } elseif ($type == CS_TOPIC_TYPE) {
            return $this->getTopicManager();
         } elseif ($type == 'group' or $type == 'groups') {
            return $this->getGroupManager();
         } elseif ($type == 'task' or $type == 'tasks') {
            return $this->getTaskManager();
         } elseif ($type == 'section') {
            return $this->getSectionManager();
         } elseif ($type == 'label') {
            return $this->getLabelManager();
         } elseif ($type == 'log') {
            return $this->getLogManager();
         } elseif ($type == 'log_archive') {
            return $this->getLogArchiveManager();
         } elseif ($type == CS_PROJECT_TYPE) {
            return $this->getProjectManager();
         } elseif ($type == CS_ROOM_TYPE) {
            return $this->getRoomManager();
         } elseif ($type == CS_COMMUNITY_TYPE) {
            return $this->getCommunityManager();
         } elseif ($type == CS_PRIVATEROOM_TYPE) {
            return $this->getPrivateRoomManager();
         } elseif ($type == CS_GROUPROOM_TYPE) {
            return $this->getGroupRoomManager();
         } elseif ($type == CS_MYROOM_TYPE) {
            return $this->getMyRoomManager();
         } elseif ($type == CS_PORTAL_TYPE) {
            return $this->getPortalManager();
         } elseif ($type == CS_SERVER_TYPE) {
            return $this->getServerManager();
         } elseif ($type == CS_FILE_TYPE) {
            return $this->getFileManager();
         } elseif ($type == CS_LINK_TYPE) {
            return $this->getLinkManager();
         } elseif ($type == CS_LINKITEM_TYPE) {
            return $this->getLinkItemManager();
         } elseif ($type == CS_LINKMODITEM_TYPE) {
            return $this->getLinkModifierItemManager();
         } elseif ($type == CS_LINKITEMFILE_TYPE) {
            return $this->getLinkItemFileManager();
         } elseif ($type == CS_ITEM_TYPE or $type == 'items') {
            return $this->getItemManager();
         } elseif ($type == CS_READER_TYPE) {
            return $this->getReaderManager();
         } elseif ($type == CS_NOTICED_TYPE) {
            return $this->getNoticedManager();
         } elseif ($type == CS_TIME_TYPE) {
            return $this->getTimeManager();
         } elseif ($type == CS_HOMEPAGE_TYPE) {
            return $this->getHomepageManager();
         } elseif ($type == CS_WIKI_TYPE) {
            return $this->getWikiManager();
         } elseif ($type == CS_LINKHOMEPAGEHOMEPAGE_TYPE) {
            return $this->getHomepageLinkManager();
         } elseif ($type == CS_AUTH_SOURCE_TYPE) {
            return $this->getAuthSourceManager();
         } elseif ($type == 'chat') {
            return '';
         } elseif ($type == CS_TAG_TYPE) {
            return $this->getTagManager();
         } elseif ($type == CS_TAG2TAG_TYPE) {
            return $this->getTag2TagManager();
         } else {
            include_once('functions/error_functions.php');
            trigger_error('do not know this type ['.$type.']',E_USER_ERROR);
         }
      }
      return NULL;
   }

  /** get boolean, if your are in the community room or not
   *
   * @return boolean, true  = you are in the community room
   *                  false = you are not in the community room
   */
   function inCommunityRoom () {
      $context_item = $this->getCurrentContextItem();
      return $context_item->isCommunityroom();
   }

  /** get boolean, if your are in the private room or not
   *
   * @return boolean, true  = you are in the private room
   *                  false = you are not in the private room
   */
   function inPrivateRoom () {
      $context_item = $this->getCurrentContextItem();
      return $context_item->isPrivateroom();
   }

   function isContextOpenForGuests() {
      $context_item = $this->getCurrentContextItem();
      return $context_item->isOpenForGuests();
   }

  /** get boolean, if your are in a group room or not
   *
   * @return boolean, true  = you are in a group room
   *                  false = you are not in a group room
   */
   function inGroupRoom () {
      $context_item = $this->getCurrentContextItem();
      return $context_item->isGroupRoom();
   }

  /** get boolean, if your are in a project room or not
   *
   * @return boolean, true  = you are in a project room
   *                  false = you are not in a project room
   */
   function inProjectRoom () {
      $context_item = $this->getCurrentContextItem();
      return $context_item->isProjectRoom();
   }

  /** get boolean, if your are in a portal or not
   *
   * @return boolean, true  = you are in a portal
   *                  false = you are not in a portal
   */
   function inPortal () {
      $context_item = $this->getCurrentContextItem();
      return $context_item->isPortal();
   }

  /** get boolean, if your are in a server or not
   *
   * @return boolean, true  = you are in a server
   *                  false = you are not in a server
   */
   function inServer () {
      $context_item = $this->getCurrentContextItem();
      return $context_item->isServer();
   }

   /** get Instance of the translation object
    * returns an object for translation of message tags
    *
    * @return cs_object translation object for commsy
    */
   function getTranslationObject () {
      global $c_messagetag_log_db;
      global $dont_resolve_messagetags;

      if ( !isset($this->instance['translation_object']) ) {
         include_once('classes/cs_translator.php');
         $this->instance['translation_object'] = new cs_translator;
         if ($dont_resolve_messagetags) {
            $this->instance['translation_object']->dontResolveMessageTags();
         }
         if ($c_messagetag_log_db) {
            $this->instance['translation_object']->logMessageTags();
            $this->instance['translation_object']->setDBConnector($this->getDBConnector());
            $this->instance['translation_object']->setCommSyVersion($this->getCurrentCommSyVersion());
         }
         $this->instance['translation_object']->setSelectedLanguage($this->getSelectedLanguage());
         $context_item = $this->getCurrentContextItem();
         if ( $this->inCommunityRoom() ) {
            $this->instance['translation_object']->setContext('community');
            $portal_item = $context_item->getContextItem();
            $this->instance['translation_object']->setTimeMessageArray($portal_item->getTimeTextArray());
         } elseif ( $this->inProjectRoom() ) {
            $this->instance['translation_object']->setContext('project');
            $portal_item = $context_item->getContextItem();
            $this->instance['translation_object']->setTimeMessageArray($portal_item->getTimeTextArray());
         } elseif ( $this->inGroupRoom() ) {
            $this->instance['translation_object']->setContext(CS_GROUPROOM_TYPE);
            $portal_item = $context_item->getContextItem();
            $this->instance['translation_object']->setTimeMessageArray($portal_item->getTimeTextArray());
         } elseif ( $this->inPrivateRoom() ) {
            $this->instance['translation_object']->setContext('private');
            $portal_item = $context_item->getContextItem();
            $this->instance['translation_object']->setTimeMessageArray($portal_item->getTimeTextArray());
         } elseif ( $this->inPortal() ) {
            $this->instance['translation_object']->setContext('portal');
            $this->instance['translation_object']->setTimeMessageArray($context_item->getTimeTextArray());
         } else {
            $this->instance['translation_object']->setContext('server');
         }
         if ( isset($context_item) ) {
            $this->instance['translation_object']->setRubricTranslationArray($context_item->getRubricTranslationArray());
            $this->instance['translation_object']->setEmailTextArray($context_item->getEmailTextArray());
         }
      }

      // we need sometimes the language, even if the user is unknown at that time
      // so we must change the language, when we know the user and selected language has changed
      else {
         $language_now = $this->getSelectedLanguage();
         $language_stored = $this->instance['translation_object']->getSelectedLanguage();
         if ($language_now != $language_stored) {
            $this->instance['translation_object']->setSelectedLanguage($language_now);
         }
      } // end of if statement

      $session_item = $this->getSessionItem();
      if ( isset($session_item) and $session_item->issetValue('message_language_select') ) {
         $session_language = $session_item->getValue('message_language_select');
         if ( !empty($session_language) ) {
            $this->instance['translation_object']->setSessionLanguage($session_language);
         }
      }

      return $this->instance['translation_object'];
   }

   /** getSelectedLanguage
    * get selected language, form user, room or browser
    *
    * @return string selected language
    */
   function getSelectedLanguage () {
      if ( empty($this->_selected_language) ) {
         $context_item = $this->getCurrentContextItem();
         if ( isset($context_item) ) {
            $this->_selected_language = $context_item->getLanguage();
         }
         if ($this->_selected_language == 'user') {
            $this->_selected_language = $this->getUserLanguage();
         }
      }
      return $this->_selected_language;
   }

   public function unsetSelectedLanguage () {
      $this->_selected_language = NULL;
   }

   function getUserLanguage () {
      $current_user = $this->getCurrentUserItem();
      if ( $current_user->isUser() ) {
         $retour = $current_user->getLanguage();
         if ($retour == 'browser') {
            $retour = $this->getBrowserLanguage();
         }
      } else {
         $retour = $this->getBrowserLanguage();
      }
      return $retour;
   }

   function getBrowserLanguage () {
      $browser_languages = $this->parseAcceptLanguage();
      $available_languages = $this->getAvailableLanguageArray();
      // there is no central default language yet, so this needs to be hardcoded
      $language = 'de'; //default language
      foreach ($browser_languages as $lang) {
         if ($lang == 'ro'){
            $lang = 'ru';
         }
   if (in_array($lang, $available_languages)) {
      $language = $lang;
      break;
   }
      }
      return $language;
   }

   function getAvailableLanguageArray () {
      if ( !isset($this->_available_languages) ) {
         if ( $this->inServer() ) {
            $context_item = $this->getServerItem();
         } else {
            $context_item = $this->getCurrentPortalItem();
         }
         $this->_available_languages = $context_item->getAvailableLanguageArray();
      }
      return $this->_available_languages;
   }

  /**
   * Taken from http://www.shredzone.de/articles/php/snippets/acceptlang/?SID=uf4h8rf736v35afbi90844qsc0
   *
   * Parse the Accept-Language HTTP header sent by the browser. It
   * will return an array with the languages the user accepts, sorted
   * from most preferred to least preferred.
   *
   *
   * @return  Array: key is the importance, value is the language code.
   */
  function parseAcceptLanguage() {
    $ayLang = array();
    $aySeen = array();
    if(getenv('HTTP_ACCEPT_LANGUAGE') != '') {
      foreach(explode(',',getenv('HTTP_ACCEPT_LANGUAGE')) as $llang) {
        preg_match("#^(.*?)([-_].*?)?(\;q\=(.*))?$#i", $llang, $ayM);
        $q = isset($ayM[4]) ? $ayM[4] : '1.0';
        $lang = strtolower(trim($ayM[1]));
        if(!in_array($lang, $aySeen)) {
          $ayLang[$q] = $lang;
          $aySeen[] = $lang;
        }
      }
      uksort($ayLang, create_function('$a,$b','return ($a>$b) ? -1 : 1;'));
    }
    return $ayLang;
  }

   function getCurrentBrowser () {
      $retour = '';
      if ( !isset($this->_browser) ) {
         $this->_parseBrowser();
      }
      if ( !empty($this->_browser) ) {
         $retour = $this->_browser;
      }
      return $retour;
   }

   function getCurrentBrowserVersion () {
      $retour = '';
      if ( !isset($this->_browser_version) ) {
         $this->_parseBrowser();
      }
      if ( !empty($this->_browser_version) ) {
         $retour = $this->_browser_version;
      }
      return $retour;
   }

   function _parseBrowser() {
      global $_SERVER;

      $browser = array ( //reversed array
         "OPERA",
         "MSIE",            // parent
         "NETSCAPE",
         "FIREFOX",
         "SAFARI",
         "KONQUEROR",
         "CAMINO",
         "MOZILLA"        // parent
      );

      $this->_browser = 'OTHER';
      $this->_browser_version = '';

      foreach ($browser as $parent) {
         if ( ($s = strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), $parent)) !== FALSE ) {
            $f = $s + strlen($parent);
            $version = substr($_SERVER['HTTP_USER_AGENT'], $f, 5);
            $version = preg_replace('/[^0-9,.]/','',$version);

            $this->_browser = $parent;
            $this->_browser_version = $version;
            break; // first match wins
         }
      }
   }

   function getCurrentOperatingSystem () {
      global $_SERVER;
      $HTTP_USER_AGENT = $_SERVER['HTTP_USER_AGENT'];
      $os = 'UNKNOWN';
      if ( $os == 'UNKNOWN' and (strpos($HTTP_USER_AGENT, "Win95") || strpos($HTTP_USER_AGENT, "Windows 95")) ) {
         $os = "Windows 95";
      }
      if ( $os == 'UNKNOWN' and (strpos($HTTP_USER_AGENT, "Win98") || strpos($HTTP_USER_AGENT, "Windows 98")) ) {
         $os = "Windows 98";
      }
      if ( $os == 'UNKNOWN' and (strpos($HTTP_USER_AGENT, "WinNT") || strpos($HTTP_USER_AGENT, "Windows NT")) ) {
         $os = "Windows NT";
      }
      if ( $os == 'Windows NT' and (strpos($HTTP_USER_AGENT, "WinNT 5.0") || strpos($HTTP_USER_AGENT, "Windows NT 5.0")) ) {
         $os = "Windows 2000";
      }
      if ( $os == 'Windows NT' and (strpos($HTTP_USER_AGENT, "WinNT 5.1") || strpos($HTTP_USER_AGENT, "Windows NT 5.1")) ) {
         $os = "Windows XP";
      }
      if ( $os == 'UNKNOWN' and (strpos($HTTP_USER_AGENT, "Linux")) ) {
         $os = "Linux";
      }
      if ( $os == 'UNKNOWN' and (strpos($HTTP_USER_AGENT, "OS/2")) ) {
         $os = "OS/2";
      }
      if ( $os == 'UNKNOWN' and (strpos($HTTP_USER_AGENT, "Sun")) ) {
         $os = "Sun OS";
      }
      if ( $os == 'UNKNOWN' and (strpos($HTTP_USER_AGENT, "Macintosh") || strpos($HTTP_USER_AGENT, "Mac_PowerPC")) ) {
         $os = "Mac OS";
      }
      return $os;
   }

   function getRootUserItem () {
      $user_manager = $this->getUserManager();
      return $user_manager->getRootUser();
   }

   function getRootUserItemID () {
      $retour = NULL;
      $root_user = $this->getRootUserItem();
      if ( isset($root_user) ) {
         $item_id = $root_user->getItemID();
         if ( !empty($item_id) ) {
            $retour = $item_id;
         }
         unset($root_user);
      }
      return $retour;
   }

   function getPluginClass ($plugin) {
      $retour = NULL;
      if (empty($this->_plugin_class_array[$plugin])) {
         $plugin_class_name = 'class_'.$plugin;
         include_once('plugins/'.$plugin.'/'.$plugin_class_name.'.php');
         $this->_plugin_class_array[$plugin] = new $plugin_class_name($this);
      }
      $retour = $this->_plugin_class_array[$plugin];
      return $retour;
   }

   function getDBConnector () {
      return $this->_getMySQLConnector();
   }

   function _getMySQLConnector () {
      if ( empty($this->_db_mysql_connector) ) {
         global $db;
         include_once('classes/db_mysql_connector.php');
         $this->_db_mysql_connector = new db_mysql_connector($db['normal']);
         global $c_show_debug_infos;
         if ( isset($c_show_debug_infos) and $c_show_debug_infos ) {
            $this->_db_mysql_connector->setLogQueries();
         }
      }
      return $this->_db_mysql_connector;
   }

   public function getCurrentCommSyVersion () {
      $server_item = $this->getServerItem();
      return $server_item->getCurrentCommSyVersion();
   }

   public function isCurlForPHPAvailable(){
      return function_exists("curl_init");
//      return false; //-> Scribd not available in release 6.1.2
   }


   public function isScribdAvailable(){
        if(!$this->isCurlForPHPAvailable()){
            return false;
        }
        $scribd_api_key = $this->getServerItem()->getScribdApiKey();
        $scribd_secret = $this->getServerItem()->getScribdSecret();
        if ($scribd_api_key == ''){
            return false;
        }
        if ($scribd_secret == ''){
            return false;
        }
        return true;
   }

   public function withBelugaConnection(){
      global $url_for_beluga_system;
      $retour = false;
      if (isset($url_for_beluga_system) and !empty($url_for_beluga_system)){
         $retour = true;
      }
      return $retour;
   }

   public function getBelugaConnectionLink(){
      global $url_for_beluga_system;
      $commsy_link = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
      if (!stristr($commsy_link,'SID')) {
         $session = $this->getSessionItem();
         $commsy_link .='&CSID='.$session->getSessionID();
      }else{
         $commsy_link = str_replace('SID','CSID',$commsy_link);
      }
      $link = $url_for_beluga_system;
      $link .='?commsy_url='.urldecode($commsy_link);
      return $link;
/* Neue Funktion ab Beluga 0.7
      global $url_for_beluga_system;
      $commsy_link = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
      if (!stristr($commsy_link,'SID')) {
         $session = $this->getSessionItem();
         $commsy_link .='&SID='.$session->getSessionID();
      }
      $commsy_link .= '&import_type=url';
      $link = $url_for_beluga_system;
      $link .='?export_system=commsy';
      $link .= '&export_url='.urlencode($commsy_link);
      return $link;
*/
   }

   public function setCacheOff () {
      $this->_cache_on = false;
   }

   public function getClassFactory () {
      global $class_factory;
      return $class_factory;
   }

   public function setOutputMode ( $value ) {
      $this->_output_mode = $value;
   }

   public function getOutputMode ( ) {
      return $this->_output_mode;
   }

   public function isOutputMode ( $value ) {
      $retour = false;
      $mode = $this->getOutputMode();
      if ( !empty($mode)
           and strtolower($mode) == strtolower($value)
         ) {
         $retour = true;
      }
      return $retour;
   }

   public function isOutputModeNot ( $value ) {
      return !$this->isOutputMode($value);
   }
}
?>
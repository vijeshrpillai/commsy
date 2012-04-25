<?php
class cs_popup_breadcrumb_controller {
	private $_environment = null;
	private $_translator = null;
	private $_popup_controller = null;
	private $_return = '';

	/**
	* constructor
	*/
	public function __construct(cs_environment $environment, cs_ajax_popup_controller $popup_controller) {
		$this->_environment = $environment;
		$this->_popup_controller = $popup_controller;
	}

	public function edit($item_id) {

	}

	public function create($form_data) {

	}

	public function getHTML() {
	
	}
	
	public function getReturn() {
		return $this->_return;
	}

	public function getFieldInformation() {
		return array(
			array(	'name'		=> 'title',
					'type'		=> 'text',
					'mandatory' => true),
			array(	'name'		=> 'description',
					'type'		=> 'text',
					'mandatory'	=> false)
		);
	}

	private function getBreadcrumbInformation() {
		$return = array();

		$current_user = $this->_environment->getCurrentUserItem();
		$portal_item = $this->_environment->getCurrentPortalItem();
		$current_context = $this->_environment->getCurrentContextItem();

		// server
		if($current_user->isRoot()) {
			$server_item = $this->_environment->getServerItem();
			$return[] = array(
					'id'	=> $server_item->getItemID(),
					'title'	=> $server_item->getTitle()
			);
		}

		// portal
		$return[] = array(
				'id'	=> $portal_item->getItemID(),
				'title'	=> $portal_item->getTitle()
		);

		// community
		if($this->_environment->inProjectRoom()) {
			$community_list = $current_context->getCommunityList();
			$community_item = $community_list->getFirst();
			if(!empty($community_item)) {
				$return[] = array(
						'id'	=> $community_item->getItemID(),
						'title'	=> $community_item->getTitle()
				);
			}

			// group groom
		} elseif($this->_environment->inGroupRoom()) {
			$project_item = $current_context->getLinkedProjectItem();
			$community_list = $project_item->getCommunityList();
			$community_item = $community_list->getFirst();
			if(!empty($community_item)) {
				$return[] = array(
						'id'	=> $community_item->getItemID(),
						'title'	=> $community_item->getTitle()
				);
			}

			// project
			$return[] = array(
					'id'	=> $project_item->getItemID(),
					'title'	=> $project_item->getTitle()
			);
		}

		// room
		$return[] = array(
				'id'	=> $current_context->getItemID(),
				'title'	=> $current_context->getTitle()
		);

		return $return;
	}

	public function assignTemplateVars() {
		$translator = $this->_environment->getTranslationObject();

		// breadcrumb information
		$breadcrumb_information = array();
		$this->_popup_controller->assign('popup', 'breadcrumb', $this->getBreadcrumbInformation());
		$this->_popup_controller->assign('popup', 'rooms', $this->getRoomListArray());
	}

	private function cleanup_session($current_iid) {
		$environment = $this->_environment;
		$session = $this->_environment->getSessionItem();

		$session->unsetValue($environment->getCurrentModule().'_add_buzzwords');
		$session->unsetValue($environment->getCurrentModule().'_add_tags');
		$session->unsetValue($environment->getCurrentModule().'_add_files');
		$session->unsetValue($current_iid.'_post_vars');
	}

   function _getCustomizedRoomListForCurrentUser(){
      $retour = array();
      $current_user = $this->_environment->getCurrentUserItem();
      $current_context_id = $this->_environment->getCurrentContextID();
      $own_room_item = $current_user->getOwnRoom();
      $temp_array = array();
      $temp_array['title'] = '----------------------------';
      $temp_array['item_id'] = '-1';
      $retour[] = $temp_array;
      $customized_room_list = $own_room_item->getCustomizedRoomList();
      if ( isset($customized_room_list) ) {
         $room_item = $customized_room_list->getFirst();
         while ($room_item) {
            $temp_array = array();
            if ( $room_item->isGrouproom() ) {
               $temp_array['title'] = '- '.$room_item->getTitle();
            } else {
               $temp_array['title'] = $room_item->getTitle();
            }
            if ( mb_strlen($temp_array['title']) > 28 ) {
               $temp_array['title'] = mb_substr($temp_array['title'],0,28);
               $temp_array['title'] .= '...';
            }
            $temp_array['item_id'] = $room_item->getItemID();
            if ($current_context_id == $temp_array['item_id']){
               $temp_array['selected'] = true;
            }
            $retour[] = $temp_array;
            $room_item = $customized_room_list->getNext();
         }
      }
      return $retour;
   }


   function _getAllOpenContextsForCurrentUser () {
	  $this->_translator = $this->_environment->getTranslationObject();
      $current_user = $this->_environment->getCurrentUserItem();
      $own_room_item = $current_user->getOwnRoom();
      if ( isset($own_room_item) ) {
         $customized_room_array = $own_room_item->getCustomizedRoomIDArray();
      }
      if (isset($customized_room_array[0])){
         return $this->_getCustomizedRoomListForCurrentUser();
      }else{
      $this->translatorChangeToPortal();
      $selected = false;
      $selected_future = 0;
      $selected_future_pos = -1;
      $retour = array();
      $temp_array = array();
      $temp_array['item_id'] = -1;
      $temp_array['title'] = '';
      $retour[] = $temp_array;
      unset($temp_array);
      $temp_array = array();
      $community_list = $current_user->getRelatedCommunityList();
      if ( $community_list->isNotEmpty() ) {
         $temp_array['item_id'] = -1;
         $temp_array['title'] = $this->_translator->getMessage('MYAREA_COMMUNITY_INDEX').'';
         $retour[] = $temp_array;
         unset($temp_array);
         $community_item = $community_list->getFirst();
         while ($community_item) {
            $temp_array = array();
            $temp_array['item_id'] = $community_item->getItemID();
            $title = $community_item->getTitle();
            $temp_array['title'] = $title;
            if ( $community_item->getItemID() == $this->_environment->getCurrentContextID()
                 and !$selected
               ) {
               $temp_array['selected'] = true;
               $selected = true;
            }

            $retour[] = $temp_array;
            unset($temp_array);
            unset($community_item);
            $community_item = $community_list->getNext();
         }
         $temp_array = array();
         $temp_array['item_id'] = -1;
         $temp_array['title'] = '';
         $retour[] = $temp_array;
         unset($community_list);
      }
      $portal_item = $this->_environment->getCurrentPortalItem();
      if ($portal_item->showTime()) {
         $project_list = $current_user->getRelatedProjectListSortByTimeForMyArea();
#         if ( $portal_item->showGrouproomConfig() ) {
            include_once('classes/cs_list.php');
            $new_project_list = new cs_list();
            $grouproom_array = array();
            $project_grouproom_array = array();
            if ( $project_list->isNotEmpty() ) {
               $room_item = $project_list->getFirst();
               while ($room_item) {
                  if ( $room_item->isA(CS_GROUPROOM_TYPE) ) {
                     $grouproom_array[$room_item->getItemID()] = $room_item->getTitle();
                     $linked_project_item_id = $room_item->getLinkedProjectItemID();
                     $project_grouproom_array[$linked_project_item_id][] = $room_item->getItemID();
                  } else {
                     $new_project_list->add($room_item);
                  }
                  unset($room_item);
                  $room_item = $project_list->getNext();
               }
               unset($project_list);
               $project_list = $new_project_list;
               unset($new_project_list);
            }
#         }
         $future = true;
         $future_array = array();
         $no_time = false;
         $no_time_array = array();
         $current_time = $portal_item->getTitleOfCurrentTime();
         $with_title = false;
      } else {
         $project_list = $current_user->getRelatedProjectListForMyArea();
#         if ( $portal_item->showGrouproomConfig() ) {
            include_once('classes/cs_list.php');
            $new_project_list = new cs_list();
            $grouproom_array = array();
            $project_grouproom_array = array();
            if ( $project_list->isNotEmpty() ) {
               $room_item = $project_list->getFirst();
               while ($room_item) {
                  if ( $room_item->isA(CS_GROUPROOM_TYPE) ) {
                     $grouproom_array[$room_item->getItemID()] = $room_item->getTitle();
                     $linked_project_item_id = $room_item->getLinkedProjectItemID();
                     $project_grouproom_array[$linked_project_item_id][] = $room_item->getItemID();
                  } else {
                     $new_project_list->add($room_item);
                  }
                  unset($room_item);
                  $room_item = $project_list->getNext();
               }
               unset($project_list);
               $project_list = $new_project_list;
               unset($new_project_list);
            }
#         }
      }
      unset($current_user);
      if ( $project_list->isNotEmpty() ) {
         $temp_array['item_id'] = -1;
         $temp_array['title'] = $this->_translator->getMessage('MYAREA_PROJECT_INDEX').'';
         $retour[] = $temp_array;
         unset($temp_array);
         $project_item = $project_list->getFirst();
         while ($project_item) {
            $temp_array = array();
            if ( $project_item->isA(CS_PROJECT_TYPE)
               ) {
               $temp_array['item_id'] = $project_item->getItemID();
               $title = $project_item->getTitle();
               $temp_array['title'] = $title;
               if ( $project_item->getItemID() == $this->_environment->getCurrentContextID()
                    and ( !$selected
                          or $selected_future == $project_item->getItemID()
                        )
                  ) {
                  $temp_array['selected'] = true;
                  if ( !empty($selected_future)
                       and $selected_future != 0
                       and $selected_future_pos != -1
                     ) {
                     $selected_future = 0;
                     unset($future_array[$selected_future_pos]['selected']);
                  }
                  $selected = true;
               }

               // grouprooms
#               if ( $portal_item->showGrouproomConfig() ) {
                  if ( isset($project_grouproom_array[$project_item->getItemID()]) and !empty($project_grouproom_array[$project_item->getItemID()]) and $project_item->isGrouproomActive()) {
                     $group_result_array = array();
                     $project_grouproom_array[$project_item->getItemID()]= array_unique($project_grouproom_array[$project_item->getItemID()]);
                     foreach ($project_grouproom_array[$project_item->getItemID()] as $value) {
                        $group_temp_array = array();
                        $group_temp_array['item_id'] = $value;
                        $group_temp_array['title'] = '- '.$grouproom_array[$value];
                        if ( $value == $this->_environment->getCurrentContextID()
                             and ( !$selected
                                   or $selected_future == $value
                                 )
                           ) {
                           $group_temp_array['selected'] = true;
                           $selected = true;
                           if ( !empty($selected_future)
                                and $selected_future != 0
                                and $selected_future_pos != -1
                              ) {
                              $selected_future = 0;
                              unset($future_array[$selected_future_pos]['selected']);
                           }
                        }
                        $group_result_array[] = $group_temp_array;
                        unset($group_temp_array);
                     }
                  }
#               }
            } else {
               $with_title = true;
               $temp_array['item_id'] = -2;
               $title = $project_item->getTitle();
               if (!empty($title) and $title != 'COMMON_NOT_LINKED') {
                  $temp_array['title'] = $this->_translator->getTimeMessage($title);
               } else {
                  $temp_array['title'] = $this->_translator->getMessage('COMMON_NOT_LINKED');
                  $no_time = true;
               }
               if (!empty($title) and $title == $current_time) {
               // if (!empty($title) and !empty($current_time) and $title == $current_time) {
                  $future = false;
               }
            }
            if ($portal_item->showTime()) {
               if ($no_time) {
                  $no_time_array[] = $temp_array;
                  if ( isset($group_result_array) and !empty($group_result_array) ) {
                     $no_time_array = array_merge($no_time_array,$group_result_array);
                     unset($group_result_array);
                  }
               } elseif ($future) {
                  if ($temp_array['item_id'] != -2) {
                     $future_array[] = $temp_array;
                     if ( !empty($temp_array['selected']) and $temp_array['selected'] ) {
                        $selected_future = $temp_array['item_id'];
                        $selected_future_pos = count($future_array)-1;
                     }
                     if ( isset($group_result_array) and !empty($group_result_array) ) {
                         $future_array = array_merge($future_array,$group_result_array);
                         unset($group_result_array);
                     }
                  }
               } else {
                  $retour[] = $temp_array;
                  if ( isset($group_result_array) and !empty($group_result_array) ) {
                      $retour = array_merge($retour,$group_result_array);
                      unset($group_result_array);
                  }
               }
            } else {
               $retour[] = $temp_array;
               if ( isset($group_result_array) and !empty($group_result_array) ) {
                    $retour = array_merge($retour,$group_result_array);
                  unset($group_result_array);
               }
            }
            unset($temp_array);
            unset($project_item);
            $project_item = $project_list->getNext();
         }
         unset($project_list);
   if ($portal_item->showTime()) {

      // special case, if no room is linked to a time pulse
      if (isset($with_title) and !$with_title) {
         $temp_array = array();
         $temp_array['item_id'] = -2;
         $temp_array['title'] = $this->_translator->getMessage('COMMON_NOT_LINKED');
         $retour[] = $temp_array;
         unset($temp_array);
         $retour = array_merge($retour,$future_array);
         $future_array = array();
      }

      if (!empty($future_array)) {
         $future_array2 = array();
         $future_array3 = array();
         foreach ($future_array as $element) {
            if ( !in_array($element['item_id'],$future_array3) ) {
                     $future_array3[] = $element['item_id'];
                     $future_array2[] = $element;
            }
         }
         $future_array = $future_array2;
         unset($future_array2);
         unset($future_array3);
         $temp_array = array();
         $temp_array['title'] = $this->_translator->getMessage('COMMON_IN_FUTURE');
         $temp_array['item_id'] = -2;
         $future_array_begin = array();
         $future_array_begin[] = $temp_array;
         $future_array = array_merge($future_array_begin,$future_array);
         unset($temp_array);
         $retour = array_merge($retour,$future_array);
      }

      if (!empty($no_time_array)) {
         $retour = array_merge($retour,$no_time_array);
      }
         }
      }
      unset($portal_item);
      $this->translatorChangeToCurrentContext();
      return $retour;
      }
   }

   function translatorChangeToPortal () {
     $current_portal = $this->_environment->getCurrentPortalItem();
     if (isset($current_portal)) {
       $this->_translator->setContext(CS_PORTAL_TYPE);
       $this->_translator->setRubricTranslationArray($current_portal->getRubricTranslationArray());
       $this->_translator->setEmailTextArray($current_portal->getEmailTextArray());
     }
   }

   function translatorChangeToCurrentContext () {
     $current_context = $this->_environment->getCurrentContextItem();
     if (isset($current_context)) {
         if ($current_context->isCommunityRoom()) {
          $this->_translator->setContext(CS_COMMUNITY_TYPE);
         } elseif ($current_context->isProjectRoom()) {
          $this->_translator->setContext(CS_PROJECT_TYPE);
         } elseif ($current_context->isPortal()) {
          $this->_translator->setContext(CS_PORTAL_TYPE);
       } else {
          $this->_translator->setContext(CS_SERVER_TYPE);
       }
       $this->_translator->setRubricTranslationArray($current_context->getRubricTranslationArray());
       $this->_translator->setEmailTextArray($current_context->getEmailTextArray());
     }
   }


	function getRoomListArray() {
		$return = array();
		
		$context_array = $this->_getAllOpenContextsForCurrentUser();
		$current_portal = $this->_environment->getCurrentPortalItem();
		$context_manager = $this->_environment->getRoomManager();
		$room_array = array();
		
		// this holds last headline and subline
		$headline = '';
		$subline = '';
		
		foreach($context_array as $context) {
			$item_id = $context['item_id'];
			$title = $context['title'];
			$selected = isset($context['selected']) ? $context['selected'] : '';
			
			$room = array();
			$additional = '';
			
			// selected
			if(isset($selected) && !empty($selected)) {
				$additional = 'selected';
			}
			
			// empty or headline
			if($item_id == -1) {
				$additional = 'disabled';
				if(!empty($title)) {
					// update headline
					$headline = $title;
				}
				
				continue;
			}
			
			// disabled
			if($item_id == -2) {
				$additional = 'disabled';
				
				if(!empty($title)) {
					// update headline
					$subline = $title;
				}
				
				continue;
			}
			
			$room = array(
					'item_id'		=> $item_id,
					'additional'	=> $additional,
					'title'			=> $title
			);
			
			$context_item = $context_manager->getItem($item_id);
			if (is_object($context_item)){
				$room['color_array'] = $context_item->getColorArray();
				$room['activity_array'] = $context_item->getActiveAndAllMembersAsArray();
				$room['page_impressions'] = $context_item->getPageImpressions();
				$room['new_entries'] = $context_item->getNewEntries();
				$room['time_spread'] = $context_item->getTimeSpread();
			}
			
			$return[$headline][$subline]['rooms'][] = $room;
		}
		
		return $return;
	}
}
<?php
require_once('classes/controller/ajax/popup/cs_rubric_popup_controller.php');

class cs_popup_group_controller implements cs_rubric_popup_controller {
    private $_environment = null;
    private $_popup_controller = null;

    /**
     * constructor
     */
    public function __construct(cs_environment $environment, cs_ajax_popup_controller $popup_controller) {
        $this->_environment = $environment;
        $this->_popup_controller = $popup_controller;
    }

    public function initPopup($item, $data) {
			// assign template vars
			$this->assignTemplateVars();
			$current_context = $this->_environment->getCurrentContextItem();

			if($item !== null) {
				// edit mode

				// TODO: check rights

				$this->_popup_controller->assign('item', 'name', $item->getName());
				$this->_popup_controller->assign('item', 'description', $item->getDescription());
 				$this->_popup_controller->assign('item', 'public', $item->isPublic());
			    $this->_popup_controller->assign('item', 'picture', $item->getPicture());
			    if ($item->isGroupRoomActivated()){
			    	$this->_popup_controller->assign('item','group_room_activate','1');
			    }
			    $this->_popup_controller->assign('item','system_label',$item->isSystemLabel());

      			if($current_context->WikiEnableDiscussionNotificationGroups() == 1){
      				$discussion_array = $current_context->getWikiDiscussionArray();

			        $discussion_notification_array = array();
			        $temp_array['text'] = '*'.$this->_translator->getMessage('PREFERENCES_NO_DISCUSSION_NOTIFICATION');
			        $temp_array['value'] = '-1';
			        $discussion_notification_array[] = $temp_array;
			        $temp_array['text'] = '--------------------';
			        $temp_array['value'] = 'disabled';
			        $discussion_notification_array[] = $temp_array;

			        if ( isset($discussion_array) and !empty($discussion_array) ) {
			           foreach ($discussion_array as $discussion) {
			              $temp_array['text'] = $discussion;
			              $temp_array['value'] = $discussion;
			              $discussion_notification_array[] = $temp_array;
			           }
			        }

      				$_discussion_notification_array = $discussion_notification_array;

			        $discussion_notification_array = array();

   		            $discussion_notification_array = $this->_item->getDiscussionNotificationArray();
			        if (isset($discussion_notification_array[0])) {
			            foreach ($discussion_notification_array as $discussion_notification) {
			               $temp_array['text'] = $discussion_notification;
			               $temp_array['value'] = $discussion_notification;
			               $discussion_notification_array[] = $temp_array;
			            }
			        }
      				$_shown_discussion_notification_array = $discussion_notification_array;
         		   	if ( !empty ($_shown_discussion_notification_array) ) {
            	       $this->_popup_controller->assign('item','discussion_notification_list',$_shown_discussion_notification_array);
         		   	}
         		   	$this->_popup_controller->assign('item','discussion_notification',$_discussion_notification_array);
 			   }

			}else{


			}
    }

    public function save($form_data, $additional = array()) {
        $environment = $this->_environment;
        $current_user = $this->_environment->getCurrentUserItem();
        $current_context = $this->_environment->getCurrentContextItem();

        if(isset($additional['action']) && $additional['action'] === 'upload_picture') $current_iid = $additional['iid'];
        else $current_iid = $form_data['iid'];

        $translator = $this->_environment->getTranslationObject();

        if($current_iid === 'NEW') {
            $item = null;
        } else {
            $item_manager = $this->_environment->getGroupManager();
            $item = $item_manager->getItem($current_iid);
        }

        // TODO: check rights */
		/****************************/
        if ( $current_iid != 'NEW' and !isset($item) ) {

        } elseif ( !(($current_iid == 'NEW' and $current_user->isUser()) or
        ($current_iid != 'NEW' and isset($item) and
        $item->mayEdit($current_user))) ) {

		/****************************/


        } else { //Acces granted
			$this->cleanup_session($current_iid);

			// upload picture
			if(isset($additional['action']) && $additional['action'] === 'upload_picture') {
				if($this->_popup_controller->checkFormData('picture_upload')) {
					/* handle group picture upload */
					if(!empty($_FILES['form_data']['tmp_name'])) {
						// rename temp file
						$new_temp_name = $_FILES['form_data']['tmp_name']['picture'] . '_TEMP_' . $_FILES['form_data']['name']['picture'];
						move_uploaded_file($_FILES['form_data']['tmp_name']['picture'], $new_temp_name);
						$_FILES['form_data']['tmp_name']['picture'] = $new_temp_name;


						// resize image to a maximum width of 150px and keep ratio
						$srcfile = $_FILES['form_data']['tmp_name']['picture'];
						$target = $_FILES['form_data']['tmp_name']['picture'];

						// determ new file name
						$filename_info = pathinfo($_FILES['form_data']['name']['picture']);
						$filename = 'cid' . $this->_environment->getCurrentContextID() . '_iid' . $item->getItemID() . '_'. $_FILES['form_data']['name']['picture'];
						// copy file and set picture
						$disc_manager = $this->_environment->getDiscManager();

						$disc_manager->copyFile($_FILES['form_data']['tmp_name']['picture'], $filename, true);
						$item->setPicture($filename);
						$item->save();

						$this->_return = 'success';
					}
				}
			} else {
				// save item
				if($this->_popup_controller->checkFormData('general')) {
					$session = $this->_environment->getSessionItem();
					$item_is_new = false;
					// Create new item
					if ( !isset($item) ) {
						$item_manager = $environment->getGroupManager();
						$item = $item_manager->getNewItem();
						$item->setContextID($environment->getCurrentContextID());
						$current_user = $environment->getCurrentUserItem();
						$item->setCreatorItem($current_user);
						$item->setCreationDate(getCurrentDateTimeInMySQL());
               			$item->setLabelType(CS_GROUP_TYPE);
						$item_is_new = true;
					}

					// Set modificator and modification date
					$current_user = $environment->getCurrentUserItem();
					$item->setModificatorItem($current_user);

					// Set attributes
					if ( isset($form_data['name']) ) {
						$item->setName($form_data['name']);
					}
					if ( isset($form_data['description']) ) {
						$item->setDescription($form_data['description']);
					}
					if (isset($form_data['public'])) {
						$item->setPublic($form_data['public']);
					}

					if ( isset($form_data['group_room_activate']) ) {
						$item->setGroupRoomActive();
					}else{
						$item->unsetGroupRoomActive();
					}

					if($item->getPicture() && isset($form_data['delete_picture'])) {
						$disc_manager = $this->_environment->getDiscManager();

						if($disc_manager->existsFile($item->getPicture())) $disc_manager->unlinkFile($item->getPicture());
						$item->setPicture('');
					}

					// Foren:
					$discussion_notification_array = array();
					if ( isset($form_data['discussion_notification_list']) ) {
						$discussion_notification_array = $form_data['discussion_notification_list'];
					}
					if ( isset($form_data['discussion_notification'])
							and !in_array($form_data['discussion_notification'],$discussion_notification_array)
							and ($form_data['discussion_notification'] != -1)
							and ($form_data['discussion_notification'] != 'disabled')
					) {
						$discussion_notification_array[] = $form_data['discussion_notification'];
					}

					$item->setDiscussionNotificationArray($discussion_notification_array);
					// Save item
					$item->save();

					// this will update the right box list
					if($item_is_new){
						if ($session->issetValue('cid'.$environment->getCurrentContextID().'_'.CS_GROUP_TYPE.'_index_ids')){
							$id_array =  array_reverse($session->getValue('cid'.$environment->getCurrentContextID().'_'.CS_GROUP_TYPE.'_index_ids'));
						} else {
							$id_array =  array();
						}

						$id_array[] = $item->getItemID();
						$id_array = array_reverse($id_array);
						$session->setValue('cid'.$environment->getCurrentContextID().'_'.CS_GROUP_TYPE.'_index_ids',$id_array);
					}

					// save session
					$this->_environment->getSessionManager()->save($session);

					// Add modifier to all users who ever edited this item
					$manager = $environment->getLinkModifierItemManager();
					$manager->markEdited($item->getItemID());

					// set return
                	$this->_popup_controller->setSuccessfullItemIDReturn($item->getItemID());
				}
			}
        }
    }

    public function isOption( $option, $string ) {
        return (strcmp( $option, $string ) == 0) || (strcmp( htmlentities($option, ENT_NOQUOTES, 'UTF-8'), $string ) == 0 || (strcmp( $option, htmlentities($string, ENT_NOQUOTES, 'UTF-8') )) == 0 );
    }

    private function assignTemplateVars() {
        $current_user = $this->_environment->getCurrentUserItem();
        $current_context = $this->_environment->getCurrentContextItem();

        // general information
        $general_information = array();

        // max upload size
        $val = $current_context->getMaxUploadSizeInBytes();
        $meg_val = round($val / 1048576);
        $general_information['max_upload_size'] = $meg_val;

        $this->_popup_controller->assign('popup', 'general', $general_information);

        // user information
        $user_information = array();
        $user_information['fullname'] = $current_user->getFullName();
        $this->_popup_controller->assign('popup', 'user', $user_information);

    }


    public function getFieldInformation($sub = '') {
		$return = array(
			'upload_picture'	=> array(
			),

			'general'			=> array(
				array(	'name'		=> 'name',
						'type'		=> 'text',
						'mandatory' => true)
			),
			'description'			=> array(
				array(	'name'		=> 'description',
						'type'		=> 'text',
						'mandatory' => false)
			),
			'public'			=> array(
				array(	'name'		=> 'public',
						'type'		=> 'radio',
						'mandatory' => true)
			),
			'grouproom_activate'=> array(
				array(	'name'		=> 'grouproom_activate',
						'type'		=> 'check',
						'mandatory' => false)
			)


		);

		return $return[$sub];
    }

	public function cleanup_session($current_iid) {
		$environment = $this->_environment;
		$session = $this->_environment->getSessionItem();

		$session->unsetValue($environment->getCurrentModule().'_add_buzzwords');
		$session->unsetValue($environment->getCurrentModule().'_add_tags');
		$session->unsetValue($environment->getCurrentModule().'_add_files');
		$session->unsetValue($current_iid.'_post_vars');
	}


}
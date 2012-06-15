<?php
require_once('classes/controller/ajax/popup/cs_rubric_popup_controller.php');

class cs_popup_todo_controller implements cs_rubric_popup_controller {
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
			$translator = $this->_environment->getTranslationObject();

			if($item !== null) {
				// edit mode

				// TODO: check rights

				// files
				$attachment_infos = array();

				$converter = $this->_environment->getTextConverter();
				$file_list = $item->getFileList();

				$file = $file_list->getFirst();
				while($file) {
					$info['file_name']	= $converter->text_as_html_short($file->getDisplayName());
					$info['file_icon']	= $file->getFileIcon();
					$info['file_id']	= $file->getFileID();

					$attachment_infos[] = $info;
					$file = $file_list->getNext();
				}
				$this->_popup_controller->assign('item', 'files', $attachment_infos);

				$this->_popup_controller->assign('item', 'title', $item->getTitle());
				$this->_popup_controller->assign('item', 'description', $item->getDescription());
 				$this->_popup_controller->assign('item', 'public', $item->isPublic());
 				$this->_popup_controller->assign('item', 'status', $item->getInternalStatus());
 				$this->_popup_controller->assign('item', 'time_type', $item->getTimeType());

			    $status_array = array();
			    $temp_array['text']  = $translator->getMessage('TODO_NOT_STARTED');
			    $temp_array['value'] = '1';
			    $status_array[] = $temp_array;
			    $temp_array['text']  = $translator->getMessage('TODO_IN_POGRESS');
			    $temp_array['value'] = '2';
			    $status_array[] = $temp_array;
			    $temp_array['text']  = $translator->getMessage('TODO_DONE');
			    $temp_array['value'] = '3';
			    $status_array[] = $temp_array;

			    $extra_status_array = $current_context->getExtraToDoStatusArray();
			    foreach ($extra_status_array as $key => $value){
			       $temp_array['text']  = $value;
			       $temp_array['value'] = $key;
			       $status_array[] = $temp_array;
			    }
 				$this->_popup_controller->assign('item', 'status_array', $status_array);


		        $time = $item->getPlannedTime();
		        $minutes = $item->getPlannedTime();
		        switch ($item->getTimeType()){
		           case 2: $minutes = $minutes/60;break;
		           case 3: $minutes = ($minutes/60)/8;break;
		        }
		        if ($translator->getSelectedLanguage() == 'de'){
		           $minutes = str_replace('.',',',$minutes);
		        }
 				$this->_popup_controller->assign('item', 'minutes', $minutes);

			    if ($item->getDate() != '' and $item->getDate() != '9999-00-00 00:00:00') {
 					$this->_popup_controller->assign('item', 'day_end',  getDateInLang($item->getDate()));
			    } else {
 					$this->_popup_controller->assign('item', 'day_end',  '');
			    }
			    if ($item->getDate() != '' and $item->getDate() != '9999-00-00 00:00:00') {
 					$this->_popup_controller->assign('item', 'time_end',  getTimeInLang($item->getDate()));
			    } else {
 					$this->_popup_controller->assign('item', 'time_end', '');
			    }

				$activating = false;
				if($current_context->withActivatingContent()) {
					$activating = true;

					$this->_popup_controller->assign('item', 'private_editing', $item->isPrivateEditing());

					if($item->isNotActivated()) {
						$this->_popup_controller->assign('item', 'is_not_activated', true);

						$activating_date = $item->getActivatingDate();

						$this->_popup_controller->assign('item', 'activating_date', mb_substr($activating_date, 0, 10));
						$this->_popup_controller->assign('item', 'activating_time', mb_substr($activating_date, -8));
					}
				}

				$this->_popup_controller->assign('popup', 'activating', $activating);
			}else{
			    $status_array = array();
			    $temp_array['text']  = $translator->getMessage('TODO_NOT_STARTED');
			    $temp_array['value'] = '1';
			    $status_array[] = $temp_array;
			    $temp_array['text']  = $translator->getMessage('TODO_IN_POGRESS');
			    $temp_array['value'] = '2';
			    $status_array[] = $temp_array;
			    $temp_array['text']  = $translator->getMessage('TODO_DONE');
			    $temp_array['value'] = '3';
			    $status_array[] = $temp_array;

			    $extra_status_array = $current_context->getExtraToDoStatusArray();
			    foreach ($extra_status_array as $key => $value){
			       $temp_array['text']  = $value;
			       $temp_array['value'] = $key;
			       $status_array[] = $temp_array;
			    }
 				$this->_popup_controller->assign('item', 'status_array', $status_array);
 				$val = ($this->_environment->inProjectRoom() OR $this->_environment->inGroupRoom())?'1':'0';
 				$this->_popup_controller->assign('item', 'public', $val);
			}
    }

    public function save($form_data, $additional = array()) {
        $environment = $this->_environment;
        $current_user = $this->_environment->getCurrentUserItem();
        $current_context = $this->_environment->getCurrentContextItem();

        $current_iid = $form_data['iid'];

        $translator = $this->_environment->getTranslationObject();

        if($current_iid === 'NEW') {
            $todo_item = null;
        } else {
            $todo_manager = $this->_environment->getTodoManager();
            $todo_item = $todo_manager->getItem($current_iid);
        }

        // TODO: check rights */
		/****************************/
        if ( $current_iid != 'NEW' and !isset($todo_item) ) {

        } elseif ( !(($current_iid == 'NEW' and $current_user->isUser()) or
        ($current_iid != 'NEW' and isset($todo_item) and
        $todo_item->mayEdit($current_user))) ) {

		/****************************/


        } else { //Acces granted
			$this->cleanup_session($current_iid);

			// save item
			if($this->_popup_controller->checkFormData()) {
                $session = $this->_environment->getSessionItem();
                $item_is_new = false;
                // Create new item
                if ( !isset($todo_item) ) {
                    $todo_manager = $environment->getTodoManager();
                    $todo_item = $todo_manager->getNewItem();
                    $todo_item->setContextID($environment->getCurrentContextID());
                    $current_user = $environment->getCurrentUserItem();
                    $todo_item->setCreatorItem($current_user);
                    $todo_item->setCreationDate(getCurrentDateTimeInMySQL());
                    $item_is_new = true;
                }

                // Set modificator and modification date
                $current_user = $environment->getCurrentUserItem();
                $todo_item->setModificatorItem($current_user);

                // Set attributes
                if ( isset($form_data['title']) ) {
                    $todo_item->setTitle($form_data['title']);
                }

                if ( isset($form_data['description']) ) {
                    $todo_item->setDescription($form_data['description']);
                }
                if (isset($form_data['public'])) {
                    $todo_item->setPublic($form_data['public']);
                }

                // already attached files
                $file_ids = array();
                foreach($form_data as $key => $value) {
                	if(mb_substr($key, 0, 5) === 'file_') {
                		$file_ids[] = $value;
                	}
                }

                // this will handle already attached files as well as adding new files
                $this->_popup_controller->getUtils()->setFilesForItem($todo_item, $file_ids, CS_TODO_TYPE);

                if ( isset($form_data['hide']) ) {
                    // variables for datetime-format of end and beginning
                    $dt_hiding_time = '00:00:00';
                    $dt_hiding_date = '9999-00-00';
                    $dt_hiding_datetime = '';
                    $converted_day_start = convertDateFromInput($form_data['dayStart'],$environment->getSelectedLanguage());
                    if ($converted_day_start['conforms'] == TRUE) {
                        $dt_hiding_datetime = $converted_day_start['datetime'].' ';
                        $converted_time_start = convertTimeFromInput($form_data['timeStart']);
                        if ($converted_time_start['conforms'] == TRUE) {
                            $dt_hiding_datetime .= $converted_time_start['datetime'];
                        }else{
                            $dt_hiding_datetime .= $dt_hiding_time;
                        }
                    }else{
                        $dt_hiding_datetime = $dt_hiding_date.' '.$dt_hiding_time;
                    }
                    $todo_item->setModificationDate($dt_hiding_datetime);
                }else{
                    if($todo_item->isNotActivated()){
                        $todo_item->setModificationDate(getCurrentDateTimeInMySQL());
                    }
                }

	            if ( isset($form_data['status']) ) {
	               $todo_item->setStatus($form_data['status']);
	            }

	            if ( isset($form_data['minutes']) ) {
	               $minutes = $form_data['minutes'];
	               $minutes = str_replace(',','.',$minutes);
	               if (isset($form_data['time_type'])){
	                  $todo_item->setTimeType($form_data['time_type']);
	                  switch ($form_data['time_type']){
	                     case 2: $minutes = $minutes*60;break;
	                     case 3: $minutes = $minutes*60*8;break;
	                  }
	               }
	               $todo_item->setPlannedTime($minutes);
	            }

	            if (isset($form_data['day_end']) and !empty($form_data['day_end'])) {
	               $date2 = convertDateFromInput($form_data['day_end'],$environment->getSelectedLanguage());
	               if (!empty($form_data['time_end'])) {
	                  $time_end = $form_data['time_end'];
	               } else {
	                  $time_end = '0:00';
	               }
	               if (!mb_ereg("(([2][0-3])|([01][0-9])):([0-5][0-9])",$time_end)) {
	                  $time_end='0:00';
	               }
	               $time2 = convertTimeFromInput($time_end);   // convertTimeFromInput
	               if ($date2['conforms'] == TRUE and $time2['conforms'] == TRUE) {
	                  $todo_item->setDate($date2['datetime']. ' '.$time2['datetime']);
	               } else {
	                  $todo_item->setDate($date2['display']. ' '.$time2['display']);
	               }
	            }else{
	               $todo_item->setDate('9999-00-00 00:00:00');
	            }


                // buzzwords
                $todo_item->setBuzzwordListByID($form_data['buzzwords']);

                // tags
                $todo_item->setTagListByID($form_data['tags']);

                // Save item
                $todo_item->save();

                // this will update the right box list
                if($item_is_new){
	                if ($session->issetValue('cid'.$environment->getCurrentContextID().'_'.CS_TODO_TYPE.'_index_ids')){
	                    $id_array =  array_reverse($session->getValue('cid'.$environment->getCurrentContextID().'_'.CS_TODO_TYPE.'_index_ids'));
	                } else {
	                    $id_array =  array();
	                }

                    $id_array[] = $todo_item->getItemID();
                    $id_array = array_reverse($id_array);
                    $session->setValue('cid'.$environment->getCurrentContextID().'_'.CS_TODO_TYPE.'_index_ids',$id_array);
                }

                // save session
                $this->_environment->getSessionManager()->save($session);

                // Add modifier to all users who ever edited this item
                $manager = $environment->getLinkModifierItemManager();
                $manager->markEdited($todo_item->getItemID());

                // set return
                $this->_popup_controller->setSuccessfullItemIDReturn($todo_item->getItemID());
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


        // config information
        $config_information = array();
        $config_information['with_activating'] = $current_context->withActivatingContent();
        $this->_popup_controller->assign('popup', 'config', $config_information);
    }


    public function getFieldInformation($sub = '') {
			return array(
				array(	'name'		=> 'title',
						'type'		=> 'text',
						'mandatory' => true),
				array(	'name'		=> 'description',
						'type'		=> 'textarea',
						'mandatory'	=> false),
				array(	'name'		=> 'status',
						'type'		=> 'select',
						'mandatory'	=> true),
				array(	'name'		=> 'day_end',
						'type'		=> 'text',
						'mandatory'	=> false),
				array(	'name'		=> 'time_end',
						'type'		=> 'text',
						'mandatory'	=> false),
				array(	'name'		=> 'minutes',
						'type'		=> 'text',
						'mandatory'	=> false),
				array(	'name'		=> 'time_type',
						'type'		=> 'select',
						'mandatory'	=> false)
			);
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
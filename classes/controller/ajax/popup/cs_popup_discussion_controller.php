<?php
	require_once('classes/controller/ajax/popup/cs_rubric_popup_controller.php');

	class cs_popup_discussion_controller implements cs_rubric_popup_controller {
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

			if($item !== null) {
				// edit mode
				$current_context = $this->_environment->getCurrentContextItem();

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
 				$val = ($this->_environment->inProjectRoom() OR $this->_environment->inGroupRoom())?'1':'0';
 				$this->_popup_controller->assign('item', 'public', $val);
			}
		}

		public function edit($item_id) {

		}

		public function save($form_data, $additional = array()) {

			$current_user = $this->_environment->getCurrentUserItem();
			$current_context = $this->_environment->getCurrentContextItem();

			$current_iid = $form_data['iid'];
			if($current_iid === 'NEW') {
				$discussion_item = null;
			} else {
				$discussion_manager = $this->_environment->getDiscussionManager();
				$discussion_item = $discussion_manager->getItem($current_iid);
			}


			// TODO: check rights */
			/****************************/
			if($current_context->isProjectRoom() && $current_context->isClosed()) {
				 /* $params = array();
				$params['environment'] = $environment;
				$params['with_modifying_actions'] = true;
				$errorbox = $class_factory->getClass(ERRORBOX_VIEW,$params);
				unset($params);
				$errorbox->setText($translator->getMessage('PROJECT_ROOM_IS_CLOSED', $context_item->getTitle()));
				$page->add($errorbox);
				 */
			} elseif(	!(($current_iid === 'NEW' && $current_user->isUser()) ||
						($current_iid !== 'NEW' && isset($discussion_item) &&
						$discussion_item->mayEditIgnoreClose($current_user)))) {
				/*
				 *    $discussion_item->mayEditIgnoreClose($current_user))) ) {
			$params = array();
			$params['environment'] = $environment;
			$params['with_modifying_actions'] = true;
			$errorbox = $class_factory->getClass(ERRORBOX_VIEW,$params);
			unset($params);
			$errorbox->setText($translator->getMessage('LOGIN_NOT_ALLOWED'));
			$page->add($errorbox);
				 */
			}
			/****************************/


			// access granted
			else {
				$this->cleanup_session($current_iid);

				// save item
				if($this->_popup_controller->checkFormData()) {
					$session = $this->_environment->getSessionItem();

					if($discussion_item === null) {
						$discussion_manager = $this->_environment->getDiscussionManager();
						$discussion_item = $discussion_manager->getNewItem();
						$discussion_item->setContextID($this->_environment->getCurrentContextID());
						$discussion_item->setCreatorItem($current_user);
						$discussion_item->setCreationDate(getCurrentDateTimeInMySQL());
					}

					$discussion_item->setModificatorItem($current_user);

					// set attributes
					if(isset($form_data['title'])) $discussion_item->setTitle($form_data['title']);

					if(isset($form_data['public'])) {
						if($discussion_item->isPublic() != $form_data['public']) {
							$discussion_item->setPublic($form_data['public']);
						}
					} else {
						if(isset($form_data['private_editing'])) {
							$discussion_item->setPrivateEditing('0');
						} else {
							$discussion_item->setPrivateEditing('1');
						}
					}

					if(isset($form_data['external_viewer']) && isset($form_data['external_viewer_accounts'])) {
						$user_ids = explode(" ", $form_data['external_viewer_accounts']);
						$discussion_item->setExternalViewerAccounts($user_ids);
					} else {
						$discussion_item->unsetExternalViewerAccounts();
					}

					if(isset($form_data['hide'])) {
						// variables for datetime-format of end and beginning
						$dt_hiding_time = '00:00:00';
						$dt_hiding_date = '9999-00-00';
						$dt_hiding_datetime = '';
						$converted_day_start = convertDateFromInput($form_data['dayStart'], $this->_environment->getSelectedLanguage());
						if($converted_day_start['conforms'] === true) {
							$dt_hiding_datetime = $converted_day_start['datetime'] . ' ';
							$converted_time_start = convertTimeFromInput($form_data['timeStart']);
							if ($converted_time_start['conforms'] === true) {
								$dt_hiding_datetime .= $converted_time_start['datetime'];
							} else {
								$dt_hiding_datetime .= $dt_hiding_time;
							}
						} else {
							$dt_hiding_datetime = $dt_hiding_date . ' ' . $dt_hiding_time;
						}
						$discussion_item->setModificationDate($dt_hiding_datetime);
					} else {
						if($discussion_item->isNotActivated()) $discussion_item->setModificationDate(getCurrentDateTimeInMySQL());
					}

					// buzzwords
					$discussion_item->setBuzzwordListByID($form_data['buzzwords']);

					// tags
					$discussion_item->setTagListByID($form_data['tags']);

					// save item
					$discussion_item->save();

					// this will update the right box list
					$id_array = array();
					if($session->issetValue('cid' . $this->_environment->getCurrentContextID() . '_' . CS_DISCUSSION_TYPE . '_index_ids')) {
						$id_array = array_reverse($session->getValue('cid' . $this->_environment->getCurrentContextID() . '_' . CS_DISCUSSION_TYPE . '_index_ids'));
					}

					$id_array[] = $discussion_item->getItemID();
					$id_array = array_reverse($id_array);
					$session->setValue('cid' . $this->_environment->getCurrentContextID() . '_' . CS_DISCUSSION_TYPE . '_index_ids', $id_array);

					// this will update the right box list
					if($discussion_item === null){
						if ($session->issetValue('cid'.$environment->getCurrentContextID().'_'.CS_ANNOUNCEMENT_TYPE.'_index_ids')){
							$id_array =  array_reverse($session->getValue('cid'.$environment->getCurrentContextID().'_'.CS_ANNOUNCEMENT_TYPE.'_index_ids'));
						} else {
							$id_array =  array();
						}
						pr($id_array); exit;

						$id_array[] = $announcement_item->getItemID();
						$id_array = array_reverse($id_array);
						$session->setValue('cid'.$environment->getCurrentContextID().'_'.CS_ANNOUNCEMENT_TYPE.'_index_ids',$id_array);
					}

					// save session
					$this->_environment->getSessionManager()->save($session);

					// save initial discussion article
					if($current_iid === 'NEW') {
						$discarticle_manager = $this->_environment->getDiscussionArticlesManager();
						$discarticle_item = $discarticle_manager->getNewItem();
						$discarticle_item->setContextID($this->_environment->getCurrentContextID());
						$discarticle_item->setCreatorItem($current_user);
						$discarticle_item->setCreationDate(getCurrentDateTimeInMySQL());
						$discarticle_item->setDiscussionID($discussion_item->getItemID());

						if(isset($form_data['subject'])) $discarticle_item->setSubject($form_data['subject']);
						if(isset($form_data['description'])) $discarticle_item->setDescription($form_data['description']);
						if(isset($form_data['discussion_type']) && $form_data['discussion_type'] == 2) $discarticle_item->setPosition('1');

						// already attached files
		                $file_ids = array();
		                foreach($form_data as $key => $value) {
		                	if(mb_substr($key, 0, 5) === 'file_') {
		                		$file_ids[] = $value;
		                	}
		                }

		                // this will handle already attached files as well as adding new files
						$this->_popup_controller->getUtils()->setFilesForItem($discarticle_item, $file_ids, $form_data["files"], CS_DISCARTICLE_TYPE);

						$discarticle_item->save();

						// update discussion item
						$discussion_item->setLatestArticleID($discarticle_item->getItemID());
						$discussion_item->setLatestArticleModificationDate($discarticle_item->getCreationDate());

						$discussion_status = $current_context->getDiscussionStatus();
						if($discussion_status == 3) {
							if($form_data['discussion_type'] == 2) $discussion_item->setDiscussionType('threaded');
							else $discussion_item->setDiscussionType('simple');
						} elseif($discussion_status == 2) {
							$discussion_item->setDiscussionType('threaded');
						} else {
							$discussion_item->setDiscussionType('simple');
						}

						$discussion_item->save();
					}

					// set return
                	$this->_popup_controller->setSuccessfullItemIDReturn($discussion_item->getItemID());
				}
			}
		}

		public function getFieldInformation($sub = '') {
			return array(
				array(	'name'		=> 'title',
						'type'		=> 'text',
						'mandatory' => true),
				array(	'name'		=> 'description',
						'type'		=> 'text',
						'mandatory'	=> false)
			);
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

		public function cleanup_session($current_iid) {
			$environment = $this->_environment;
			$session = $this->_environment->getSessionItem();

			$session->unsetValue($environment->getCurrentModule().'_add_buzzwords');
			$session->unsetValue($environment->getCurrentModule().'_add_tags');
			$session->unsetValue($environment->getCurrentModule().'_add_files');
			$session->unsetValue($current_iid.'_post_vars');
		}
	}
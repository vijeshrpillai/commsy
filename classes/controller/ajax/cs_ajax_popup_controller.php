<?php
	require_once('classes/controller/cs_ajax_controller.php');
	require_once('classes/controller/cs_room_controller.php');

	class cs_ajax_popup_controller extends cs_ajax_controller {
		protected $_popup_controller = null;

		/**
		 * constructor
		 */
		public function __construct(cs_environment $environment) {
			// call parent
			parent::__construct($environment);
		}

		public function actiongetHTML() {
			// get module from submitted data
			$module = $this->_data['module'];
			$this->_tpl_file = 'popups/' . $module . '_popup';

			// include
			require_once('classes/controller/ajax/popup/cs_popup_' . $module . '_controller.php');
			$class_name = 'cs_popup_' . $module . '_controller';
			$this->_popup_controller = new $class_name($this->_environment, $this);

			// initPopup
			$this->initPopup();

			global $c_smarty;
			if($c_smarty === true) {
				ob_start();

				$this->displayTemplate();

				// setup return
				$output = ob_get_clean();
				$this->setSuccessfullHTMLReturn($output);

				//echo preg_replace('/\s/', '', $this->_return);
				//echo str_replace(array('\n', '\t'), '', $this->_return);		// for some reasons, categories in popup will not work if active
				echo $this->_return;

			} else {
				echo json_encode('smarty not enabled');
			}
		}

		protected function initPopup() {
			$this->_popup_controller->initPopup();
		}

		public function actionSave() {
			// include
			$module = $this->_data['module'];
			require_once('classes/controller/ajax/popup/cs_popup_' . $module . '_controller.php');
			$class_name = 'cs_popup_' . $module . '_controller';
			$this->_popup_controller = new $class_name($this->_environment, $this);

			// get form data
			$form_data = array();
			foreach($this->_data['form_data'] as $data) {
				$form_data[$data['name']] = $data['value'];
			}

			// additional data
			$additional = array();
			if(isset($this->_data['additional']) && !empty($this->_data['additional'])) {
				$additional = $this->_data['additional'];
			}

			$this->_popup_controller->save($form_data, $additional);

			echo $this->_return;
		}

		public function checkFormData($sub = '') {
			try {
				$this->checkForm($sub);

				return true;
			} catch(cs_form_mandatory_exception $e) {
				// setup return array
				$this->setErrorReturn($e->getCode(), $e->getMessage(), $e->getMissingFields());

				echo $this->_return;

				return false;
			} catch(cs_form_value_exception $e) {
				// TODO: implement in edit form
				echo "value catched";

				return false;
			}
		}

		public function setSuccessfullItemIDReturn($item_id, $module = '') {
			// setup return

			if (empty($module)){
				$item_manager = $this->_environment->getItemManager();
				$item = $item_manager->getItem($item_id);
				$module = $item->getItemType();
			}

			$return = array(
				'status'	=> 'success',
				'item_id'	=> $item_id,
				'module'	=> $module
			);

			$this->_return = json_encode($return);
		}

		private function setSuccessfullHTMLReturn($html) {
			// setup return
			$return = array(
				'status'=> 'success',
				'html'	=> $html
			);

			$this->_return = json_encode($return);
		}

		private function checkForm($sub) {
			// get form data
			$form_data = array();
			foreach($this->_data['form_data'] as $data) {
				$form_data[$data['name']] = $data['value'];
			}

			$missing_fields = array();
			foreach($this->_popup_controller->getFieldInformation($sub) as $field) {
				// check mandatory
				if(isset($field['mandatory']) && $field['mandatory'] === true) {
					if(!isset($form_data[$field['name']]) || trim($form_data[$field['name']]) === '') {
						// add to missing
						$missing_fields[] = $field['name'];
					}
				}

				// check values
				// TODO:
				//throw new cs_form_value_exception('value exception');
			}

			if(!empty($missing_fields)) {
				// setup new exception
				$exception = new cs_form_mandatory_exception('missing_mandatory field', 101);
				$exception->setMissingFields($missing_fields);

				throw $exception;
			}
		}

		public function getUtils() {
			return parent::getUtils();
		}

		/*
		 * every derived class needs to implement an processTemplate function
		 */
		public function process() {
			// call parent
			parent::process();

		}

		public function assign($categorie, $key, $assignment) {
			parent::assign($categorie, $key, $assignment);
		}
	}
<?php

/**
 * Base forms class
 * @author Mikhail Kelner
 */
abstract class OPAL_Form {
	
	protected $fields = array();
	protected $errors = array();
	protected $areas  = array();
	protected $action = '';
	protected $method = 'post';
	protected $formname = '';
	protected $addafter = array();
	
	public function __construct($action = '', $method = 'post', $params = array()){
		$this->action = $action;
		$this->method = $method;
		$this->formname = strtolower(get_class($this));
		$this->build($params);
	}
	 
	public function setFormName($formname){
		$this->formname = $formname;
	}
	
	abstract protected function build($params);
	
	public function getValues(){
		$data = array();
		foreach ($this->fields as $id => $field){
			$name = isset($this->fields[$id]['name']) ? $this->fields[$id]['name'] : $id; //TODO Add support of array's indexes
			if (array_key_exists($id.'[]', $this->areas)){
				$multirowData = array();
				if ($multirowRawData = isset($field['value']) ? $field['value'] : null){
					foreach ($multirowRawData as $multirowRawDataKey => $multirowRawDataValues){
						if ($multirowRawDataKey != '_'){ //TODO If only array key? Weird, but...
							foreach ($multirowRawDataValues as $multirowRawDataRowIndex => $multirowRawDataRowValue){
								$multirowRawDataRowKey = !empty($multirowRawData['_']) ? $multirowRawData['_'][$multirowRawDataRowIndex] : $multirowRawDataRowIndex;
								if (!isset($multirowData[$multirowRawDataRowKey])){
									$multirowData[$multirowRawDataRowKey] = array();
								}
								if ($multirowRawDataKey == '*'){
									$multirowData[$multirowRawDataRowKey] = $multirowRawDataRowValue;
								} else {
									$multirowData[$multirowRawDataRowKey][$multirowRawDataKey] = $multirowRawDataRowValue;
								}
							}
						}
					}
				}
				$data[$name] = $multirowData;
				//Remove empty rows
				foreach ($data[$name] as $rowID => $rowData){
					$rowDataSum = 0;
					if (is_array($rowData)){
						foreach ($rowData as $rowDataValue){
							$rowDataSum += is_array($rowDataValue) ? count($rowDataValue) : strlen($rowDataValue);
						}
					} else {
						$rowDataSum = strlen($rowData);
					}
					if (!$rowDataSum){
						unset($data[$name][$rowID]);
					}
				}
			} else if (strpos($id, ':') === false){
				$multi = (strpos($name, '[]') !== false);
				$name = str_replace('[]', '', $name);
				if (in_array($this->fields[$id]['type'],array('checkbox','radio'))){
					if ($multi){
						if (!isset($data[$name])){
							$data[$name] = array();
						}
					}
					if ($multi){
						if (!empty($this->fields[$id]['checked'])){
							if (isset($field['value'])){
								$data[$name][] = $field['value'];
							}
						}
					} else {
						if (!empty($this->fields[$id]['checked'])){
							$data[$name] = isset($field['value']) ? $field['value'] : null;
						} else {
							$data[$name] = null;
						}
					}
				} else {
					$data[$name] = isset($field['value']) ? $field['value'] : null;
				}
			}
		}
		return $data;
	}
	
	public function setValues($values = null,$validate = false){
		if ($values instanceof \Orange\Database\ActiveRecord){
			$values = $values->getData();
		} else if (is_array($values)){
			//OK, $values is already array
		} else if ($this->method == 'post'){
			$values = &$_POST;
		} elseif ($this->method == 'get'){
			$values = &$_GET;
		} else {
			$values = &$_REQUEST;
		}
		foreach ($this->fields as $id => $field){
			$name = isset($this->fields[$id]['name']) ? $this->fields[$id]['name'] : $id;
			$multi = (strpos($name, '[]') !== false);
			$name = str_replace('[]', '', $name);
			if (in_array($this->fields[$id]['type'],array('checkbox','radio'))){
				if ($multi){
					if (!isset($data[$name])){
						$data[$name] = array();
					}
				}
				$value = isset($values[$name]) ? $values[$name] : array();
				if ($multi){
					if (in_array($this->fields[$id]['value'], $value)){
						$this->fields[$id]['checked'] = 'checked';
					}
				} else {
					if (isset($this->fields[$id]['value']) && ($this->fields[$id]['value'] == $value)){
						$this->fields[$id]['checked'] = 'checked';
					}
				}
			} else {
				$this->fields[$id]['value'] = isset($values[$name]) ? $values[$name] : null;
				if ($validate){
					if ($this->fields[$id]['type'] == 'number'){
						if (!is_numeric($this->fields[$id]['value'])){
							$this->setError($id, 'FORM_VALIDATION_ERROR_VALUE_IS_NOT_NUMBER');
						}
					} else if ($this->fields[$id]['type'] == 'email'){
						if (!empty($this->fields[$id]['value']) && !filter_var($this->fields[$id]['value'],FILTER_VALIDATE_EMAIL)){
							$this->setError($id, 'FORM_VALIDATION_ERROR_VALUE_IS_NOT_EMAIL');
						}
					}
                    if (!empty($this->fields[$id]['required'])){
                        if ($this->fields[$id]['value'] === ''){
                            $this->setError($id, 'FORM_VALIDATION_ERROR_VALUE_IS_EMPTY');
                        }
                    }
                    if (!empty($this->fields[$id]['maxlength'])){
                        if (mb_strlen($this->fields[$id]['value']) > intval($this->fields[$id]['maxlength'])){
                            $this->setError($id, 'FORM_VALIDATION_ERROR_MAX_LENGTH');
                        }
                    }
				}
			}
		}
		return $validate ? $this->errors : array();
	}

	public function setError($id,$text){
		if (!isset($this->errors[$id])){
			$this->errors[$id] = array();
		}
		$this->errors[$id][] = $text;
	}
	
	public function getErrors(){
		return $this->errors;
	}
	
	public function resetErrors(){
		$this->errors = array();
	}
	
	protected function addField($id,$type,$label = null,$params = array(),$area = ''){
		$field = array('id' => $id,'type' => $type,'label' => $label);
		if ($params){
			$field = array_merge($field,$params);
		}
		$this->fields[$id] = $field;
		if (!isset($this->areas[$area])){
			$this->areas[$area] = array();
		}
		$this->areas[$area][] = array('field',$id);
	}

	protected function addHTML($html,$area = ''){
		if (!isset($this->areas[$area])){
			$this->areas[$area] = array();
		}
		$this->areas[$area][] = array('html',$html);
	}

	protected function addTemplate($template,$area = ''){
		if (!isset($this->areas[$area])){
			$this->areas[$area] = array();
		}
		$this->areas[$area][] = array('template',$template);
	}
	
	protected function addFieldset($label,$area = '',$id = ''){
		if (!isset($this->areas[$area])){
			$this->areas[$area] = array();
		}
		$this->areas[$area][] = array('fieldset',$label,$id);
	}
	
	protected function addMultirow($area_name,$area = ''){
		if (!isset($this->areas[$area])){
			$this->areas[$area] = array();
		}
		$this->addField($area_name, 'virtual');
		$this->areas[$area][] = array('multirow',$area_name.'[]');
	}

	protected function addAfter($a){
		$this->addafter[] = $a;
	}
	
	/**
	 * @param OPAL_Templater $templater
	 * @param string $prefix
	 * @return string
	 */
	public function getHTML($templater,$prefix = 'default'){
		return $templater->fetch('form-'.$prefix . '.phtml',array(
			'fields'     => $this->fields,
			'errors'     => $this->errors,
			'areas'      => $this->areas,
			'action'     => $this->action,
			'method'     => $this->method,
			'formname'   => $this->formname,
			'addafter'   => $this->addafter,
		));
	}
	
}
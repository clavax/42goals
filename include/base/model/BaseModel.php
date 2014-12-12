<?php
import('cherokee.DB.PrimaryTable');
import('lib.xml');

class BaseModel extends Object
{   
    protected $name;
    protected $fields;
    protected $data;
    
	protected $dataNew; // added by KM @clavax shall use it as global variable within class
   
	protected $errors;
    protected $Primary;

    protected $select_single_field;
    protected $select_single_row;
    
    const FLD_ID        = 'id';
    
    public function __construct($name)
    {
        $this->name = $name;
        $this->Primary = new PrimaryTable($name, self::FLD_ID);
    
        $Conf = Framework::get_object('Conf');
        if (file_exists($config = $Conf->PTH->data . 'model/' . $name . '.xml')) {
			//print $config;
            $doc = xDOMDocument::fromXmlFile($config);
            $doctype = $Conf->PTH->data . 'model/model.dtd';
            $doc->addDoctype('model', null, $doctype);
			
            if ($doc->validate()) {
                $this->readConfig($config);
            } else {
                //describe('invalid model!', 2);
            }
        } else {
            $this->createConfig();
        }
    }
    
    protected function createConfig()
    {
        $fields = $this->Primary->list_fields();
        $empty = array_fill(0, count($fields), array());
        $this->fields['primary'] = array_combine($fields, $empty);

        $config = array('model' => array('table' => array()));
        
        foreach ($this->fields as $type => $fields) {
            $table = array('@attributes' => array('type' => $type));
            foreach ($fields as $field => $options) {
                $table['field'][] = array('@attributes' => array('name' => $field));
            }
            $config['model']['table'][] = $table; 
        }
        
        $Conf = Framework::get_object('Conf');
        $path = $Conf->PTH->data . 'model/' . $this->name . '.xml';
        $xml = '<?xml version="1.0"?>' . xml::from_array($config);
        file_put_contents($path, $xml);
    }
    
    protected function readConfig($config)
    {
        $doc = xDOMDocument::fromXmlFile($config);
        $model = $doc->firstChild;
        $fields = array();
        foreach ($model->childNodes as $table) {
            if (!($table instanceof DOMElement)) {
                continue;
            }
            $type = $table->getAttribute('type');
            foreach ($table->childNodes as $field) {
                if (!($field instanceof DOMElement)) {
                    continue;
                }
                $name = $field->getAttribute('name');
                $fields[$type][$name] = array(
                    'required' => $field->hasAttribute('required') ? ($field->getAttribute('required') == 'yes' ? true : false) : false,
                    'unique'   => $field->hasAttribute('unique') ? $field->getAttribute('unique') : false,
                    'default'  => $field->hasAttribute('default') ? $field->getAttribute('default') : false,
                );
                
                foreach ($field->getElementsByTagName('validate') as $validate) {
                    $validation = array();
                    
                    // get function
                    $function = $validate->getElementsByTagName('function')->item(0);
                    $method   = $function->getElementsByTagName('method')->item(0)->nodeValue;
                    
                    $class    = $function->getElementsByTagName('class');
                    $object   = $function->getElementsByTagName('object');
                    
                    if ($class->length) {
                        $class = $class->item(0)->nodeValue;
                        if (class_exists($class)) {
                            $validation['function'] = array($class, $method);
                        } else {
                            $this->Error->log('Class ' . $class . ' does not exists for validation');
                        }
                    } elseif ($object->length) {
                        $object = $object->item(0)->nodeValue;
                        if (isset($$object) && method_exists($$object, $method)) {
                            $validation['function'] = array(&$$object, $method);
                        }
                    } else {
                        $validation['function'] = $method;
                    }
                    
                    // get arguments
                    $arguments = $validate->getElementsByTagName('argument');
                    foreach ($arguments as $argument) {
                        if (!($argument instanceof DOMElement)) {
                            continue;
                        }
                        $validation['arguments'][] = $argument->nodeValue;
                    }
                    
                    if (isset($validation['function']) && isset($validation['arguments'])) {
                        $error = $validate->getElementsByTagName('error');
                        if ($error->length) {
                            $validation['error'] = $error->item(0)->nodeValue;
                        }
                        $fields[$type][$name]['validation'][] = $validation;
                    }
                }
            }
        }
        $this->fields = $fields;
    }
    
    protected function listFields($type)
    {
        if(is_array($this->fields))
		{
			return array_keys($this->fields[$type]);
		}
		else
		{
			return array();	
		}
    }
        
    public function __get($name)
    {
        $value = null;
        
        switch ($name) {
        case 'name':
            $value = $this->name;
            break;
        case 'errors':
            $value = $this->errors;
            break;
        default:
            return parent::__get($name);
        }
        
        return $value;
    }
    
    protected function validate($data, $id = false)
    {
        $errors = array();
		if(is_array($this->fields))
		{
			foreach ($this->fields as $fields) {
				foreach ($fields as $name => $options) {
					if (!isset($data[$name])) {
						if (isset($options['required']) && !empty($options['required']) && !$id) {
							$errors[$name] = 'empty';
						}
						continue;
					} elseif (empty($data[$name])) {
						if (isset($options['required']) && !empty($options['required'])) {
							$errors[$name] = 'empty';
						}
						continue;
					}
					
					// check for uniqueness
					if (isset($options['unique']) && !empty($options['unique']) && isset($data[$name])) {
						switch ($options['unique']) {
						case 'yes':
							$condition = '% = ?';
							break;
						case 'i':
							$condition = '% ' . SQL::ILIKE . ' ?';
							break;
						default:
							$this->Error->handle(0, 'Unknown option for unique value ' . $options['unique']);
						}
						
						if ($id) {
							if ($this->count($name, SQL::quote($condition . ' and % != ?', $name, $data[$name], $this->Primary->pk, $id))) {
								$errors[$name] = 'not_unique';
								continue;
							}
						} else {
							if ($this->count($name, SQL::quote($condition, $name, $data[$name]))) {
								$errors[$name] = 'not_unique';
								continue;
							}
						}
					}

					// perform custom validation
					if (isset($options['validation']) && isset($data[$name])) {
						foreach ($options['validation'] as $validation) {
							$id_needed = in_array($this->Primary->pk, $validation['arguments']);
							if (!$id_needed || ($id_needed && $id)) {
								$arguments = array();
								foreach ($validation['arguments'] as $arg) {
									$arguments[] = isset($data[$arg]) ? $data[$arg] : null;
								}
								if (!$arguments) {
									$this->Error->handle(0, 'No arguments for validation');
									continue;
								}
								if (!call_user_func_array($validation['function'], $arguments)) {
									$error = array_get($validation, 'error', 'wrong'); 
									break;
								}
							}
						}
						if (isset($error)) {
							$errors[$name] = $error;
							unset($error);
							continue;
						}
					}
					
					// assign default value
					if (isset($options['default']) && $options['default'] !== false && isset($data[$name]) && !$id) {
						$data[$name] = $options['default'];
					}
				}
			}
        }
        $this->errors = $errors;
        return empty($errors);
    }
    //protected function distribute(&$data)
    protected function distribute($data)
    {
        $primary = $this->listFields('primary');

        foreach ($data as $field => $value) {
			
            if (!in_array($field, $primary)){
			 
                unset($data[$field]);
            }
        }
		$this->dataNew = $data;
		//return $data; // Added by Kanhaiya feb 06 2014 as call by reference is not supported by php resulting not effect on $data
						// 
    }    

    public function add($data)
    {
        if (!arrays::nonempty($data)) {
            $this->errors['_msg'] = 'empty data';
            return false;
        }
        
        if (!$this->validate($data)) {
            $this->errors['_msg'] = 'not valid';
            return false;
        }
		//$this->distribute($data);
        $this->distribute($data); // modified by kanhaiya @clavax
		$data = $this->dataNew;
		//insert primary data
		$id = $this->Primary->insert($data);

        return $id;
    }
    
    public function edit($id, $data)
    {
        
		
		if (!arrays::nonempty($data)) {
            return false;
        }
        
        if (!is_scalar($id) && !is_array($id)) {
            return false;
        }
        if (!$this->validate($data, $id)) {
            return false;
        }
        
		
		$this->distribute($data);  
		$data = $this->dataNew; // added by kanhaiya @clavax used $this->dataNew to get array instead of  $data
		
		$edited = false;
		
		if(is_array($data) && count($data)>0)
		{
			
			// update primary data
			if (is_scalar($id)) 
			{
				$edited = $this->Primary->update($id, $data);
			} 
			else 
			{
				
				$edited = $this->Primary->update_where($data, SQL::quote('% in (?)', $this->Primary->pk, $id));
			}
		}
        return $edited;
    }    

    public function delete($id)
    {
        if (!is_scalar($id) && !is_array($id)) {
            return false;
        }
        
        if (is_scalar($id)) {
            $this->Primary->delete($id);
        } else {
            $this->Primary->delete_where(SQL::quote('% in (?)', $this->Primary->pk, $id));
        }

        return true;
    }
    
    public function view($id, $fields = '*')
    {
        return $this->select($fields, SQL::quote('m.% = ?', $this->Primary->pk, $id), null, 1);
    }
    
    public function select($fields = '*', $condition = null, $order = null, $paging = null, $grouping = null)
    {
        $this->select_single_field = false;
        $select = $this->prepareSelect($fields, $condition, $order, $paging, $grouping);
        return $this->performSelect($select);
    }

    protected function requires_field(&$fields, $field, $required) 
    {
        if (is_array($fields)) {
            if (in_array($field, $fields) && !in_array('*', $fields) && !in_array($required, $fields)) {
                $fields[] = $required;
            }
        } else {
            if ($fields == $field) {
                $fields = array($fields, $required);
            }
        }
    }

    protected function add_field($fields, $field) 
    {
        if (is_array($fields)) {
            if (!in_array('*', $fields) && !in_array($field, $fields)) {
                $fields[] = $field;
            }
        } else {
            if ($fields != $field) {
                $fields = array($fields, $field);
            }
        }
    }
        
    protected function prepareSelect($fields = '*', $condition = null, $order = null, $paging = null, $grouping = null)
    {
        $Conf = Framework::get_object('Conf');
        
        if (is_scalar($fields)) {
            if ($fields != '*') {
                $this->select_single_field = $fields;
            }
            $fields = array($fields);
        } elseif (is_array($fields) && count($fields) == 1) {
            $key = arrays::first(array_keys($fields));
            $this->select_single_field = is_int($key) ? arrays::first($fields) : $key;
        }
        
        // primary fields
        if (in_array('*', $fields) || in_array('primary.*', $fields)) {
            $select_fields = '*';
        } else {
            $select_fields = $fields;
        }
        if (!$select_fields) {
            $select_fields = $this->Primary->pk;
        }

        $select = SQL::select($select_fields, array('m' => $this->Primary));
        //$select->group($this->Primary->pk); // ???
        
        if (isset($condition)) {
            $select->where($condition);
        }
            
        if (isset($order)) {
            if (is_scalar($order)) {
                if (strpos($order, ':')) {
                    list($order, $dir) = explode(':', $order, 2);
                    $select->order($order, $dir);
                } else {
                    $select->order($order);
                }
            } else if (is_array($order)) {
                foreach ($order as $an_order) {
                    if (strpos($an_order, ':')) {
                        list($an_order, $dir) = explode(':', $an_order, 2);
                        $select->order($an_order, $dir);
                    } else {
                        $select->order($an_order);
                    }
                }
            } else {
                // TODO: unsupported type
            }
        }

        if (isset($paging)) {
            if (strpos($paging, ':')) {
                list($start, $limit) = explode(':', $paging, 2);
                $select->limit($start, $limit);
            } else {
                $limit = $paging;
                $select->limit($paging);
            }
        }

        if (isset($grouping)) {
            $select->group($grouping);
        }

        return $select;
    }
    
    protected function performSelect($select)
    {
        $data = array();
        
        $this->db->query($select);
        $sys_fields = array();
        if (isset($this->Common)) {
            $sys_fields[] = self::FLD_ITEM;
            $sys_fields[] = self::FLD_MODULE;
        }
        if (isset($this->Trans)) {
            $sys_fields[] = self::FLD_LANG;
        }
        if ($select->limit == 1) {
            $this->select_single_row = true;
            if ($row = $this->db->fetch()) {
                if ($sys_fields) {
                    $data = array_diff_key($row, array_combine($sys_fields, $sys_fields));
                } else {
                    $data = $row;
                }
            } else {
                $data = array();
            }
            if ($this->select_single_field) {
                $data = array_get($data, $this->select_single_field, '');
            }
        } else {
            $n = 0;
            while ($row = $this->db->fetch()) {
                $n ++;
                $id = array_get($row, $this->Primary->pk, $n);
                foreach ($row as $field => $value) {
                    if (in_array($field, $sys_fields)) {
                        continue;
                    }
                    if ($this->select_single_field) {
                        $data[$id] = $value;
                    } else {
                        $data[$id][$field] = $value;
                    }
                }
            }
        }

        return $data;
    }
    
    public function count($field, $condition = null)
    {
        return count($this->select($field, $condition));
    }
    
    public function existsWhere($condition)
    {
        return (bool)$this->count($this->Primary->pk, $condition);
    }
    
    public function exists($id)
    {
        return (bool)$this->existsWhere(SQL::quote('% = ?', $this->Primary->pk, $id));
    }
}
?>
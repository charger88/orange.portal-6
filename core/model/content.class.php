<?php

// TODO Add field for protocol (both, HTTP, HTTPS)
// TODO Create tags support
/**
 * Class OPAM_Content
 */
class OPAM_Content extends OPDB_Object {

    /**
     * @var string
     */
    protected static $table = 'content';

    /**
     * @var array
     */
    protected static $schema = array(
        'id'                      => array(0,'ID'),
        'content_type'            => array('','VARCHAR',32),
        'content_title'           => array('','VARCHAR',1024),
        'content_parent_id'       => array(0,'INTEGER'),
        'content_order'           => array(0,'INTEGER'),
        'content_access_groups'   => array(array(),'LIST',256),
        'content_lang'            => array('','VARCHAR',2),
        'content_area'            => array('','VARCHAR',32),
        'content_slug'            => array('','VARCHAR',1024),
        'content_default_lang_id' => array(0,'INTEGER'),
        'content_on_site_mode'    => array(0,'TINYINT'),
        'content_status'          => array(0,'TINYINT'),
        'content_commands'        => array(array(),'ARRAY',8192),
        'content_template'        => array('','VARCHAR',32),
        'content_image'           => array(0,'INTEGER'),
        'content_time_modified'   => array('0000-00-00 00:00:00','TIMESTAMP'),
        'content_time_published'  => array('0000-00-00 00:00:00','TIMESTAMP'),
        'content_user_id'         => array('0','INTEGER'),
	);
    /**
     * @var array
     */
    private $fields = array();

    /**
     * @var array
     */
    protected static $indexes = array('content_type','content_parent_id','content_order','content_lang','content_area','content_slug','content_on_site_mode','content_status','content_time_published','content_user_id');

    /**
     * @var array
     */
    private static $listMoreData = array();

    /**
     * @return int|null
     */
    public function save(){
		if (!strtotime($this->get('content_time_published'))) {
			$this->set('content_time_published', OPDB_Functions::getTime());
		}
        $this->set('content_time_modified', OPDB_Functions::getTime());
		$id = parent::save();
		$type = new OPAM_Content_Type('content_type_code', $this->get('content_type'));
		$field_IDs = array();
		if ($fields = $type->get('content_type_fields')) {
			$this->loadFields();
			foreach($fields as $field_id => $field){
				$fieldObject = OPAM_Content_Field::getObject($id, $field_id);
				$value = isset($this->fields[$field_id]) ? $this->fields[$field_id] : '';
                if (($fieldObject->get('content_field_type') != $field['type']) || ($fieldObject->get('content_field_value') != $value)) {
					$fieldObject->set('content_field_type', $field['type']);
					$fieldObject->set('content_field_value', $value);
					$field_IDs[] = $fieldObject->save();
				} else {
					$field_IDs[] = $fieldObject->id;
				}
			}
		}
		$delete = new OPDB_Select('content_field');
		$delete->addWhere(new OPDB_Clause('content_id', '=', $id));
		if ($field_IDs) {
			$delete->addWhereAnd(new OPDB_Clause('id', 'NOT IN', $field_IDs));
		}
		$delete->execQuery();
		return $id;
	}

    /**
     * @param bool $null
     * @return bool|null
     */
    public function delete($null = false){
        if ($this->id) {
            $delete = new OPDB_Select('content_field');
            $delete->addWhere(new OPDB_Clause('content_id', '=', $this->id));
            $delete->execQuery();
            $delete = new OPDB_Select('content_text');
            $delete->addWhere(new OPDB_Clause('content_id', '=', $this->id));
            $delete->execQuery();
        }
        return parent::delete($null);
    }


    /**
     * @param string $role
     * @return OPAM_Content_Text
     */
    public function text($role = 'text') {
        if ($this->id) {
            $select = new OPDB_Select('content_text');
            $select->addWhere(new OPDB_Clause('content_id', '=', $this->id));
            $select->addWhereAnd();
            $select->addWhere(new OPDB_Clause('content_text_role', '=', $role));
            $text = new OPAM_Content_Text($select->execQuery()->getNext());
            if (!$text->id) {
                $text->set('content_id', $this->id);
                $text->set('content_text_role', $role);
            }
            return $text;
        } else {
            return new OPAM_Content_Text();
        }
	}

    /**
     * @param string $field
     * @param mixed $value
     */
    public function setField($field, $value) {
		$this->fields[$field] = $value;
	}

    /**
     * Load content fields from DB
     */
    protected function loadFields() {
		$select = new OPDB_Select('content_field');
		$select->addWhere(new OPDB_Clause('content_id', '=', $this->id));
		$select->addField('content_field_name');
		$select->addField('content_field_value');
		$this->fields = array_merge($select->execQuery()->getResultArray(true),$this->fields);
	}

    /**
     * @param string $field
     * @return mixed
     */
    public function field($field = '') {
		if (!array_key_exists($field, $this->fields)&& $this->id) {
			$this->loadFields();
		}
		if (!array_key_exists($field, $this->fields)) {
			$this->fields[$field]= null;
		}
		return $this->fields[$field];
	}

    /**
     * @return array
     */
    public static function getListMoreData() {
		return self::$listMoreData;
	}

    /**
     * @param null $default_lang
     * @return string
     */
    public function getSlug($default_lang = null) {
        $slug = '';
        if (!is_null($default_lang) && $this->get('content_lang') && ($this->get('content_lang') != $default_lang)){
            $slug = $this->get('content_lang');
        }
        if ($this->get('content_status') < 7){
            $slug .= '/'.str_replace('%2F', '/', $this->get('content_slug'));
        }
		return trim($slug, '/');
	}

    /**
     * @param null $default_lang
     * @param null $current_lang
     * @return string
     */
    public function getURL($default_lang = null,$current_lang = null) {
        $url = OP_WWW . '/' . $this->getSlug($default_lang);
        if (
            ($this->get('content_lang') || ($current_lang != $default_lang))
            &&
            ($this->get('content_lang') != $current_lang)
        ){
            $url .= '?lang=' . $current_lang;
        }
        return $url;
    }

    /**
     * @param null $type
     * @param null $lang
     * @param null $slug
     * @return mixed
     */
    public static function getContent($type = null, $lang = null, $slug = null) {
		$select = new OPDB_Select(self::$table);
		$select->addWhere(new OPDB_Clause('id', '>', 0));
		if (!is_null($type)) {
			$select->addWhereAnd(new OPDB_Clause('content_type', 'LIKE', $type));
		}
		if (!is_null($lang)) {
			$select->addWhereAnd();
			$select->addWhereBracket(true);
			$select->addWhere(new OPDB_Clause('content_lang', 'LIKE', $lang));
			$select->addWhereOr(new OPDB_Clause('content_lang', 'LIKE', ''));
			$select->addWhereBracket(false);
			$select->setOrder('content_lang', true);
		}
		if (!is_null($slug)) {
			$select->addWhereAnd(new OPDB_Clause('content_slug', 'LIKE', $slug));
		}
		$select->setLimit(1);
		$typeObject = new OPAM_Content_Type('content_type_code', $type);
		$classname = $typeObject->getClass();
		return new $classname($select->execQuery()->getNext());
	}

    /**
     * @param array $params
     * @param string|null $classname
     * @param OPDB_Select|null $select_base
     * @return OPAM_Content[]|array
     */
    public static function getList($params = array(), $classname = null, $select_base = null) {
		$IDs = isset($params['IDs'])? $params['IDs']: null;
		$exclude = isset($params['exclude'])? $params['exclude']: null;
		$types = isset($params['types'])? $params['types']: null;
		$search = isset($params['search'])? $params['search']: null;
		$searchmode = isset($params['searchmode'])? $params['searchmode']: 0;
        $fields = isset($params['fields'])? $params['fields'] : null;
        $fields_not = !empty($params['fields_not']);
		$status_min = isset($params['status_min'])? $params['status_min']: null;
		$status_max = isset($params['status_max'])? $params['status_max']: null;
		$access_user = isset($params['access_user'])? $params['access_user']: null;
		$lang = isset($params['lang'])? $params['lang']: null;
		$on_site_mode = isset($params['on_site_mode'])? $params['on_site_mode']: null;
		$time_published_from = isset($params['time_published_from'])? $params['time_published_from']: null;
		$time_published_to = isset($params['time_published_to'])? $params['time_published_to']: null;
		$user_id = isset($params['user_id'])? $params['user_id']: null;
		$parent_id = isset($params['parent_id'])? $params['parent_id']: null;

		$limit = isset($params['limit'])? $params['limit']: 30;
		$offset = isset($params['offset'])? $params['offset']: 0;
		$order = isset($params['order'])? $params['order']: 'content_time_published';
		$desc = isset($params['desc'])? $params['desc']: false;

		$select = is_null($select_base)? new OPDB_Select(self::$table): $select_base;

		if (!is_null($IDs)&& $IDs) {
			$select->addWhere(new OPDB_Clause('id', 'IN', $IDs));
		} else {
			$select->addWhere(new OPDB_Clause('id', '>', 0));
		}

		if (!is_null($exclude)&& $exclude) {
			$select->addWhereAnd(new OPDB_Clause('id', 'NOT IN', $exclude));
		}

		if (!is_null($types)) {
			$select->addWhereAnd($types ? new OPDB_Clause('content_type', 'IN', is_array($types)? $types : array($types
			)): new OPDB_Clause('id', '=', 0));
		}

        if ($fields){
            $fieldsSelect = new OPDB_Select('content_field');
            $fieldsSelect->addField('content_id');
            $first = true;
            foreach ($fields as $param => $value){
                if ($first){
                    $first = false;
                } else {
                    $fieldsSelect->addWhereOr();
                }
                $fieldsSelect->addWhere(new OPDB_Clause('content_field_name','=',$param));
                $fieldsSelect->addWhereAnd(new OPDB_Clause('content_field_value',strpos($value,'%') !== false ? 'LIKE' : '=',$value));

            }
            $select->addWhereAnd(new OPDB_Clause('id',$fields_not ? 'NOT IN' : 'IN',$fieldsSelect));
        }

		if (!is_null($search)) {
			$select->addWhereAnd(new OPDB_Clause('content_template', 'LIKE', 'main-%'));
			$select->addWhereAnd();
			$select->addWhereBracket(true);
			if (($searchmode == 0) || ($searchmode == 1)) {
				$select->addWhere(new OPDB_Clause('content_title', 'LIKE', '%' . $search . '%'));
			}
			if ($searchmode == 0) {
				$select->addWhereOr();
			}
			if (($searchmode == 0) || ($searchmode == 2)) {
				$textsSelect = new OPDB_Select('content_text');
				$textsSelect->addWhere(new OPDB_Clause('content_text_value', 'LIKE', '%' . $search . '%'));
				if (!is_null($exclude)&& $exclude) {
					$textsSelect->addWhereAnd(new OPDB_Clause('content_id', 'NOT IN', $exclude));
				}
				$textsSelect->addField('content_id');
				$select->addWhere(new OPDB_Clause('id', 'IN', $textsSelect));
			}
			$select->addWhereBracket(false);
		}

		if (!is_null($access_user) && ($access_user instanceof OPAM_User)) {
			$groups = $access_user->get('user_groups');
			$groups[] = 0;
			$f = true;
			$select->addWhereAnd();
			$select->addWhereBracket(true);
			foreach($groups as $n => $group_id){
				if (!$f) {
					$select->addWhereOr();
				} else {
					$f = false;
				}
				$select->addWhere(new OPDB_Clause('content_access_groups', 'LIKE', '%|' . $group_id . '|%'));
			}
			$select->addWhereBracket(false);
		}

		if (!is_null($lang)) {
			$select->addWhereAnd(new OPDB_Clause('content_lang', is_array($lang)? 'IN' : 'LIKE', $lang));
		}

		if (!is_null($on_site_mode)) {
			$select->addWhereAnd($on_site_mode ? new OPDB_Clause('content_on_site_mode', 'IN', $on_site_mode): new OPDB_Clause('id', '=', 0));
		}

		if (!is_null($status_min)) {
			$select->addWhereAnd(new OPDB_Clause('content_status', '>=', $status_min));
		}

		if (!is_null($status_max)) {
			$select->addWhereAnd(new OPDB_Clause('content_status', '<=', $status_max));
		}

		if (!is_null($time_published_from)) {
			$select->addWhereAnd(new OPDB_Clause('content_time_published', '>=', OPDB_Functions::getTime(strtotime($time_published_from))));
		}

		if (!is_null($time_published_to)) {
			$select->addWhereAnd(new OPDB_Clause('content_time_published', '<', OPDB_Functions::getTime(strtotime($time_published_to))));
		}

		if (!is_null($user_id)) {
			$select->addWhereAnd(new OPDB_Clause('content_user_id', 'IN', is_array($user_id)? $user_id : array($user_id)));
		}

		if (!is_null($parent_id)) {
			$select->addWhereAnd(new OPDB_Clause('content_parent_id', 'IN', is_array($parent_id)? $parent_id : array($parent_id)));
		}

		if (!is_null($order)) {
			$select->setOrder($order, $desc);
		}

		if (!is_null($limit)) {
			$select->setLimit($limit, $offset * $limit);
		}

		if (is_array($classname)) {
			foreach($classname as $field){
				$select->addField($field);
			}
		}

		$select->execQuery();

		self::$listMoreData = array(
            'content_image' => array(),
            'content_user_id' => array()
		);
		return is_array($classname)? $select->getResultArray(true): $select->getResultArray(false, is_null($classname)? __CLASS__ : $classname, self::$listMoreData );
	}

    /**
     * @param array $user_groups
     * @return bool
     */
    public function isAllowedForGroups($user_groups) {
		if (!in_array(OPAM_User::GROUP_ADMIN, $user_groups)) {
			$access_groups = $this->get('content_access_groups');
			if (in_array(0, $access_groups)) {
				return true;
			} else {
				if ($user_groups) {
					foreach($user_groups as $group_id){
						if (in_array($group_id, $access_groups)) {
							return true;
						}
					}
				}
				return false;
			}
		} else {
			return true;
		}
	}

    /**
     * @param string $lang
     * @return array
     */
    public function getDefaultLanguageRef($lang) {
		return self::getList(array(
            'types' => $this->get('content_type' ),
            'lang' => $lang
		), array(
            'id',
            'content_title'
		));
	}

    /**
     * @param string|null $th
     * @return string
     */
    public function getImageUrl($th = null) {
		$url = '';
		if ($iid = $this->get('content_image')) {
			$media = new OPMM_System_Media($iid);
			$url = $media->getDir($th).'/'.$media->get('media_file');
		}
		return $url;
	}

    /**
    * @param array $user_groups
    * @return bool
    */
    public function isReadable($user_groups) {
        return ($this->id > 0) && ($this->get('content_status') >= 5) && $this->isAllowedForGroups($user_groups);
    }

    /**
     * @param array $user_groups
     * @return bool
     */
    public function isEditable($user_groups) {
		return ($this->id > 0) && $this->isAllowedForGroups($user_groups);
	}

	// TODO Possible to make some type restrictions
    /**
     * @return bool
     */
    public function isNewAllowed() {
		return (!$this->id);
	}

    /**
     * @param string $default_lang
     * @param OPAM_User|null $user
     * @return array
     */
    public function getLanguagePages($default_lang, $user = null) {
		$links = array();
		$id_def = (!$this->get('content_lang')|| ($this->get('content_lang')== $default_lang)) ? $this->id : $this->get('content_default_lang_id');
		if ($id_def) {
			$select = new OPDB_Select(self::$table);
			$select->addWhereBracket(true);
			$select->addWhereBracket(true);
			$select->addWhere(new OPDB_Clause('content_lang', 'LIKE', $default_lang));
			$select->addWhereAnd(new OPDB_Clause('id', '=', $id_def));
			$select->addWhereBracket(false);
			$select->addWhereOr();
			$select->addWhereBracket(true);
			$select->addWhere(new OPDB_Clause('content_lang', 'LIKE', ''));
			$select->addWhereAnd(new OPDB_Clause('id', '=', $id_def));
			$select->addWhereBracket(false);
			$select->addWhereOr();
			$select->addWhereBracket(true);
			$select->addWhere(new OPDB_Clause('content_lang', 'NOT LIKE', $default_lang));
			$select->addWhereAnd(new OPDB_Clause('content_default_lang_id', '=', $id_def));
			$select->addWhereBracket(false);
			$select->addWhereBracket(false);
			$select->addWhereAnd();
			if ($pages = self::getList(array(
					'access_user' => $user
			), null, $select)) {
				foreach($pages as $page){
					$links[$page->get('content_lang')]= $page->getURL($default_lang);
				}
			}
		} else {
			$links['']= '';
		}
		return $links;
	}

    /**
     * @param int $root
     * @param array $order
     * @param string $group_field
     * @param OPAM_User $access_user
     * @return array
     */
    public static function reorder($root,$order,$group_field,$access_user){
        $updated = array();
        if ($order){
            if ($list = self::getList(array('IDs' => $order,'access_user' => $access_user), __CLASS__)){
                foreach ($order as $ord => $id){
                    if (isset($list[$id])){
                        $item = $list[$id];
                        if ( ($item->get('content_order') != $ord) || ($item->get($group_field) != $root) ){
                            $item->set('content_order',$ord);
                            $item->set($group_field,$root);
                            $item->save();
                            $updated[] = $item->id;
                        }
                    }
                }
            }
        }
        return $updated;
    }

    /**
     * @param OPAM_Content[] $list
     * @return array
     */
    public static function getRssData($list){
        $rss = array();
        if ($list){
            foreach ($list as $item){
                $image = new OPMM_System_Media($item->get('content_image'));
                $rss[] = array(
                    'title' => $item->get('content_title'),
                    'link' => $item->getURL(),
                    'time' => $item->get('content_time_published'),
                    'image_url' => $image->id ? $image->getURL('m') : '',
                    'image_type' => $image->id ? $image->getMimeType() : '',
                );
            }
        }
        return $rss;
    }

}
<?php

/**
 * Class OPAM_Content_Type
 */
class OPAM_Content_Type extends OPDB_Object {

    /**
     * @var string
     */
    protected static $table = 'content_type';

    /**
     * @var array
     */
    protected static $schema = array(
		'id'                            => array(0 ,'ID'),
		'content_type_name'             => array('','VARCHAR',64),
		'content_type_code'             => array('','VARCHAR',32),
		'content_type_status'           => array(0 ,'BOOLEAN'),
		'content_type_type'             => array(0 ,'TINYINT'), // 0 - system, 1 - page, 2 - block, 3 - module, 4 - custom
		'content_type_multilang'        => array(0 ,'BOOLEAN'),
		'content_type_class'            => array('','VARCHAR',32),
		'content_type_hidden'           => array(array(),'ARRAY',2048),
		'content_type_fields'           => array(array(),'ARRAY'),
		'content_type_texts'            => array(array(),'ARRAY',1024),
        'content_type_sitemap_priority' => array(0 ,'TINYINT'),
	);

    /**
     * @var array
     */
    protected static $indexes = array('content_type_status');
    /**
     * @var array
     */
    protected static $uniq = array('content_type_code');

    /**
     * @return string
     */
    public function getClass(){
        if ($this->get('content_type_class')){
            return $this->get('content_type_class');
        } else {
            if ($this->get('content_type_type') == 1){
                return 'OPAM_Page';
            } else if ($this->get('content_type_type') == 2) {
                return 'OPAM_Block';
            } else {
                return 'OPAM_Content';
            }
        }
	}

    /**
     * @param string $output
     * @return array
     */
    public static function getPageTypes($output = 'codes'){
		return self::getTypes(1,null,$output);
	}

    /**
     * @param string $output
     * @return array
     */
    public static function getBlockTypes($output = 'codes'){
		return self::getTypes(2,null,$output);
	}

    /**
     * @param string $output
     * @return array
     */
    public static function getModuleTypes($output = 'codes'){
		return self::getTypes(3,null,$output);
	}

    /**
     * @param string $output
     * @return array
     */
    public static function getCustomTypes($output = 'codes'){
		return self::getTypes(4,null,$output);
	}

    /**
     * @param string $output
     * @return array
     */
    public static function getSearchableTypes($output = 'codes'){
        return self::getTypes(array(1,3,4),null,$output);
    }

    /**
     * @return array
     */
    public static function getTypesForSitemap(){
        $select = new OPDB_Select(self::$table);
        $select->addWhere(new OPDB_Clause('content_type_status', '=', 1));
        $select->addWhereAnd(new OPDB_Clause('content_type_type', 'NOT IN', array(0,2)));
        $select->addWhereAnd(new OPDB_Clause('content_type_code', 'NOT LIKE', 'admin'));
        $select->addField('content_type_code');
        $select->addField('content_type_sitemap_priority');
        return $select->execQuery()->getResultArray(true);
    }

    /**
     * @return array
     */
    public static function getList(){
		$select = new OPDB_Select(self::$table);
		$select->setOrder('id');
		return $select->execQuery()->getResultArray(false,__CLASS__);
	}

    /**
     * @param int|array|null $type
     * @param string|null $type_name
     * @param string $output
     * @return array|OPAM_Content_Type[]
     */
    public static function getTypes($type = null,$type_name = null,$output = 'codes'){
        if ($type && !is_array($type)){
            $type = array($type);
        }
		$select = new OPDB_Select(self::$table);
		$select->addWhere(new OPDB_Clause('content_type_status', '=', 1));
		if (!is_null($type)){
			$select->addWhereAnd(new OPDB_Clause('content_type_type', 'IN', $type));
		}
		if ( !is_null($type_name) && (in_array(3,$type) || in_array(4,$type)) ){
			$select->addWhereAnd(new OPDB_Clause('content_type_code', '=', $type_name));
		}
		if ($output != '*'){
			$select->addField('content_type_code');
			if ($output == 'ref'){
				$select->addField('content_type_name');
			}
			return $select->execQuery()->getResultArray(true);
		} else {
			return $select->execQuery()->getResultArray(false,__CLASS__);
		}
	}
	
}
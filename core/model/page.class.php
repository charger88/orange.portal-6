<?php

/**
 * Class OPAM_Page
 */
class OPAM_Page extends OPAM_Content {

    /**
     * @return int|null
     */
    public function save(){
		if (!$this->get('content_type')){
			$this->set('content_type', 'page');
		}
		if (!$this->id){
			$select = new OPDB_Select(self::$table);
			$select->addWhere(new OPDB_Clause('content_type', 'IN', OPAM_Content_Type::getPageTypes()));
			$select->addField(array('max','content_order'));
			$this->set('content_order',intval($select->execQuery()->getResult()));
		}
		return parent::save();
	}

    /**
     * @return array
     */
    public function getParentsRef(){
		return self::getList(array('types' => array('page'),'exclude' => array($this->id)),array('id','content_title'));
	}

    /**
     * @param $lang
     * @return OPAM_Content
     */
    public static function getHomepage($lang){
		$select = new OPDB_Select(self::$table);
		$select->addWhere(new OPDB_Clause('content_status', '=', 7));
		$select->addWhereAnd(new OPDB_Clause('content_lang', 'IN', array($lang,'')));
		$select->addWhereAnd(new OPDB_Clause('content_type', 'IN', OPAM_Content_Type::getPageTypes()));
		$select->setOrder('content_lang',true);
		return new OPAM_Content($select->execQuery()->getNext());
	}

    /**
     * @param OPAM_User $user
     * @param array|null $types
     * @return array
     */
    public static function getPagesByParents($user,$types = null){
		$grouped = array();
		$pages = self::getList(array(
			'access_user' => $user,
			'types'        => !is_null($types) ? $types : OPAM_Content_Type::getPageTypes(),
		), __CLASS__);
		if ($pages){
			foreach ($pages as $item){
				if (!isset($grouped[$item->get('content_parent_id')])){
					$grouped[$item->get('content_parent_id')] = array();
				}
				$grouped[$item->get('content_parent_id')][] = $item;
			}
		}
		return $grouped;
	}

    /**
     * @param OPAM_User $user
     * @param int $root
     * @return OPAM_Page[]
     */
    public static function getMenu($user,$root = 0){
		$params = array(
			'types' => OPAM_Content_Type::getPageTypes(),
			'access_user' => $user,
			'parent_id' => $root,
		);
		return self::getList($params,__CLASS__);
	}

    //TODO Think about optimization of request
    /**
     * @param OPAM_User $user
     * @param string $lang
     * @param int $root
     * @param int $tree_levels
     * @return array
     */
    public static function getTreeMenu($user,$lang,$root = 0,$tree_levels = 0){
		$params = array(
			'types'       => OPAM_Content_Type::getPageTypes(),
			'access_user' => $user,
			'lang'        => array($lang,''),
		);
		$menu = array();
		$pages = self::getList($params,__CLASS__);
		foreach ($pages as $page){
			if (!isset($menu[$page->get('content_parent_id')])){
				$menu[$page->get('content_parent_id')] = array();
			}
			$menu[$page->get('content_parent_id')][ $page->get('content_default_lang_id') ? $page->get('content_default_lang_id') : $page->id ] = $page;
		}
		return $menu;
	}
	
}
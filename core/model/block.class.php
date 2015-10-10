<?php

/**
 * Class OPAM_Block
 */
class OPAM_Block extends OPAM_Content {

    /**
     * @return int|null
     */
    public function save(){
		if (!$this->get('content_type')){
			$this->set('content_type', 'block');
		}
		return parent::save();
	}

    /**
     * @param array|null $areas
     * @param string|null $lang
     * @param array|null $exclude_areas
     * @param OPAM_Content|null $content
     * @param OPAM_User|null $user
     * @param bool $activeOnly
     * @return array
     */
    public static function getBlocksByAreas($areas = null,$lang = null,$exclude_areas = null,$content = null,$user = null,$activeOnly = false){
		$by_areas = array();
		if (is_null($areas) || !empty($areas)){
			$select = new OPDB_Select(self::$table);
			$select->addWhere(new OPDB_Clause('content_type', 'IN', OPAM_Content_Type::getBlockTypes()));
			$select->setOrder('content_order',false);
			if (!empty($areas)){
				$select->addWhereAnd(new OPDB_Clause('content_area', 'IN', $areas));
			}
			if (!empty($exclude_areas)){
				$select->addWhereAnd(new OPDB_Clause('content_area', 'NOT IN', $exclude_areas));
			}
			if ($activeOnly){
				$select->addWhereAnd(new OPDB_Clause('content_status', '>=', 5));
			}
			if (!is_null($lang)){
				$select->addWhereAnd();
				$select->addWhereBracket(true);
				$select->addWhere(new OPDB_Clause('content_lang', 'LIKE', $lang));
				$select->addWhereOr(new OPDB_Clause('content_lang', 'LIKE', ''));
				$select->addWhereBracket(false);
				$select->addOrder('content_lang',true);
			}
			if (!is_null($content)){
				$page_modes = array(0);
				$page_modes[] = $content->get('content_parent_id') == 0 ? 1 : 2;
				$page_modes[] = $content->get('content_status') == 7 ? 3 : 4;
				$select->addWhereAnd(new OPDB_Clause('content_on_site_mode', 'IN', $page_modes));
			}
			if (!is_null($user)){
				$groups = $user->get('user_groups');
				$groups[] = 0;
				$f = true;
				$select->addWhereAnd();
				$select->addWhereBracket(true);
				foreach ($groups as $n => $group_id){
					if (!$f){
						$select->addWhereOr();
					} else {
						$f = false;
					}
					$select->addWhere(new OPDB_Clause('content_access_groups', 'LIKE', '%|'.$group_id.'|%'));
				}
				$select->addWhereBracket(false);
			}
			$blocks = $select->execQuery()->getResultArray(false,'OPAM_Block');
			$added = array();
			foreach ($blocks as $block){
				$dlID = $block->get('content_default_lang_id') ? $block->get('content_default_lang_id') : $block->id;
				if (!in_array($dlID, $added)){
					if (!isset($by_areas[$block->get('content_area')])){
						$by_areas[$block->get('content_area')] = array();
					}
					$by_areas[$block->get('content_area')][] = $block;
					$added[] = $dlID;
				}
			}
		}
		return $by_areas;
	}

}
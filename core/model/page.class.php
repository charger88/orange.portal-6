<?php

use \Orange\Database\Queries\Parts\Condition;

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
			$select = new \Orange\Database\Queries\Select(self::$table);
			$select
                ->addField(array('max','content_order'))
                ->addWhere(new Condition('content_type', 'IN', OPAM_Content_Type::getPageTypes()))
                ->execute()
            ;
			$this->set('content_order',intval($select->getResultValue()));
		}
		return parent::save();
	}

    /**
     * @return array
     */
    public function getParentsRef(){
		return self::getList(
            [
                'types' => array('page'),
                'exclude' => array($this->id)
            ],
            [
                'id' => 'content_title',
            ]
        );
	}

    /**
     * @param $lang
     * @return OPAM_Content
     */
    public static function getHomepage($lang){
		$select = new \Orange\Database\Queries\Select(self::$table);
		$select
            ->addWhere(new Condition('content_status', '=', 7))
		    ->addWhere(new Condition('content_lang', 'IN', array($lang,'')))
		    ->addWhere(new Condition('content_type', 'IN', OPAM_Content_Type::getPageTypes()))
		    ->setOrder('content_lang',\Orange\Database\Queries\Select::SORT_DESC)
            ->execute()
        ;
		return new OPAM_Content($select->getResultNextRow());
	}

    /**
     * @param OPAM_User $user
     * @param boolean $ignoreOnSiteMode
     * @return array
     */
    public static function getPagesByParents($user,$ignoreOnSiteMode = false){
		$grouped = array();
        $params = array(
            'access_user' => $user,
            'order'       => 'content_order',
            'types'       => OPAM_Content_Type::getPageTypes(),
        );
        if (!$ignoreOnSiteMode) {
            $params['on_site_mode'] = array(2,3);
        }
		$pages = self::getList($params, __CLASS__);
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
            'on_site_mode' => array(1,3),
            'order' => 'content_order',
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
			'types'        => OPAM_Content_Type::getPageTypes(),
			'access_user'  => $user,
			'lang'         => array($lang,''),
            'on_site_mode' => [2,3],
            'order'        => 'content_order',
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
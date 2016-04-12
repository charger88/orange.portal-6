<?php

class OPMC_System_Tags extends OPAL_Controller {
	
	public function indexAction(){
		$return = $this->form();
		if ($tag = $this->getGet('tag')){
			$return .= $this->results($tag);
		}
        return $return;
	}

	public function searchActionDirect($tag){
		return $this->results($tag,$this->getGet('offset',0));
	}

	public function searchAjaxDirect($tag){
		return $this->results($tag,$this->getGet('offset',0));
	}
	
	private function results($tag,$offset = 0){
        $tag = urldecode($tag);
        OPAL_Portal::getInstance()->content->set('content_title', OPAL_Lang::t('SEARCH_BY_TAG_%s',[$tag]));
        $limit = $this->arg('limit', 25);
        $list = OPAM_Page::getList(array(
            'types'       => OPAM_Content_Type::getSearchableTypes(),
            'tag'         => $tag,
            'access_user' => $this->user,
            'limit'       => $limit,
            'offset'      => $offset,
        ), 'OPAM_Page');
        if ($list){
            return $this->templater->fetch('system/tag-result.phtml', array(
                'list'   => $list,
                'limit'  => $limit,
                'offset' => $offset,
            ));
        } else {
            return $this->msg(OPAL_Lang::t('NOTHING_FOUND'), self::STATUS_NOTFOUND);
        }
	}

    public function cloudAction(){
        return $this->cloud();
    }

    public function cloudAjax(){
        return $this->cloud();
    }

    public function cloudBlock(){
        return $this->cloud();
    }

    public function cloud(){
        $tags = OPAM_Content_Tag::getCloudData($this->arg('limit',50));
        list($min, $max, $avg) = OPAM_Content_Tag::tagsStats($tags);
        return $this->templater->fetch('system/'.$this->arg('prefix','default').'-tags-cloud.phtml', array(
            'tags' => $tags,
            'min'  => $min,
            'max'  => $max,
            'avg'  => $avg,
        ));
    }
	
}
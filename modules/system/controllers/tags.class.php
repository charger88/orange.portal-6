<?php

class OPMC_System_Tags extends \Orange\Portal\Core\App\Controller
{

	public function searchActionDirect($tag)
	{
		return $this->results($tag, $this->getGet('offset', 0));
	}

	public function searchAjaxDirect($tag)
	{
		return $this->results($tag, $this->getGet('offset', 0));
	}

	private function results($tag, $offset = 0)
	{
		$tag = urldecode($tag);
		\Orange\Portal\Core\App\Portal::getInstance()->content->set('content_title', \Orange\Portal\Core\App\Lang::t('SEARCH_BY_TAG_%s', [$tag]));
		$limit = $this->arg('limit', 25);
		$list = \Orange\Portal\Core\Model\Page::getList([
			'types' => \Orange\Portal\Core\App\Portal::getInstance()->processHooks('get_searchable_types'),
			'tag' => $tag,
			'access_user' => $this->user,
			'limit' => $limit,
			'offset' => $offset,
		], '\Orange\Portal\Core\Model\Page');
		if ($list) {
			return $this->templater->fetch('system/tag-result.phtml', [
				'list' => $list,
				'limit' => $limit,
				'offset' => $offset,
			]);
		} else {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('NOTHING_FOUND'), self::STATUS_NOTFOUND);
		}
	}

	public function cloudAction()
	{
		return $this->cloud();
	}

	public function cloudAjax()
	{
		return $this->cloud();
	}

	public function cloudBlock()
	{
		return $this->cloud();
	}

	public function cloud()
	{
		$tags = \Orange\Portal\Core\Model\ContentTag::getCloudData($this->arg('limit', 50));
		list($min, $max, $avg) = \Orange\Portal\Core\Model\ContentTag::tagsStats($tags);
		return $this->templater->fetch('system/' . $this->arg('prefix', 'default') . '-tags-cloud.phtml', [
			'tags' => $tags,
			'min' => $min,
			'max' => $max,
			'avg' => $avg,
		]);
	}

}
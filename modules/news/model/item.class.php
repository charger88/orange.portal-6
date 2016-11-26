<?php

/**
 * Class OPMM_News_Item
 */
class OPMM_News_Item extends \Orange\Portal\Core\Model\Content
{

	/**
	 * @return int|null
	 */
	public function save()
	{
		if (!$this->get('content_type')) {
			$this->set('content_type', 'news_item');
		}
		return parent::save();
	}

}
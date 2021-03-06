<?php

class OPMA_System_Types extends \Orange\Portal\Core\App\Controller
{

	protected $list_columns = array(
		'id' => array(),
		'content_type_name' => array('width' => 50, 'link' => '_edit'),
		'content_type_code' => array('width' => 20,),
		'content_type_type' => array('width' => 20,),
		'content_type_status' => array('width' => 10,),
	);

	protected $list_class_fields = array('content_type_status');

	public function indexAction()
	{
		$params = array();
		$params['list'] = \Orange\Portal\Core\Model\ContentType::getList();
		$params['class_fields'] = $this->list_class_fields;
		$params['columns'] = $this->list_columns;
		$params['refs'] = array(
			'content_type_type' => array(
				0 => \Orange\Portal\Core\App\Lang::t('ADMIN_TYPE_TYPE_SYSTEM'),
				1 => \Orange\Portal\Core\App\Lang::t('ADMIN_TYPE_TYPE_PAGE'),
				2 => \Orange\Portal\Core\App\Lang::t('ADMIN_TYPE_TYPE_BLOCK'),
				3 => \Orange\Portal\Core\App\Lang::t('ADMIN_TYPE_TYPE_MODULE'),
				4 => \Orange\Portal\Core\App\Lang::t('ADMIN_TYPE_TYPE_CUSTOM'),
			),
			'content_type_status' => array(
				0 => \Orange\Portal\Core\App\Lang::t('ADMIN_DISABLED'),
				1 => \Orange\Portal\Core\App\Lang::t('ADMIN_ENABLED'),
			),
		);
		$params['columns']['_edit'] = array(
			'title' => '',
			'text' => \Orange\Portal\Core\App\Lang::t('ADMIN_EDIT'),
			'hint' => \Orange\Portal\Core\App\Lang::t('ADMIN_EDIT'),
			'class' => 'icon icon-edit',
			'link' => '/' . $this->content->getSlug() . '/edit/%id%',
		);
		return $this->templater->fetch('system/admin-types-list-wrapper.phtml', array(
			'html' => $this->templater->fetch('system/admin-list.phtml', $params),
			'slug' => $this->content->getSlug(),
		));
	}

	public function newAction()
	{
		return $this->edit(new \Orange\Portal\Core\Model\ContentType());
	}

	public function editAction($id)
	{
		$id = intval($id);
		$item = new \Orange\Portal\Core\Model\ContentType($id);
		if ($item->id) {
			return $this->edit($item);
		} else {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_WARNING_NEW_CONTENT_TYPE'), self::STATUS_WARNING);
		}
	}

	/**
	 * @param \Orange\Portal\Core\Model\ContentType $item
	 * @return string
	 */
	protected function edit($item)
	{
		$form = new OPMX_System_TypeEdit();
		$form->setAction($this->content->getURL() . '/save/' . $item->id);
		$form->setValues($item->getData(), true);
		return $form->getHTML();
	}

	public function saveAction($id = 0)
	{
		$id = intval($id);
		$item = new \Orange\Portal\Core\Model\ContentType($id);
		$form = new OPMX_System_TypeEdit();
		$form->setValues($this->getPostArray());
		$item->setData($form->getValuesWithXSRFCheck());
		$item->save();
		$this->log('CONTENT_TYPE_%s_SAVED', array($item->get('content_type_name')), 'LOG_CONTENT', self::STATUS_OK, $item);
		return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_SAVED'), self::STATUS_OK, $this->content->getURL() . '/edit/' . $item->id);
	}

}
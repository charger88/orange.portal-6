<?php

class OPMA_System_Blocks extends OPMA_System_Content
{

	protected $content_type = 'block';
	protected $allowed_type_type = 2;

	public function indexAction()
	{
		return $this->wrapContentWithTemplate(
			$this->wrapper,
			$this->templater->fetch('system/admin-blocks-list.phtml', array(
				'list' => \Orange\Portal\Core\Model\Block::getBlocksByAreas(null, null, array_keys($this->templater->theme->getAdminAreas()), null, $this->user),
				'areas' => $this->templater->theme->getThemeAreas(),
				'refs' => $this->getFormOptions(),
				'slug' => $this->content->getSlug(),
			))
		);
	}

	public function newAction($type = null)
	{
		$type = new \Orange\Portal\Core\Model\ContentType('content_type_code', $type ? $type : 'block');
		$classname = $type->getClass();
		/** @var \Orange\Portal\Core\Model\Block $item */
		$item = new $classname();
		$item->set('content_type', $type->get('content_type_code'));
		if ($item->isNewAllowed()) {
			$item->set('content_area', $this->getGet('area'));
			$item->set('content_time_published', time());
			$item->set('content_template', 'block-element.phtml');
			$item->set('content_commands', array(array('module' => 'system', 'controller' => 'text', 'method' => 'index', 'static' => true, 'args' => array())));
			$item->set('content_access_groups', array(0));
			return $this->edit($item, $type);
		} else {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_WARNING_NEW_CONTENT'), self::STATUS_WARNING);
		}
	}

	protected function edit($item, $type, $validate = false)
	{
		$this->edit_form_params['lang_overwrite'] = array('content_slug' => \Orange\Portal\Core\App\Lang::t('BLOCK_ID'));
		return parent::edit($item, $type, $validate);
	}

	protected function getFormOptions($item = null)
	{
		$options = parent::getFormOptions($item);
		$options['content_on_site_mode'] = array(
			\Orange\Portal\Core\App\Lang::t('BLOCK_MODE_ALL_PAGES'),
			\Orange\Portal\Core\App\Lang::t('BLOCK_MODE_ROOT_PAGES'),
			\Orange\Portal\Core\App\Lang::t('BLOCK_MODE_NOT_ROOT_PAGES'),
			\Orange\Portal\Core\App\Lang::t('BLOCK_MODE_MAIN_PAGE'),
			\Orange\Portal\Core\App\Lang::t('BLOCK_MODE_NOT_MAIN_PAGE'),
		);
		$options['content_area'] = $this->templater->theme->getThemeAreas();
		return $options;
	}

	public function reorderAjax()
	{
		$updated = \Orange\Portal\Core\Model\Block::reorder($area = $this->getPost('root'), $this->getPost('order'), 'content_area', $this->user);
		return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_CONTENT_REORDERED'), self::STATUS_OK, null, array('IDs' => $updated));
	}

}
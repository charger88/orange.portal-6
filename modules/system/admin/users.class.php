<?php

class OPMA_System_Users extends \Orange\Portal\Core\App\Controller
{

	protected $refs = [];
	protected $content_type = 'content';
	protected $wrapper = 'system/admin-content-wrapper.phtml';
	protected $edit_form_params = [];

	protected $list_columns = [
		'id' => [],
		'user_login' => ['width' => 15, 'link' => '_edit'],
		'user_email' => ['width' => 15],
		'user_name' => ['width' => 15],
		'user_phone' => ['width' => 15],
		'user_groups' => ['width' => 40],
	];

	public function indexAction()
	{
		$params = [];
		$params['groups'] = \Orange\Portal\Core\Model\UserGroup::getRef(true);
		$form = new OPMX_System_UserSearch($params);
		$form->setMethod(\Orange\Forms\Form::METHOD_GET);
		$form->setAction(OP_WWW . '/' . $this->content->getSlug() . '/list');
		return $form->getHTML();
	}

	public function listAction()
	{
		$params = [];
		$params['limit'] = $this->arg('limit', 50);
		$params['order'] = $this->getGet('order', $this->arg('order', 'id'));
		$params['desc'] = (bool)$this->getGet('desc', $this->arg('desc', false));
		$params['offset'] = intval($this->getGet('offset', 0));
		$params['desc'] = false;
		if ($this->getGet('user_search')) {
			$params['filter_login'] = $this->getGet('user_login');
			$params['filter_email'] = $this->getGet('user_email');
			$params['filter_name'] = $this->getGet('user_name');
			$params['filter_phone'] = $this->getGet('user_phone');
			$params['filter_group'] = $this->getGet('user_group');
			$params['filter_status'] = $this->getGet('user_status');
			$params['order'] = $this->getGet('order');
			$params['limit'] = 1000;
			$params['offset'] = null;
		}
		$params['list'] = \Orange\Portal\Core\Model\User::getList($params);
		$params['class_fields'] = ['user_status'];
		$params['columns'] = $this->list_columns;
		$params['refs'] = $this->getFormOptions();
		$params['columns']['_edit'] = [
			'title' => '',
			'text' => \Orange\Portal\Core\App\Lang::t('ADMIN_EDIT'),
			'hint' => \Orange\Portal\Core\App\Lang::t('ADMIN_EDIT'),
			'class' => 'icon icon-edit',
			'link' => '/' . $this->content->getSlug() . '/edit/%id%',
		];
		return $this->templater->fetch('system/admin-users-list-wrapper.phtml', array(
			'html' => $this->templater->fetch('system/admin-list.phtml', $params),
			'slug' => $this->content->getSlug(),
		));
	}

	public function newAction()
	{
		return $this->edit(new \Orange\Portal\Core\Model\User());
	}

	public function editAction($id)
	{
		$id = intval($id);
		$item = new \Orange\Portal\Core\Model\User($id);
		if ($item->id) {
			return $this->edit($item);
		} else {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_WARNING_NEW_USER'), self::STATUS_WARNING);
		}
	}

	/**
	 * @param \Orange\Portal\Core\Model\User $item
	 * @return string
	 */
	protected function edit($item)
	{
		$params = [];
		$params['options'] = $this->getFormOptions();
		$form = new OPMX_System_UserEdit($params);
		$form->setAction(OP_WWW . '/' . $this->content->getSlug() . '/save/' . $item->id);
		$form->setValues($item->getData(), true);
		return $form->getHTML();
	}

	public function saveAction($id = 0)
	{
		$id = intval($id);
		$item = new \Orange\Portal\Core\Model\User($id);
		$form = new OPMX_System_UserEdit();
		$form->setValues($this->getPostArray());
		$item->setData($data = $form->getValuesWithXSRFCheck());
		$groups = $this->getPost('user_groups');
		$item->set('user_groups', $groups);
		if (!empty($data['user_password_new'])) {
			$item->setPassword($data['user_password_new']);
		}
		$item->save();
		$this->log('USER_%s_SAVED', [
			$item->get('user_login')
		], 'LOG_CONTENT', self::STATUS_OK, $item);
		return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_SAVED'), self::STATUS_OK, OP_WWW . '/' . $this->content->getSlug() . '/edit/' . $item->id);
	}

	protected function getFormOptions()
	{
		$options = [
			'user_groups' => \Orange\Portal\Core\Model\UserGroup::getRef(),
		];
		return $options;
	}

}
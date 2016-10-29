<?php

class OPMA_System_Admin extends OPMA_System_Content
{

	protected $content_type = 'admin';
	protected $allowed_type_type = 3;

	protected $list_columns = array(
		'content_title' => array('width' => 50, 'link' => '_edit'),
		'content_access_groups' => array('width' => 30,),
		'content_slug' => array('width' => 10,),
		'content_status' => array('width' => 10,),
	);

	public function newAction($type = null)
	{
		return null;
	}

}
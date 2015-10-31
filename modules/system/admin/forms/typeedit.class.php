<?php

class OPMX_System_TypeEdit extends OPAL_Form {
		
	protected function build($params){
		
		$this->addField('content_type_name', 'text', OPAL_Lang::t('content_type_name'), array());
		$this->addField('content_type_code', 'text', OPAL_Lang::t('content_type_code'), array());
		$this->addField('content_type_type', 'select', OPAL_Lang::t('content_type_type'), array('required' => 'required', 'options' => array(
			0 => OPAL_Lang::t('ADMIN_TYPE_TYPE_SYSTEM'),
			1 => OPAL_Lang::t('ADMIN_TYPE_TYPE_PAGE'),
			2 => OPAL_Lang::t('ADMIN_TYPE_TYPE_BLOCK'),
			3 => OPAL_Lang::t('ADMIN_TYPE_TYPE_MODULE'),
			4 => OPAL_Lang::t('ADMIN_TYPE_TYPE_CUSTOM'),
		)));
        $this->addField('content_type_sitemap_priority', 'text', OPAL_Lang::t('content_type_sitemap_priority'));
        $this->addField('content_type_class', 'text', OPAL_Lang::t('content_type_class'), array());

		$content_type_hidden_options = array(
			'content_title'           => OPAL_Lang::t('content_title'),
			'content_parent_id'       => OPAL_Lang::t('content_parent_id'),
			'content_access_groups'   => OPAL_Lang::t('content_access_groups'),
			'content_lang'            => OPAL_Lang::t('content_lang'),
			'content_area'            => OPAL_Lang::t('content_area'),
			'content_slug'            => OPAL_Lang::t('content_slug'),
			'content_default_lang_id' => OPAL_Lang::t('content_default_lang_id'),
			'content_on_site_mode'    => OPAL_Lang::t('content_on_site_mode'),
			'content_status'          => OPAL_Lang::t('content_status'),
			'content_commands'        => OPAL_Lang::t('content_commands'),
			'content_template'        => OPAL_Lang::t('content_template'),
			'content_image'           => OPAL_Lang::t('content_image'),
			'content_time_published'  => OPAL_Lang::t('content_time_published'),
		);
		
		$this->addField('content_type_hidden', 'select', OPAL_Lang::t('content_type_hidden'), array('options' => $content_type_hidden_options, 'multiple' => 'multiple'));

        $this->addField('content_type_multilang', 'checkbox', OPAL_Lang::t('content_type_multilang'), array('value' => 1));
        $this->addField('content_type_status', 'checkbox', OPAL_Lang::t('content_type_status'), array('value' => 1));
		
		$this->addField('content_type_fields:_', 'text', OPAL_Lang::t('content_type_fields:_'), array(), 'content_type_fields[]');
		$this->addField('content_type_fields:type', 'text', OPAL_Lang::t('content_type_fields:type'), array(), 'content_type_fields[]');
		$this->addField('content_type_fields:group', 'text', OPAL_Lang::t('content_type_fields:group'), array(), 'content_type_fields[]');
		$this->addField('content_type_fields:title', 'text', OPAL_Lang::t('content_type_fields:title'), array(), 'content_type_fields[]');
		$this->addMultirow('content_type_fields');
		
		$this->addField('content_type_texts:_', 'text', OPAL_Lang::t('content_type_texts:_'), array(), 'content_type_texts[]');
		$this->addField('content_type_texts:*', 'text', OPAL_Lang::t('content_type_texts:*'), array(), 'content_type_texts[]');
		$this->addMultirow('content_type_texts');

        $this->addField('type_edit_submit', 'submit', OPAL_Lang::t('ADMIN_SAVE'), array(), 'buttons');
		
	}
		
}
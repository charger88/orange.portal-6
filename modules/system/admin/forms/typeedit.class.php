<?php

use \Orange\Forms\Form;
use \Orange\Forms\Components\Multirow;
use \Orange\Forms\Fields\Selectors\Checkbox;
use \Orange\Forms\Fields\Selectors\Select;
use \Orange\Forms\Fields\Inputs\Text;
use \Orange\Forms\Fields\Buttons\Submit;

class OPMX_System_TypeEdit extends Form {
		
	protected function init($params){

        $this->addField((new Text('content_type_name', OPAL_Lang::t('content_type_name'))));
        $this->addField((new Text('content_type_code', OPAL_Lang::t('content_type_code'))));
		$this->addField((new Select('content_type_type', OPAL_Lang::t('content_type_type')))
            ->requireField()
            ->setOptions([
                0 => OPAL_Lang::t('ADMIN_TYPE_TYPE_SYSTEM'),
                1 => OPAL_Lang::t('ADMIN_TYPE_TYPE_PAGE'),
                2 => OPAL_Lang::t('ADMIN_TYPE_TYPE_BLOCK'),
                3 => OPAL_Lang::t('ADMIN_TYPE_TYPE_MODULE'),
                4 => OPAL_Lang::t('ADMIN_TYPE_TYPE_CUSTOM'),
            ])
        );
        $this->addField((new Text('content_type_sitemap_priority', OPAL_Lang::t('content_type_sitemap_priority'))));
        $this->addField((new Text('content_type_class', OPAL_Lang::t('content_type_class'))));

		$content_type_hidden_options = [
			'content_title'           => OPAL_Lang::t('content_title'),
			'content_parent_id'       => OPAL_Lang::t('content_parent_id'),
            'content_tags'            => OPAL_Lang::t('content_tags'),
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
		];

		$this->addField((new Select('content_type_hidden', OPAL_Lang::t('content_type_hidden')))
            ->setMultiple()
            ->setOptions($content_type_hidden_options));

        $this->addField((new Checkbox('content_type_multilang', OPAL_Lang::t('content_type_multilang')))->setDefault(1));
        $this->addField((new Checkbox('content_type_status', OPAL_Lang::t('content_type_status')))->setDefault(1));

        $content_type_fields = new Multirow('content_type_fields', OPAL_Lang::t('content_type_fields'));
        {
            $content_type_fields->addField((new Text('content_type_fields_id', OPAL_Lang::t('content_type_fields:_')))->setName('id'));
            $content_type_fields->addField((new Text('content_type_fields_type', OPAL_Lang::t('content_type_fields:type')))->setName('type'));
            $content_type_fields->addField((new Text('content_type_fields_group', OPAL_Lang::t('content_type_fields:group')))->setName('group'));
            $content_type_fields->addField((new Text('content_type_fields_title', OPAL_Lang::t('content_type_fields:title')))->setName('title'));
        }
        $this->addField($content_type_fields);

        $content_type_texts = new Multirow('content_type_texts', OPAL_Lang::t('content_type_texts'));
        {
            $content_type_texts->addField((new Text('content_type_texts_id', OPAL_Lang::t('content_type_texts:_')))->setName('id'));
            $content_type_texts->addField((new Text('content_type_texts_value', OPAL_Lang::t('content_type_texts:*')))->setName('value'));
        }
        $this->addField($content_type_texts);

        $this->addField(new Submit('type_edit_submit', OPAL_Lang::t('ADMIN_SAVE')), 'top');

	}

    public function getValues()
    {
        $values = parent::getValues();
        $content_type_texts = $values['content_type_texts'];
        $values['content_type_texts'] = [];
        $first_column = $content_type_texts[key($content_type_texts)];
        foreach ($first_column as $i => $id) {
            if (!empty($id) && !empty($content_type_texts['value'][$i])) {
                $values['content_type_texts'][$id] = $content_type_texts['value'][$i];
            }
        }
        $content_type_fields = $values['content_type_fields'];
        $values['content_type_fields'] = [];
        $first_column = $content_type_fields[key($content_type_fields)];
        foreach ($first_column as $i => $id) {
            if (!empty($id)) {
                $values['content_type_fields'][$id] = [
                    'code' => $id,
                    'type' => $content_type_fields['type'][$i],
                    'group' => $content_type_fields['group'][$i],
                    'title' => $content_type_fields['title'][$i],
                ];
            }
        }
        return $values;
    }

    public function setValues($values, $from_db = false)
    {
        if ($from_db) {
            $content_type_texts = $values['content_type_texts'];
            $values['content_type_texts'] = [];
            $values['content_type_texts']['id'] = [];
            $values['content_type_texts']['type'] = [];
            foreach ($content_type_texts as $i => $row) {
                $values['content_type_texts']['id'][$i] = $i;
                $values['content_type_texts']['value'][$i] = $row;
            }
            $content_type_fields = $values['content_type_fields'];
            $values['content_type_fields'] = [];
            $values['content_type_fields']['id'] = [];
            $values['content_type_fields']['type'] = [];
            $values['content_type_fields']['group'] = [];
            $values['content_type_fields']['title'] = [];
            foreach ($content_type_fields as $i => $row) {
                $values['content_type_fields']['id'][$i] = $i;
                $values['content_type_fields']['type'][$i] = $row['type'];
                $values['content_type_fields']['group'][$i] = $row['group'];
                $values['content_type_fields']['title'][$i] = $row['title'];
            }
        }
        return parent::setValues($values);
    }

}
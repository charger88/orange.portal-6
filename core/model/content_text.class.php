<?php

/**
 * Class OPAM_Content_Text
 */
class OPAM_Content_Text extends OPDB_Object {

    /**
     * @var string
     */
    protected static $table = 'content_text';

    /**
     * @var array
     */
    protected static $schema = array(
		'id'                  => array(0 ,'ID'),
		'content_id'          => array(0 ,'INTEGER'),
		'content_text_role'   => array('','VARCHAR',16),
		'content_text_format' => array(0 ,'TINYINT'),
		'content_text_value'  => array('','LONGTEXT'),
	);

    /**
     * @var array
     */
    protected static $indexes = array('content_id');
    /**
     * @var array
     */
    protected static $uniq = array(array('content_id','content_text_role'));

    public function format(){
        $replaces = array(
            '%%url%%'      => OP_WWW,
            '%%sitename%%' => OPAL_Portal::config('system_sitename'),
        );
        $text = OPAL_Parser::formatText($this->get('content_text_value'),$this->get('content_text_format'));
        foreach ($replaces as $code => $value){
            $text = str_replace($code, $value, $text);
        }
        return $text;
    }

}
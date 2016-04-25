<?php

use \Orange\Database\Queries\Parts\Condition;

class OPMO_Feedback extends OPAL_Module {

    protected $privileges = array(
        'OPMC_Feedback_Main::sendActionDirect' => 'METHOD_FEEDBACK_SEND_MESSAGE',
    );

    protected function doInit(){
        $this->initHooks();
        return true;
    }

    private function initHooks(){
    }

    protected function doInstall($params = array()){
        return empty((new OPMI_Feedback('feedback'))->installModule($params));
    }

    protected function doEnable(){
        return null;
    }

    protected function doUninstall(){
        (new \Orange\Database\Queries\Delete('content'))
            ->addWhere(new Condition('content_slug','=','admin/feedback'))
            ->execute()
        ;
        return null;
    }

    public function getAdminMenu(){
        $adminMenu = array(
            'feedback' => array(
                'name' => 'MODULE_FEEDBACK',
                'url' => '/admin/feedback',
                'icon' => '/modules/feedback/static/icons/feedback.png',
                'order' => 52,
                'sub' => array(
                    'feedback-messages-actual' => array(
                        'name'  => 'MODULE_FEEDBACK_MESSAGES_ACTUAL',
                        'url'   => '/admin/feedback',
                        'icon'  => '/modules/feedback/static/icons/feedback-actual.png',
                        'order' => 10
                    ),
                    'feedback-messages-all' => array(
                        'name'  => 'MODULE_FEEDBACK_MESSAGES_ALL',
                        'url'   => '/admin/feedback/all',
                        'icon'  => '/modules/feedback/static/icons/feedback-all.png',
                        'order' => 20
                    ),
                    'feedback-forms' => array(
                        'name'  => 'MODULE_FEEDBACK_FORMS',
                        'url'   => '/admin/feedback/forms',
                        'icon'  => '/modules/feedback/static/icons/feedback-forms.png',
                        'order' => 30
                    ),
                )
            ),
        );
        return $adminMenu;
    }

}
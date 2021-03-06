<?php

use Orange\Database\Queries\Parts\Condition;

class OPMO_Feedback extends \Orange\Portal\Core\App\Module
{

	protected $privileges = [
		'OPMC_Feedback_Main::sendActionDirect' => 'METHOD_FEEDBACK_SEND_MESSAGE',
		'OPMC_Feedback_Main::sendAjaxDirect' => 'METHOD_FEEDBACK_SEND_MESSAGE',
	];

	protected function doInit()
	{
		return true;
	}

	protected function doInstall($params = [])
	{
		return (new OPMI_Feedback('feedback'))->installModule();
	}

	protected function doEnable()
	{
		return null;
	}

	protected function doUninstall()
	{
		(new \Orange\Database\Queries\Delete('content'))
			->addWhere(new Condition('content_slug', '=', 'admin/feedback'))
			->execute();
		return null;
	}

}
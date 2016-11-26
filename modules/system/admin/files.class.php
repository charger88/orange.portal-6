<?php

class OPMA_System_Files extends \Orange\Portal\Core\App\Controller
{

	public function indexAction()
	{
		return $this->templater->fetch('system/admin-files.phtml', []);
	}

	public function editAction(){
		$filename = $this->getGet('file');
		if ($filename && $this->checkFilepath($this->getPathBase() . $filename)) {
			$file = $this->getFileObject($filename);
			$form = new OPMX_System_FileEdit($this->getFileEditFormParams($file));
			$form->setAction($this->content->getURL() . '/save');
			$form->setValues([
				'file_data' => $this->isFileDataEditable($file) && $file->exists() ? $file->getData() : '',
				'file_name' => $filename,
				'file_name_org' => $filename,
			]);
			return $form->getHTML();
		} else {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_FILEPATH_FAIL'), self::STATUS_ERROR);
		}
	}

	public function saveAction()
	{
		$filename = $this->getPost('file_name_org');
		$file = $this->getFileObject($filename);
		$form = new OPMX_System_FileEdit($this->getFileEditFormParams($file));
		$form->setValues($this->getPostArray());
		if ($form->checkXSRF()) {
			$new_filename = $this->getPost('file_name');
			if (!empty($filename) && !empty($new_filename) && $this->checkFilepath($this->getPathBase() . $filename) && $this->checkFilepath($this->getPathBase() . $new_filename)) {
				try {
					if ($filename !== $new_filename) {
						if ($file->exists()) {
							$file->move($this->getPathBase() . $new_filename);
						} else {
							$file = new \Orange\FS\File($this->getPathBase() . $new_filename);
						}
					}
					if ($this->isFileDataEditable($file)) {
						$file->save($this->getPost('file_data'));
					}
					return $this->redirect($this->content->getURL() . '/edit?file=' . urlencode($new_filename));
				} catch (\Exception $e) {
					return $this->msg($e->getMessage(), self::STATUS_ERROR);
				}
			} else {
				return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_FILEPATH_FAIL'), self::STATUS_ERROR);
			}
		} else {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_XSRF'), self::STATUS_ERROR);
		}
	}

	private function getFileObject($filename){
		try {
			$file = \Orange\FS\FS::open($this->getPathBase() . $filename);
		} catch (\Exception $e){
			$file = new \Orange\FS\File($this->getPathBase() . $filename);
		}
		return $file;
	}

	private function getFileEditFormParams($file){
		return [
			'editable' => $this->isFileDataEditable($file),
			'is_new' => $file->exists(),
		];
	}

	private function isFileDataEditable($file){
		return ($file instanceof \Orange\FS\File) && (!$file->exists() || ($file->getFileSize() < 1048576));
	}

	public function readdirAjax()
	{
		$org_path = $this->getGet('path');
		$path = trim($this->getPathBase() . $org_path, '/');
		if ($this->checkFilepath($path)) {
			$dir = new \Orange\FS\Dir($path);
			if ($dir->exists()) {
				$list = array();
				if ($files = $dir->readDir()) {
					foreach ($files as $file) {
						$mtime = $file->getModifyTime();
						$filesize = $file instanceof \Orange\FS\Dir ? '' : $file->getFileSize();
						$fileData = array(
							'name' => $file->getName(),
							'ext' => $file instanceof \Orange\FS\Dir ? '.' : $file->getExt(),
							'mtime' => date(\Orange\Portal\Core\App\Portal::config('system_time_format', 'Y-m-d H:i:s'), $mtime),
							'mtime_raw' => $mtime,
							'size' => $this->templater->getFilesize($filesize),
							'size_raw' => $filesize,
							'path' => trim($org_path . '/' . $file->getName(), '/'),
						);
						$list[] = $fileData;
					}
				}
				return $this->msg('', self::STATUS_OK, null, array('dir' => $list));
			} else {
				return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_FILES_NOT_DIR'), self::STATUS_WARNING);
			}
		} else {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_FILEPATH_FAIL'), self::STATUS_ERROR);
		}
	}

	public function uploadAjax()
	{
		$path = trim($this->getPathBase() . $this->getPost('path'), '/');
		if ($this->checkFilepath($path)) {
			$files = $this->getFile('uploads');
			if (!empty($files['name'])) {
				$status = true;
				foreach ($files['name'] as $fIndex => $fName) {
					$fName = str_replace(' ', '-', $fName);
					$file = new \Orange\FS\File($path, $fName);
					$status = $status && $file->saveUpload($files['tmp_name'][$fIndex]);
				}
				if ($status) {
					return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_UPLOADED'), self::STATUS_OK);
				} else {
					return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_ERROR'), self::STATUS_WARNING);
				}
			} else {
				return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_EMPTY_REQUEST'), self::STATUS_WARNING);
			}
		} else {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_FILEPATH_FAIL'), self::STATUS_ERROR);
		}
	}

	public function newfolderAjax()
	{
		$path = trim($this->getPathBase() . $this->getPost('path'), '/') . '/' . $this->getPost('folder');
		if ($this->checkFilepath($path)) {
			$dir = new \Orange\FS\Dir($path);
			$dir->create();
			return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_FOLDER_CREATED'), self::STATUS_OK);
		} else {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_FILEPATH_FAIL'), self::STATUS_ERROR);
		}
	}

	public function deleteAjax()
	{
		$path = trim($this->getPathBase() . $this->getPost('file'), '/');
		if ($this->checkFilepath($path)) {
			try {
				\Orange\FS\FS::open($path)->remove();
				return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_FILE_DELETED'), self::STATUS_OK);
			} catch (\Orange\FS\FSException $e) {
				return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_FILEPATH_FAIL'), self::STATUS_ERROR);
			}
		} else {
			return $this->msg(\Orange\Portal\Core\App\Lang::t('ADMIN_FILEPATH_FAIL'), self::STATUS_ERROR);
		}
	}

	private function checkFilepath($path)
	{
		$status = true;
		$path = explode('/', $path);
		foreach ($path as $path_element) {
			if (strlen(trim($path_element, '.')) == 0) {
				$status = false;
			} else if (strpbrk($path_element, "\\/?%*:|\"<>") !== false) {
				$status = false;
			}
		}
		if ($status) {
			$status = ((count($path) >= 3) && ($path[0] == 'sites') && ($path[1] == \Orange\Portal\Core\App\Portal::$sitecode) && ($path[2] == 'static'));
		}
		return $status;
	}

	private function getPathBase(){
		return 'sites/' . \Orange\Portal\Core\App\Portal::$sitecode . '/static/';
	}

}
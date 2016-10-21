<?php

class OPMA_System_Files extends OPAL_Controller {
	
	public function indexAction(){
		return $this->templater->fetch('system/admin-files.phtml', []);
	}

    public function readdirAjax(){
        //TODO XXX Add checkings for path!!!
        $org_path = $this->getGet('path');
        $path = trim('sites/'.OPAL_Portal::$sitecode.'/static/'.$org_path,'/');
        if ($this->checkFilepath($path)){
            $dir = new \Orange\FS\Dir($path);
            if ($dir->exists()){
                $list = array();
                if ($files = $dir->readDir()){
                    foreach ($files as $file) {
                        $mtime = $file->getModifyTime();
                        $filesize = $file instanceof \Orange\FS\Dir ? '' : $file->getFileSize();
                        $fileData = array(
                            'name'      => $file->getName(),
                            'ext'       => $file instanceof \Orange\FS\Dir ? '.' : $file->getExt(),
                            'mtime'     => date(OPAL_Portal::config('system_time_format','Y-m-d H:i:s'),$mtime),
                            'mtime_raw' => $mtime,
                            'size'      => $this->templater->getFilesize($filesize),
                            'size_raw'  => $filesize,
                            'path'      => trim($org_path.'/'.$file->getName(),'/'),
                        );
                        $list[] = $fileData;
                    }
                }
                return $this->msg('',self::STATUS_OK,null,array('dir' => $list));
            } else {
                return $this->msg(OPAL_Lang::t('ADMIN_FILES_NOT_DIR'),self::STATUS_WARNING);
            }
        } else {
            return $this->msg(OPAL_Lang::t('ADMIN_FILEPATH_FAIL'),self::STATUS_ERROR);
        }
    }

    public function uploadAjax(){
        //TODO XXX Add checkings for path!!!
        $path = trim('sites/'.OPAL_Portal::$sitecode.'/static/'.$this->getPost('path'), '/');
        if ($this->checkFilepath($path)){
            $files = $this->getFile('uploads');
            if (!empty($files['name'])){
                $status = true;
                foreach ($files['name'] as $fIndex => $fName) {
                    $fName = str_replace(' ', '-', $fName);
                    $file = new \Orange\FS\File($path, $fName);
                    $status = $status && $file->saveUpload($files['tmp_name'][$fIndex]);
                }
                if ($status) {
                    return $this->msg(OPAL_Lang::t('ADMIN_UPLOADED'), self::STATUS_OK);
                } else {
                    return $this->msg(OPAL_Lang::t('ADMIN_ERROR'), self::STATUS_WARNING);
                }
            } else {
                return $this->msg(OPAL_Lang::t('ADMIN_EMPTY_REQUEST'), self::STATUS_WARNING);
            }
        } else {
            return $this->msg(OPAL_Lang::t('ADMIN_FILEPATH_FAIL'),self::STATUS_ERROR);
        }
    }

    public function newfolderAjax(){
        $path = trim('sites/'.OPAL_Portal::$sitecode.'/static/' . $this->getPost('path'), '/').'/'.$this->getPost('folder');
        if ($this->checkFilepath($path)){
            $dir = new \Orange\FS\Dir($path);
            $dir->create();
            return $this->msg(OPAL_Lang::t('ADMIN_FOLDER_CREATED'),self::STATUS_OK);
        } else {
            return $this->msg(OPAL_Lang::t('ADMIN_FILEPATH_FAIL'),self::STATUS_ERROR);
        }
    }

    public function deleteAjax(){
        $path = trim('sites/'.OPAL_Portal::$sitecode.'/static/'.$this->getPost('file'), '/');
        if ($this->checkFilepath($path)){
            try {
                \Orange\FS\FS::open($path)->remove();
                return $this->msg(OPAL_Lang::t('ADMIN_FILE_DELETED'), self::STATUS_OK);
            } catch (\Orange\FS\FSException $e){
                return $this->msg(OPAL_Lang::t('ADMIN_FILEPATH_FAIL'),self::STATUS_ERROR);
            }
        } else {
            return $this->msg(OPAL_Lang::t('ADMIN_FILEPATH_FAIL'),self::STATUS_ERROR);
        }
    }

    private function checkFilepath($path){
        $status = true;
        $path = explode('/',$path);
        foreach ($path as $path_element){
            if (strlen(trim($path_element,'.')) == 0){
                $status = false;
            }
        }
        if ($status){
            $status = ( (count($path) >= 3) && ($path[0] == 'sites') && ($path[1] == OPAL_Portal::$sitecode) && ($path[2] == 'static') );
        }
        return $status;
    }

}
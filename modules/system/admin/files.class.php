<?php

class OPMA_System_Files extends OPAL_Controller {
	
	public function indexAction(){
		return $this->templater->fetch('system/admin-files.phtml',array(

		));
	}

    public function readdirAjax(){
        //TODO XXX Add checkings for path!!!
        $org_path = $this->getGet('path');
        $path = trim('files'.'/'.$org_path,'/');
        if ($this->checkFilepath($path)){
            $dir = new OPAL_File($path);
            if ($dir->dir){
                $list = array();
                if ($filenames = $dir->dirFiles()){
                    foreach ($filenames as $filename) {
                        $file = new OPAL_File($filename, $path);
                        $mtime = $file->getModifyTime();
                        $filesize = $file->getFileSize();
                        $fileData = array(
                            'name'      => $filename,
                            'ext'       => $file->getExt(),
                            'mtime'     => date(OPAL_Portal::config('system_time_format','Y-m-d H:i:s'),$mtime),
                            'mtime_raw' => $mtime,
                            'size'      => $this->templater->getFilesize($filesize),
                            'size_raw'  => $filesize,
                            'path'      => trim($org_path.'/'.$filename,'/'),
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

        $path = trim('files' . '/' . $this->getPost('path'), '/');
        if ($this->checkFilepath($path)){
            $files = $this->getFile('uploads');
            if (!empty($files['name'])){
                $status = true;
                foreach ($files['name'] as $fIndex => $fName) {
                    $fName = str_replace(' ', '-', $fName);
                    $file = new OPAL_File($fName, $path);
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
        $path = trim('files' . '/' . $this->getPost('path'), '/').'/'.$this->getPost('folder');
        if ($this->checkFilepath($path)){
            $file = new OPAL_File($path);
            $file->makeDir();
            return $this->msg(OPAL_Lang::t('ADMIN_FOLDER_CREATED'),self::STATUS_OK);
        } else {
            return $this->msg(OPAL_Lang::t('ADMIN_FILEPATH_FAIL'),self::STATUS_ERROR);
        }
    }

    public function deleteAjax(){
        $path = trim('files' . '/' . $this->getPost('file'), '/');
        if ($this->checkFilepath($path)){
            $file = new OPAL_File($path);
            $file->delete();
            return $this->msg(OPAL_Lang::t('ADMIN_FILE_DELETED'),self::STATUS_OK);
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
            $status = ( (count($path) >= 1) && ($path[0] == 'files') )
                || ( (count($path) >= 3) && ($path[0] == 'themes') && ($path[2] == 'static') );
        }
        return $status;
    }

}
<?php

//TODO Create functionality for protected files.

class OPMA_System_Media extends OPAL_Controller {

    /*TODO Implement
	public function indexAction(){
		return ':)';
	}
    */
	
	public function indexAjax(){
		$prepared = array();
		if ($list = OPMM_System_Media::getList(
				$this->user->get('user_status') >= 60 ? true : $this->user->id,
				$this->user->get('user_status'),
				$this->getGet('last_id',null),
				$this->getGet('first_id',null)
			)){
			foreach ($list as $media){
				$prepared[] = $this->getArray($media);
			}
		}
		return $this->msg('OK', $prepared ? self::STATUS_OK : self::STATUS_NOTFOUND, null, array('list' => $prepared));
	}
	
	public function oneAjax($id = 0){
		$id = $this->getGet('id',$id);
		$media = OPMM_System_Media::getOne(
			$id,
			$this->user->get('user_status') >= 60 ? true : $this->user->id,
			$this->user->get('user_status')
		);
		if ($media->id){
			return $this->msg('OK', self::STATUS_OK, null, array('one' => $this->getArray($media)));
		} else {
			return $this->msg(OPAL_Lang::t('NOT_FOUND'), self::STATUS_NOTFOUND);
		}
	}

	public function uploadAction(){
	}
	
	public function uploadAjax(){
		$ids = array();
		$files = $this->getFile('uploads');
		foreach ($files['name'] as $fIndex => $fName){
			$media = new OPMM_System_Media();
			$id = $media->create($fName, null, $files['tmp_name'][$fIndex], $this->user->id);
			if ($id > 0){
				$ids[] = $id;
				$this->log('MEDIA_SAVED', array(), 'LOG_FILES', self::STATUS_OK, $media);
			} else {
				$message = ($id == -1)
					? 'MEDIA_BAD_FILENANE'
					: 'MEDIA_SAVING_FAILED'
				;
				$this->log($message, array(), 'LOG_FILES', self::STATUS_ERROR);
			}
		}
		return $this->msg(OPAL_Lang::t('Uploaded'), self::STATUS_OK, null, array('ids' => $ids));
	}

    /**
     * @param OPMM_System_Media $media
     * @return string
     */
	protected function getArray($media){
		return array(
			'id' => $media->id,
			'name' => $media->get('media_name'),
			'image' => $media->get('media_thumbnails') ? OP_WWW.'/'.$media->getDir('s').'/'.$media->get('media_file') : '',
		);
	}
	
}
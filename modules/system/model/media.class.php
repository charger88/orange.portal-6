<?php

use \Orange\Database\Queries\Parts\Condition;

class OPMM_System_Media extends \Orange\Database\ActiveRecord {
	
	protected static $table = 'media';
	
	protected static $scheme = array(
		'id'                   => array('type' => 'ID'),
		'media_name'           => array('type' => 'STRING', 'length' => 512),
		'media_size'           => array('type' => 'BIGINT'),
		'media_file'           => array('type' => 'STRING', 'length' => 512),
		'media_hidden_in_list' => array('type' => 'BOOLEAN'),
		'media_protected'      => array('type' => 'BOOLEAN'),
		'media_thumbnails'     => array('type' => 'BOOLEAN'),
		'media_access_level'   => array('type' => 'TINYINT'),
		'media_time_uploaded'  => array('type' => 'TIME'),
		'media_user_id'        => array('type' => 'INTEGER'),
	);
	
	protected static $keys = array('media_hidden_in_list','media_time_uploaded');
	
	public static $th_sizes = array(
		'l' => array(960,null,null,70),
		'm' => array(480,320,false,70),
		's' => array(240,160,false,70),
	);
	
	public function getDir($th = null){
		$path = 'files/';
		$path .= $this->get('media_protected') ? 'protected' : 'media';
		$path .= '/';
		$path .= ( $th && isset(self::$th_sizes[$th]) ) ? 'th/'.$th : 'org';
		$path .= '/';
		$path .= date('Y/m/d',strtotime($this->get('media_time_uploaded')));
		return $path;
	}

    public function getURL($th = null){
        return OP_WWW.'/'.$this->getDir($th).'/'.$this->get('media_file');
    }

    public function getMimeType(){
        $ext = explode('.',$this->get('media_file'));
        $ext = strtolower(array_pop($ext));
        switch ($ext){
            case 'jpg':
            case 'jpg':
                $type = 'image/jpeg';
                break;
            case 'png':
                $type = 'image/png';
                break;
            case 'gif':
                $type = 'image/gif';
                break;
            default:
                $type = '';
                break;
        }
        return $type;
    }
	
	public function create($org_name,$data,$tmp_name,$user_id,$params = array()){

		$return = 0;
		
		if ($params){
			$this->setData($params);
		}
		
		$this->set('media_name',$org_name);
		$org_name = str_replace(' ','-',$org_name);
		$this->set('media_file',$org_name);
		$this->set('media_time_uploaded',time());
		$this->set('media_user_id',$user_id);
		
		$file = new OPAL_File();
		$dir = $this->getDir();
		
		$tNum = 0;
		do {
			if ($file->set($filename = $this->getNameWithNumber($tNum), $dir)){
				$tNum++;
			} else {
				$return = -1;
			}
		} while ( $file->is_exists() && ($return == 0) );
		
		$this->set('media_file',$filename);
		
		if ($return == 0){

            $status = !is_null($tmp_name) ? $file->saveUpload($tmp_name) : $file->saveData($data);

			$this->set('media_size',$file->getFileSize());
			
			if ($status){
				$this->generateThumbnails();
                $return = $this->save()->id;
            } else {
				$return = -2;
			}
		}
		return $return;
	}
	
	private function getNameWithNumber($tNum){
		if ($tNum){
			$name = $this->get('media_file');
			strrpos($name,'.');
			if ($sp = strrpos($name,'.')){ //It is a feature, not a bug with (0 !== false)
				return substr($name,0,$sp) . '_' . $tNum . substr($name,$sp);
			} else {
				return $name . '_' . $tNum;
			}
		} else {
			return $this->get('media_file');
		}
	}
	
	public function generateThumbnails(){
		$result = false;
		if ($this->get('media_size')){
			$image = new OPAL_Image($this->getDir().'/'.$this->get('media_file'));
			if ($image->getType()){
				$result = true;
				foreach (self::$th_sizes as $size => $info){
					$th = clone $image;
					if (is_null($info[2])){
						$th->resize($info[0],$info[1]);
					} else {
						$th->rectangle($info[0],$info[1],$info[2]);
					}
					$dirname = $this->getDir($size);
					$dir = new OPAL_File($dirname);
					if (!$dir->dir){
						$dir->makeDir();
					}
					$tresult = $th->save($dirname.'/'.$this->get('media_file'),null,$info[3],true);
					$result = $result && $tresult;
				}
			}
		}
		$this->set('media_thumbnails', $result);
		return $result;
	}
	
	public static function getOne($id,$user_id,$user_status = 0){
		$select = new \Orange\Database\Queries\Select(self::$table);
		$select->addWhere(new Condition('id','=',$id));
		$select->addWhere(new Condition('media_access_level','<=',$user_status));
		if ($user_id !== true){
			$select->addWhere(new Condition('media_user_id','=',$user_id));
		}
		$select->setLimit(1);
		return new OPMM_System_Media($select->execute()->getResultNextRow());
	}
	
	public static function getList($user_id,$user_status = 0,$last_id = null,$first_id = null,$limit = 30){
		$select = new \Orange\Database\Queries\Select(self::$table);
		$select->addWhere(new Condition('media_access_level','<=',$user_status));
		if ($user_id !== true){
			$select->addWhere(new Condition('media_user_id','=',$user_id));
		}
		if ($last_id){
			$select->addWhere(new Condition('id','<',$last_id));
		}
		if ($first_id){
			$select->addWhere(new Condition('id','>',$first_id));
		}
		$select->setOrder('media_time_uploaded',$first_id ? false : true);
		$select->setLimit($limit);
		return $select->execute()->getResultArray(false,__CLASS__);
	}
	
}
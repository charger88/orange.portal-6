<?php

class OPAL_File {
	
	private $path;
	private $name;
	
	private $fullname;
	
	public $file = false;
	public $dir = false;
	
	public function __construct($filename = null,$filepath = null){
		if (!is_null($filename)){
			$this->set($filename,$filepath);
		}
	}
	
	public function set($filename,$filepath){
		$filename = !is_null($filename) ? trim($filename,"/\\") : '';
		$filepath = !is_null($filepath) ? trim($filepath,"/\\") : '';
		if ((strpos($filename,'/') !== false) && !$filepath){
			$path = explode('/',$filename);
			$count = count($path) - 1;
			$filepath = '';
			foreach ($path as $i => $pathElement) {
				if ($i < $count){
					$filepath .= ($i > 0) ? '/'.$pathElement : $pathElement;
				} else {
					$filename = $pathElement;
				}
			}
		}
		if (self::checkFilename($filename) && self::checkFilepath($filepath)){
			$this->path = $filepath;
			$this->name = $filename;
			$this->fullname = ($this->path ? $this->path . '/' : '') . $this->name;
			$this->file = is_file(OP_SYS_ROOT.$this->fullname);
			$this->dir = $this->file ? false : is_dir(OP_SYS_ROOT.$this->fullname);
			return $this;
		} else {
			return null;
		}
	}
	
	public function is_exists(){
		return ($this->file || $this->dir);
	}
	
	public function getFullname(){
		return $this->fullname;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function getPath(){
		return $this->path;
	}
	
	public function makeDir(){
		if ($this->fullname && !$this->file && !$this->dir){
			$tree = explode('/',trim($this->fullname,'/'));
			$path = '';
			foreach ($tree as $i => $t_dir){
				$path .= $i ? '/'.$t_dir : $t_dir;
				if (!is_dir(OP_SYS_ROOT.$path)){
					$this->dir = mkdir(OP_SYS_ROOT.$path);
				}
			}
			return $this->dir;
		} else {
			return null;
		}
	}
	
	public function delete(){
		if ($this->file){
			if (unlink(OP_SYS_ROOT.$this->fullname)){
				$this->reset();
				return true;
			} else {
				return false;
			}
		} elseif ($this->dir) {
			$files = self::dirFiles();
			if ($files){
				foreach ($files as $file){
					$file = new OPAL_File($file,$this->fullname);
					$file->delete();
				}
			}
			if (rmdir(OP_SYS_ROOT.$this->fullname)){
				$this->reset();
				return true;
			} else {
				return false;
			}
		} else {
			return null;
		}
	}
	
	public function getModifyTime(){
		if ($this->is_exists()){
			return filemtime(OP_SYS_ROOT.$this->fullname);
		} else {
			return null;
		}
	}
	
	public function getFileSize(){
		if ($this->file){
			return filesize(OP_SYS_ROOT.$this->fullname);
		} else {
			return null;
		}
	}
	
	public function getDirCountAndSize(){
		if ($this->dir){
			return $this->getDirFilesCountAndSize($this);
		} else {
			return array(null,null);
		}
	}
	
	private function getDirFilesCountAndSize($dir){
		$count = $size = 0;
		$dir = $dir instanceof OPAL_File ? $dir : new OPAL_File($dir);
		if ($dir->dir){
			$dirfiles = $dir->dirFiles();
			foreach ($dirfiles as $file){
				$file = new OPAL_File($file,$dir->getFullname());
				if ($file->dir){
					list($fCount,$fSize) = $this->getDirFilesCountAndSize($file);
					$count += $fCount;
					$size += $fSize;
				} else {
					$count++;
					$size += $file->getFileSize();
				}
			}
		}
		return array($count,$size);
	}
	
	public function rename($newname){
		$test = new OPAL_File($newname,$this->path);
		if (self::checkFilename($test->getName()) && self::checkFilepath($test->getPath())){
			if ( rename( OP_SYS_ROOT.$this->getFullname(), OP_SYS_ROOT.$test->getFullname() ) ){
				return $this->set($test->getName(), $test->getPath());
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	public function dirFiles(){
		$files = array();
		if ($this->dir){
			$dir = opendir(OP_SYS_ROOT.$this->fullname);
	        while (false !== ($file = readdir($dir))) {
	        	if(OPAL_File::checkFilename($file)){
	        		$files[] = $file;
	        	}		        		
	        }
	   		closedir($dir);
		}
   		return $files;
	}
	
	public function getExt(){
		if (!$this->dir){
			if ($this->getName()){
				$fname = explode('.', $this->getName());
				return $fname[count($fname)-1];
			} else {
				return '';
			}
		} else {
			return '.';
		}
	}
	
	public function getData(){
		if ($this->file){
			$filesize = $this->getFileSize();
			if ($filesize){
				$file = fopen(OP_SYS_ROOT.$this->fullname, 'r');
				$data = fread($file, $filesize);
				fclose($file);
				return $data;
			} else {
				return '';
			}
		} else {
			return null;
		}
	}
	
	public function saveData($data, $add = false){
		if (!$this->dir){
			$dir = new OPAL_File($this->path);
			if (!$dir->is_exists()){
				$dir->makeDir();
			}
			if (is_writable(OP_SYS_ROOT.$this->fullname) || !file_exists(OP_SYS_ROOT.$this->fullname)){
				$file = fopen(OP_SYS_ROOT.$this->fullname, $add ? 'a' : 'w' );
				$result = fwrite($file, $data);
				fclose($file);
				return $result;
			} else {
				return false;
			}
		} else {
			return null;
		}
	}
	
	public function saveUpload($tmp_name){
		$dir = new OPAL_File($this->path);
		if (!$dir->is_exists()){
			$dir->makeDir();
		}
		if (move_uploaded_file($tmp_name,OP_SYS_ROOT.$this->getFullname())){
			$this->file = true;
			chmod($this->getFullname(),0644);
			return true;
		} else {
			return false;
		}
	}
	
	public function copy($newname = null,$newpath = null){
		$newname = $newname ? $newname : $this->name;
		$newpath = !is_null($newpath) ? $newpath : $this->path;
		if (self::checkFilename($newname) && self::checkFilepath($newpath)){
			copy($this->getFullname(),($newpath ? $newpath . '/' : '') . $newname);
		}
	}
	
	protected function reset(){
		$this->path = null;
		$this->name = null;
		$this->fullname = null;
		$this->file = false;
		$this->dir = false;
	}
	
	public static function checkFilename($filename){
		if ((strlen($filename) == 0)||(strlen($filename) > 256)){
			$status = false;
		} else {
			if (!( 
				((strpos($filename,'/') === false)) &&
				((strpos($filename,'\\') === false)) && 
				((strpos($filename,'*') === false)) && 
				((strpos($filename,':') === false)) && 
				((strpos($filename,'?') === false)) &&  
				((strpos($filename,'"') === false)) &&  
				((strpos($filename,'>') === false)) &&  
				((strpos($filename,'<') === false)) &&  
				((strpos($filename,'|') === false))
			)) {
		    	$status = false;
			} else {
				if (in_array($filename,array('.','..'))) {
		   	 		$status = false;
				} else {
			   		$status = true;
				}
			}
		}
		return $status;
	}
	
	public static function checkFilepath($filepath){
		if ($filepath){
			$path = explode('/',$filepath);
			foreach ($path as $dir) {
				if (!self::checkFilename($dir)){
					return false;
				}
			}
			return true;
		} else {
			return true;
		}
	}
		
}
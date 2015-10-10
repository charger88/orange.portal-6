<?php

class OPAL_Image {

	private $source;
	private $filepath;
	private $height;
	private $width;
	private $type;
		
	public $defaultBGColor = null;
	
	public function __construct($filepath = null,$width = null,$height = null){
		$this->filepath = OP_SYS_ROOT.$filepath;
		if (is_null($this->filepath) || !is_file($this->filepath)){
			$this->width = intval($width);
			$this->height = intval($height);
			$this->type = null;
		} else {
			$imagesize = getimagesize($this->filepath);
			$this->width = intval($imagesize[0]);
			$this->height = intval($imagesize[1]);
			$this->type = image_type_to_mime_type($imagesize[2]);
		}
		$this->create();
	}
	
	public function getWidth(){
		return $this->width;
	}
	
	public function getHeight(){
		return $this->height;
	}
	
	public function getFilepath(){
		return $this->filepath;
	}
	
	public function echoImage(){
		if ($this->getType()){
			header('Content-type: '.$this->getType(false));
			echo file_get_contents($this->filepath);
			flush();
			return true;
		} else {
			return false;
		}
	}
	
	public function getType($ext = true){
		if ($this->type == 'image/png') {
			return $ext ? 'png' : $this->type;
		} elseif ($this->type == 'image/jpeg') {
			return $ext ? 'jpg' : $this->type;
		} elseif ($this->type == 'image/gif') {
			return $ext ? 'gif' : $this->type;
		} else {
			return false;
		}
	}
	
	private function create(){
		if ($this->type == 'image/png') {
			$this->source = imagecreatefrompng($this->filepath);
			imagealphablending($this->source, false);
			imagesavealpha($this->source, true);
		} elseif ($this->type == 'image/jpeg') {
			$this->source = imagecreatefromjpeg($this->filepath);
		} elseif ($this->type == 'image/gif') {
			$this->source = imagecreatefromgif($this->filepath);
		} else {
			if (($this->width > 0) && ($this->height > 0)){
				$this->source = $this->getEmptyImage($this->width, $this->height);
			} else {
				$this->source = false;
			}
		}
	}
	
	public function save($path = null,$type = null,$qualityForJpeg = 100,$setNewName = false){
		
		if ($this->source){
		
			if (is_null($type)){
				$type = $this->type;
			}
			
			if (is_null($path)){
				$path = $this->filepath;
			} else {
				$path = OP_SYS_ROOT.$path;
				if ($setNewName){
					$this->filepath = $path;
				}
			}
			
			if ($type == 'image/png') {
				return imagepng($this->source,$path);
			} elseif ($type == 'image/jpeg') {
				return imagejpeg($this->source,$path,intval($qualityForJpeg));
			} elseif ($type == 'image/gif') {
				return imagegif($this->source,$path);
			} else {
				return imagepng($this->source,$path);
			}
		
		} else {
			return false;
		}
		
	}
	
	private function getEmptyImage($width,$height,$bg = false){
		$tmp = imagecreatetruecolor($width,$height);
		if (($this->type == 'image/png') && !$bg){
			imagealphablending($tmp, false);
			imagesavealpha($tmp, true);
		}
		if ($this->defaultBGColor){
			if (count($this->defaultBGColor) == 3){
				$color = imagecolorallocate($tmp, $this->defaultBGColor[0], $this->defaultBGColor[1], $this->defaultBGColor[2]);
			} else if (count($this->defaultBGColor) == 4) {
				$color = imagecolorallocatealpha($tmp, $this->defaultBGColor[0], $this->defaultBGColor[1], $this->defaultBGColor[2], $this->defaultBGColor[3]);
			} else {
				$color = imagecolorallocate($tmp, 255, 255, 255);
			}
		} else {
			$color = ($this->type == 'image/png')
				? imagecolorallocatealpha($tmp, 255, 255, 255, 127)
				: imagecolorallocate($tmp, 255, 255, 255)
			;
		}
		imagefill($tmp, 0, 0, $color);
		return $tmp;
	}
	
	public function square($size,$contain = false){
		return $this->rectangle($size, $size, $contain);
	}
	
	public function rectangle($width,$height,$contain = false){
		if ($this->source && $this->width && $this->height){
			$ratio_base = $this->width / $this->height;
			$ratio_new  = $width / $height;
			if ($ratio_base > $ratio_new){
				if ($contain){
					$this->resize($width, round($width / $ratio_base));
				} else {
					$this->resize(round($ratio_base * $height), $height);
				}
			} else {
				if ($contain){
					$this->resize($ratio_base * $height, $height);
				} else {
					$this->resize($width, round($width / $ratio_base));
				}
			}
			$this->crop($width,$height,0.5,0.5,true);
			return true;
		} else {
			return false;
		}
	}
	
	public function resize($width = null,$height = null){
		if ($this->source){
			if (is_null($width)){
				if (is_null($height)){
					$width = $this->width;
					$height = $this->height;
				} else {
					$width = round($this->width / $this->height * $height);
				}
			} else {
				if (is_null($height)){
					$height = round($this->height / $this->width * $width);
				}
			}
			$tmp = $this->getEmptyImage($width, $height);
			imagecopyresampled($tmp,$this->source,0,0,0,0,$width,$height,$this->width,$this->height);
			$this->source = $tmp;
			$this->width = round($width);
			$this->height = round($height);
			return true;
		} else {
			return false;
		}
	}
	
	public function crop($width = null,$height = null,$offset_x = 0,$offset_y = 0,$percent_mode = false){
		if ($this->source){
			if (is_null($width)){
				if (is_null($height)){
					$width = $this->width;
					$height = $this->height;
				} else {
					$width = round($this->width / $this->height * $height);
				}
			} else {
				if (is_null($height)){
					$height = round($this->height / $this->width * $width);
				}
			}
			$tmp = $this->getEmptyImage($width, $height, true);
			$ratio_base = $this->width / $this->height;
			$ratio_new  = $width / $height;
			if ($ratio_base < $ratio_new){
				$width2 = round($this->height * $ratio_base);
				$height2 = $height;
			} else {
				$width2 = $width;
				$height2 = round($this->width / $ratio_base);
			}

			if ($percent_mode){
				$offset_x = round( ($width - $this->width) * $offset_x);
				$offset_y = round( ($height - $this->height) * $offset_y);
			}

			//die($width2.'x'.$height2.'|'.$width.'x'.$height.'|'.$this->width.'x'.$this->height.'|'.$offset_x.':'.$offset_y);
			
			imagecopyresampled(
				$tmp,$this->source,
				
				$offset_x > 0 ? abs($offset_x) : 0,
				$offset_y > 0 ? abs($offset_y) : 0,
				
				$offset_x < 0 ? abs($offset_x) : 0,
				$offset_y < 0 ? abs($offset_y) : 0,
				
				min($this->width,$width2),min($this->height,$height2),min($this->width,$width2),min($this->height,$height2)
			);
			$this->source = $tmp;
			$this->width = $width;
			$this->height = $height;
			return true;
		} else {
			return false;
		}
	}

	public function setBGToImage(){
		$bgImage = $this->getEmptyImage($this->width, $this->height, true);
		imagecopy($bgImage,$this->source,0,0,0,0,$this->width,$this->height);
		$this->source = $bgImage;
	}
	
}
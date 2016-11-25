<?php

class OPAL_Nocache implements OPAL_CacheInterface
{

	public function __construct($config = array()){
	}

	public function get($key){
		return null;
	}

	public function set($key, $data, $period = 3600){
		return true;
	}

	public function remove($key, $not_exact){
		return true;
	}

	public function reset(){
		return true;
	}

	public function clean(){
		return true;
	}

}
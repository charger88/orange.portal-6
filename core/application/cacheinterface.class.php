<?php

interface OPAL_CacheInterface
{

	public function __construct($config);

	public function get($key);

	public function set($key, $data, $period);

	public function remove($key, $not_exact);

	public function reset();

	public function clean();

}
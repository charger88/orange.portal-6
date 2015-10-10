<?php

/*
 * OPDB - Orange.Portal DataBase
 * OPAM - Orange.Portal Application Model
 * OPAC - Orange.Portal Application Controller
 * OPAL - Orange.Portal Application Logic
 * OPLC - Orange.Portal Library Class
 * OPAA - Orange.Portal Application Admin
 * OPML - Orange.Portal Module Controller
 * OPMA - Orange.Portal Module Admin
 * OPMM - Orange.Portal Module Model
 */

	function __autoload($class_name) {
		
		$prefix = substr($class_name,0,4);
		$classname = strtolower(substr($class_name,5));
		$filename = '';
		
		if ($prefix{0}.$prefix{1} == 'OP'){
			if ($prefix{2}.$prefix{3} == 'DB'){
				$filename = 'core/opdb/'.$classname.'.class.php';
				if (!is_file(OP_SYS_ROOT.$filename)) {
					$filename = 'core/opdb/'.OPDB_Config::$DBType.'/'.$classname.'.class.php';
					//TODO Make something with database - it is not so beautiful
				}
			} elseif ($prefix{2}.$prefix{3} == 'AL') {
				$filename = 'core/application/'.$classname.'.class.php';
			//} elseif ($prefix{2}.$prefix{3} == 'AC') {
			//	$filename = 'core/controllers/'.$classname.'.class.php';
			//} elseif ($prefix{2}.$prefix{3} == 'CF') {
			//	$filename = 'core/controllers/forms/'.$classname.'.class.php';
			//} elseif ($prefix{2}.$prefix{3} == 'AA') {
			//	$filename = 'core/admin/'.$classname.'.class.php';
			//} elseif ($prefix{2}.$prefix{3} == 'AF') {
			//	$filename = 'core/admin/forms/'.$classname.'.class.php';
			} elseif ($prefix{2}.$prefix{3} == 'AM') {
				$filename = 'core/model/'.$classname.'.class.php';
			} elseif ($prefix{2}.$prefix{3} == 'LC') {
				$filename = 'core/libs/'.$classname.'.class.php';
			} elseif ($prefix{2}.$prefix{3} == 'TF') {
				$filename = 'themes/'.$classname.'/'.$classname.'.class.php';
			} elseif ($prefix{2} == 'M') {
				
				$separator = strpos($classname,'_');
				
				if ($separator > 0){
					$modname = substr($classname,0,$separator);
					$classname = substr($classname,$separator+1);
				} else {
					$modname = $classname;
				}
				
				if ($prefix{3} == 'C') {
					$filename = 'modules/'.$modname.'/controllers/'.$classname.'.class.php';
				} elseif ($prefix{3} == 'F') {
					$filename = 'modules/'.$modname.'/controllers/forms/'.$classname.'.class.php';
				} elseif ($prefix{3} == 'M') {
					$filename = 'modules/'.$modname.'/model/'.$classname.'.class.php';
				} elseif ($prefix{3} == 'O') {
					$filename = 'modules/'.$modname.'/'.$classname.'.class.php';
				} elseif ($prefix{3} == 'L') {
					$filename = 'modules/'.$modname.'/libs/'.$classname.'.class.php';
				} elseif ($prefix{3} == 'A') {
					$filename = 'modules/'.$modname.'/admin/'.$classname.'.class.php';
				} elseif ($prefix{3} == 'X') {
					$filename = 'modules/'.$modname.'/admin/forms/'.$classname.'.class.php';
				} elseif ($prefix{3} == 'I') {
					$filename = 'modules/'.$modname.'/installer.class.php';
				} else {
					
				}
				
			} else {
				
			}
			
			if ($filename && is_file(OP_SYS_ROOT.$filename)){
				require_once OP_SYS_ROOT.$filename;
			} else {
				if (is_file($filename = OP_SYS_ROOT.'core/libs/'.$classname.'.class.php')){
					require_once $filename;
				} else {
					// Here will be thrown Fatal error.
				}
			}
			
		}
		
	}

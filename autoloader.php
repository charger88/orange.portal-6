<?php

if (is_file(OP_SYS_ROOT . 'vendor/autoload.php')) {
	require_once OP_SYS_ROOT . 'vendor/autoload.php';
} else {
	die('Vendor modules was not installed.');
}

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

spl_autoload_register(
	function ($orgclassname) {
		if ((strpos($orgclassname, 'OP') === 0) && (($plen = strpos($orgclassname, '_')) !== false)) {
			$classname = strtolower(substr($orgclassname, $plen + 1));
			if (strpos($orgclassname, 'AL') === 2) {
				$filename = 'core/application/' . $classname . '.class.php';
			} elseif (strpos($orgclassname, 'AM') === 2) {
				$filename = 'core/model/' . $classname . '.class.php';
			} elseif (strpos($orgclassname, 'LC') === 2) {
				$filename = 'core/libs/' . $classname . '.class.php';
			} elseif (strpos($orgclassname, 'TF') === 2) {
				$filename = 'themes/' . $classname . '/' . $classname . '.class.php';
			} elseif ($orgclassname{2} === 'M') {
				$separator = strpos($classname, '_');
				if ($separator > 0) {
					$modname = substr($classname, 0, $separator);
					$classname = substr($classname, $separator + 1);
				} else {
					$modname = $classname;
				}
				if ($orgclassname{3} === 'C') {
					$filename = 'modules/' . $modname . '/controllers/' . $classname . '.class.php';
				} elseif ($orgclassname{3} === 'F') {
					$filename = 'modules/' . $modname . '/controllers/forms/' . $classname . '.class.php';
				} elseif ($orgclassname{3} === 'M') {
					$filename = 'modules/' . $modname . '/model/' . $classname . '.class.php';
				} elseif ($orgclassname{3} === 'O') {
					$filename = 'modules/' . $modname . '/' . $classname . '.class.php';
				} elseif ($orgclassname{3} === 'L') {
					$filename = 'modules/' . $modname . '/libs/' . $classname . '.class.php';
				} elseif ($orgclassname{3} === 'A') {
					$filename = 'modules/' . $modname . '/admin/' . $classname . '.class.php';
				} elseif ($orgclassname{3} === 'X') {
					$filename = 'modules/' . $modname . '/admin/forms/' . $classname . '.class.php';
				} elseif ($orgclassname{3} === 'I') {
					$filename = 'modules/' . $modname . '/installer.class.php';
				} else {
					$filename = '';
				}
			} else {
				$filename = '';
			}
		} else {
			$classname = $orgclassname;
			$filename = '';
		}

		if ($filename && is_file(OP_SYS_ROOT . $filename)) {
			require_once OP_SYS_ROOT . $filename;
		} else {
			if (is_file($libname = OP_SYS_ROOT . 'core/libs/' . $classname . '.class.php')) {
				require_once $libname;
			} else {
				die('Class ' . htmlspecialchars($orgclassname) . ' was not found in files: ' . htmlspecialchars($filename ? substr($filename, strlen(OP_SYS_ROOT)) . ', ' : '') . htmlspecialchars(substr($libname, strlen(OP_SYS_ROOT))) . '.');
			}
		}
	}
);
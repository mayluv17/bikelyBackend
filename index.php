<?php
ini_set('display_errors', 0);

define('LIB', dirname(__FILE__).'/library');
define('APP', dirname(__FILE__).'/application');

require_once (LIB.'/ctf.php');
require_once (APP.'/database.php');

$ctf = new Ctf();
$ctf->main();
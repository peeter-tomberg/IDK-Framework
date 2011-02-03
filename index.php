<?php

ini_set('display_errors',1); 
ini_set('allow_url_fopen',1); 
error_reporting(E_ALL);

function getmicrotime($e = 7) 
{ 
    list($u, $s) = explode(' ',microtime()); 
    return bcadd($u, $s, $e); 
} 
/**
 * Start time of the application
 */
$start_time = getmicrotime();
/**
 * Require the IDK framework
 */
require 'Idk/IDK.php';
/**
 * Initiate the IDK framework
 */
new \Idk\Idk();

$methodCall = new \Idk\MethodCall($_SERVER['QUERY_STRING']);

try {
	$methodCall->checkControllerSecurityAccess();
	$methodCall->checkMethodSecurityAccess();
}
catch(SecurityExceptionNotLoggedIn $e) {
	\Idk\ExceptionHandler::handle($e);
}	
catch(SecurityExceptionNotEnoughPermissions $e) {
	\Idk\ExceptionHandler::handle($e);
}

try {
	$methodCall->callMethod();
}
catch(Exception $e) {
	\Idk\ExceptionHandler::handle($e);
}
try {
	$methodCall->renderView();
}
catch(Exception $e) {
	\Idk\ExceptionHandler::handle($e);
}

\Idk\KLogger::getInstance()->logInfo("IDK finished serving the request, memory usage: " . memory_get_peak_usage() . " bytes, time: " . sprintf('%.3f', getmicrotime() - $start_time). 's');


	
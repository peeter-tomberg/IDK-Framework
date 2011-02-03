<?php 
require 'AppConfig/Logger.php';

$file = \AppConfig\Logger::$_defaultLogLocation . '/' . 'log_' . date('Y-m-d') . '.txt';
/**
 * Require the library
 */
require 'Idk/PHPTail.php';
/**
 * Initilize a new instance of PHPTail
 * @var PHPTail
 */
$tail = new \Idk\PHPTail($file);
/**
 * We're getting an AJAX call
 */
if(isset($_GET['ajax']))  {
	echo $tail->getNewLines($_GET['lastsize'], $_GET['grep'], $_GET['invert']);
	die();
}
/**
 * Regular GET/POST call, print out the GUI
 */
$tail->generateGUI();

 
	
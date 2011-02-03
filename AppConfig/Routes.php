<?php
/**
 * Default routing
 * 
 * Route name (used in redirects) | route(if your method has the variable $variable it is automatically passed on) | controller | action
 */
//$map->connect('Route.name', '/my/route/:variable', 'controller', 'method');

/**
 * Default controller, for when there is no data supplied in the URL 
 */
$map->connect('default', '',  \AppConfig\GeneralConfig::$defaultController, \AppConfig\GeneralConfig::$defaultAction);
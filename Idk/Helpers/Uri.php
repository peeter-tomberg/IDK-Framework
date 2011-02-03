<?php 

namespace Idk\Helpers;

class Uri {
	/**
	 * Calls a header redirect to a specific route
	 * @param string $route - The name of the route we'll be redirected to
	 * @param array $data - Array of data
	 */
	public static function redirect($route, $data = array()) {
		return RouteMap::getInstance()->redirect_to($route, $data);
	}
	
	public static function url($route, $data = array()) {
		return RouteMap::getInstance()->url_for($route, $data);
	}
}

?>
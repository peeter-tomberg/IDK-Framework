<?php
/**
 * This class handles calling a controllers method and the security setup
 * @author User
 *
 */

namespace Idk;

use ReflectionClass;

class MethodCall {

	/**
	 *
	 * @param $url
	 */
	public function __construct($url) {

		$map = RouteMap::getInstance();

		require 'AppConfig/Routes.php';

		try	{
			list($route, $action, $args) = $map->match($url);
		}
		catch (\Idk\Exceptions\ERouteMapNotFound $e) {
			exit('Error: RouteMap not found');
		}
		catch (\Idk\Exceptions\ERouteMapNoMatch $e) {
			Idk::getSavant()->display('views/errors/404.tpl.php');
			exit();
		}
		catch (\Idk\Exceptions\ERouteMapReqs $e) {
			Idk::getSavant()->display('views/errors/404.tpl.php');
			exit();
		}
		catch (\Idk\Exceptions\ERouteMapNoReqs $e) {
			Idk::getSavant()->display('views/errors/404.tpl.php');
			exit();
		}
		$controllerName = $action[0];
		$action = $action[1];
		
		require 'controllers/'. $controllerName .'.php';
		$controllerWithPath = '\controllers\\'. $controllerName;
		
		$this->controller = new $controllerWithPath();
		$this->action = $action;
		$this->class = new ReflectionClass($controllerWithPath);
		$this->method = $this->class->getMethod($action);
		$this->routeMap = $map;
		$this->args = $args;
	}

	/**
	 * The name of the controller we're trying to call
	 * @var Controller
	 */
	private $controller;
	/**
	 * The name of the action we're trying to call
	 */
	public $action;
	/**
	 * The ReflectionClass of the controller
	 */
	private $class;
	/**
	 * The ReflectionMethod of the action
	 * @var ReflectionMethod
	 */
	private $method;
	/**
	 * The RouteMap instance
	 * @var RouteMap
	 */
	private $routeMap;
	/**
	 * The arguments passed here from the RouteMap class
	 * @var unknown_type
	 */
	private $args;
	/**
	 * Checks if a user has enough permissions to access the controller
	 * @throws SecurityExceptionNotLoggedIn Thrown when a user is trying to access a controller that is limited to logged in users only
	 * @throws SecurityExceptionNotEnoughPermissions Thrown when a user does not have a permission required to access a controller
	 */
	public function checkControllerSecurityAccess() {
		$permission = Annotations::getReader()->getClassAnnotation($this->class,'Secure');
		if($permission != null) {
			$user = User::getAuthenticatedUser();
			if($permission->permission == null) {
				if($user == null) {
					if($permission->redirect != null) {
						Uri::redirect($permission->redirect, $permission->data);
						return;
					}
					throw new \Idk\Exceptions\SecurityExceptionNotLoggedIn('User needs to be logged in to access this area');
				}
			}
			else if(!$user->hasPermission($permission->permission)) {
				if($permission->redirect != null) {
					Uri::redirect($permission->redirect, $permission->data);
					return;
				}
				throw new \Idk\Exceptions\SecurityExceptionNotEnoughPermissions($permission->permission);
			}
		}
	}
	/**
	 * Checks if a user has enough permissions to access the controllers method
	 * @throws SecurityExceptionNotLoggedIn Thrown when a user is trying to access a controllers method that is limited to logged in users only
	 * @throws SecurityExceptionNotEnoughPermissions Thrown when a user does not have a permission required to access a controllers method
	 */
	public function checkMethodSecurityAccess() {
		$permission = Annotations::getReader()->getMethodAnnotation($this->method,'Secure');
		if($permission != null) {
			$user = User::getAuthenticatedUser();
			if($permission->permission == null) {
				if($user == null) {
					if($permission->redirect != null) {
						Uri::redirect($permission->redirect, $permission->data);
						return;
					}
					throw new \Idk\Exceptions\SecurityExceptionNotLoggedIn('User needs to be logged in to access this area');
				}
			}
			else if($user == null || !$user->hasPermission($permission->permission)) {
				if($permission->redirect != null) {
					Uri::redirect($permission->redirect, $permission->data);
					return;
				}
				throw new \Idk\Exceptions\SecurityExceptionNotEnoughPermissions($permission->permission);
			}
		}
	}
	/**
	 * This function determines if a user can access the controller and method. Used in templating
	 */
	public function canAccess() {
		try {
			$this->checkControllerSecurityAccess();
			$this->checkMethodSecurityAccess();
		}
		catch(SecurityExceptionNotLoggedIn $e) {
			return false;
		}
		catch(SecurityExceptionNotEnoughPermissions $e) {
			return false;
		}
		return true;
	}
	/**
	 * Actually calls the method and maps $_REQUEST parameters to variables
	 */
	public function callMethod() {


		$params = array();
		foreach($this->method->getParameters() as $param) {
			$paramName = $param->getName();
			if(isset($this->args[$paramName])) {
				$params[$paramName] = $this->args[$paramName];
			}
			else {
				$params[$paramName] = null;
			}
		}
		ob_start();
		var_dump($params);
		$paramString = ob_get_clean();
		KLogger::getInstance()->logInfo($_SERVER['REMOTE_ADDR'] . " accessing controller: " . $this->class->name . " | action: " . $this->action . ' | params: ' . $paramString);

		call_user_func_array(array($this->controller, $this->action), $params);
	}
	/**
	 * Renders the view file
	 */
	public function renderView() {

		$tpl = Idk::getSavant();

		/**
		 * A set of predefined filters
		 */
		if(\AppConfig\GeneralConfig::$minify_CSS)
			$tpl->addFilters(array('CSS_minifier', 'filter'));
		if(\AppConfig\GeneralConfig::$minify_JS)
			$tpl->addFilters(array('JS_minifier', 'filter'));
		if(\AppConfig\GeneralConfig::$minify_HTML)
			$tpl->addFilters(array('HTML_minifier', 'filter'));

		$view = Annotations::getReader()->getClassAnnotation($this->class,'Idk\Annotations\View');
		if($view != null && $view->folder != null) {
			$folder = $view->folder . '/';
		}
		else {
			$folder = '';
		}
		$view = Annotations::getReader()->getMethodAnnotation($this->method, 'Idk\Annotations\View');
		if($view != null && strtolower($view->type) == 'json') {
			return;
		}

		if($view == null || $view->template == "" || $view->template == null) {
			$viewFilename = 'views/'.$folder.str_replace('controllers\\', '', get_class($this->controller)). '_' . $this->action . '.tpl.php';
		}
		else {
			$viewFilename = 'views/'.$folder.str_replace('controllers\\', '', get_class($this->controller)). '_' .$view->template.'.tpl.php';
		}

		foreach($this->controller->getVariables() as $key => $value)
			$tpl->$key = $value;

		$tpl->display($viewFilename);
	}
}

?>
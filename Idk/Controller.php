<?php
namespace Idk;
class Controller {

	
	private $templateVariables = array();
	
	public function addVariable($name, $value) {
		$this->templateVariables[$name] = $value;
	}
	public function addVariables($array) {
		array_merge($this->templateVariables, $array);
	}
	public function getVariables() {
		return $this->templateVariables;
	}
	
	
}

?>
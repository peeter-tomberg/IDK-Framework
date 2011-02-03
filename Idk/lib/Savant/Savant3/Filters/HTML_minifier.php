<?php
namespace Savant\Savant3\Filters;

require 'Idk\lib\min\lib\Minify\HTML.php';

class HTML_minifier extends \Savant\Savant3\Savant3_Filter  {
	
	public static function filter($source) {
		return \HTML::minify($source);
	}
}


?>
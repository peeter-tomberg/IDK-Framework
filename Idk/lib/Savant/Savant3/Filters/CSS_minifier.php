<?php
namespace Savant\Savant3\Filters;

class CSS_minifier extends \Savant\Savant3\Savant3_Filter  {

	public static function filter($source) {

		$options = array(
			'tag'=>'link',
			'type'=>'text/css',
			'ext'=>'css',
			'src'=>'href',
			'rel'=>'stylesheet',
			'self_close'=>true
		);

		preg_match("!<head>.*?</head>!is", $source, $matches);

		if(is_array($matches)) {
			preg_match_all("!<" . $options['tag'] . "[^>]+" . $options['type'] . "[^>]+>(</" . $options['tag'] . ">)?!is", $matches[0], $matches);
		}

		$script_array = $matches[0];

		if(is_array($script_array)) {

			//Remove empty sources
			foreach($script_array AS $key=>$value) {
				preg_match("!" . $options['src'] . "=\"(.*?)\"!is", $value, $src);
				if(!$src[1]) {
					unset($script_array[$key]);
				}
			}

			$sources = array();

			foreach($script_array AS $key=>$value) {
				//Get the src
				preg_match("!" . $options['src'] . "=\"(.*?)\"!is", $value, $src);
			 	$current_src = str_replace("http://".$_SERVER['HTTP_HOST'],"",$src[1]);
				$sources[] = $current_src;
				//Get the code
				if($key == count($script_array)-1) { //Remove script
					$source = str_replace($value,"@@marker@@",$source);
			   	}
			   	else {
					$source = str_replace($value,"",$source);
				}
			}
			$source = str_replace("@@marker@@", '<link rel="stylesheet" type="text/css" href="'.\AppConfig\GeneralConfig::$baseURL.'minify.php?f='.implode($sources, ',').'"/>',$source);
		}
		return $source;
	}
}


?>
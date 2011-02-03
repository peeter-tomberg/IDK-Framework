<?php

namespace Idk;

/**
 * Annotations are required to be loaded manually, otherwise the AnnotationReader will not find them.
 */
require 'Annotations/Secure.php';
require 'Annotations/View.php';
require 'Annotations/Persist.php';
require 'Annotations/OneToOne.php';
require 'Annotations/OneToMany.php';
require 'Annotations/Route.php';

class Annotations {
	/**
	 * @var AnnotationReader
	 */
	private static $reader = null;
	/**
	 * @return AnnotationReader
	 */
	public static function getReader() {
		if(self::$reader == null) {
			self::$reader = new \Doctrine\Common\Annotations\AnnotationReader(Config::getInstance()->getCache());
			self::$reader->setDefaultAnnotationNamespace('\Idk\Annotations\\');
		}
		
		return self::$reader;
	}

}

?>
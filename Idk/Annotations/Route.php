<?php
namespace Idk\Annotations;
/**
 * Database persistance annotation, used to define if a variable should be persisted in the database or not
 * @author User
 *
 */
class Route extends \Doctrine\Common\Annotations\Annotation
{
	public $route = "";
	
	public $validation = array();
}

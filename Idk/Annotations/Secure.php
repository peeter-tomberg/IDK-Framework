<?php
namespace Idk\Annotations;
/**
 * Security annotation, used for defining permission based access to controllers
 * @author User
 *
 */
class Secure extends \Doctrine\Common\Annotations\Annotation
{
    public $permission;
    
    public $redirect;
    
    public $data;
}

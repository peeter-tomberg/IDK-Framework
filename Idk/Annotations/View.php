<?php
namespace Idk\Annotations;
/**
 * View annotation, used for defining views
 * @author User
 *
 */
class View extends \Doctrine\Common\Annotations\Annotation
{
    public $template;
    
    public $type = 'html';
    
    public $folder = '.';
}

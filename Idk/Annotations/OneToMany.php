<?php
namespace Idk\Annotations;
/**
 * Database persistance annotation, used to define a OneToMany relationship
 * @author User
 *
 */
class OneToMany extends \Doctrine\Common\Annotations\Annotation
{
    public $class;
}

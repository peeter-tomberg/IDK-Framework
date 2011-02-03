<?php
namespace Idk\Annotations;
/**
 * Database persistance annotation, used to define if a class is linked with another class
 * @author User
 *
 */
class OneToOne extends \Doctrine\Common\Annotations\Annotation
{
    public $class;
}

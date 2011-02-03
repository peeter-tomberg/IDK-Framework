<?php
/**
 * Thrown when a user is trying to access a controller or a method that is limited to logged in users only
 */

namespace Idk\Exceptions;

use \Exception;

class SecurityExceptionNotLoggedIn extends Exception {

}

?>
<?php
namespace AppConfig;
class Logger {
	
	/**
     * Error severity, from low to high. From BSD syslog RFC, secion 4.1.1
     * @link http://www.faqs.org/rfcs/rfc3164.html
     */
    const EMERG  = 0;  // Emergency: system is unusable
    const ALERT  = 1;  // Alert: action must be taken immediately
    const CRIT   = 2;  // Critical: critical conditions
    const ERR    = 3;  // Error: error conditions
    const WARN   = 4;  // Warning: warning conditions
    const NOTICE = 5;  // Notice: normal but significant condition
    const INFO   = 6;  // Informational: informational messages
    const DEBUG  = 7;  // Debug: debug messages
    const OFF    = 8;  // Log nothing at all
    
	/**
	* Default severity of log messages, if not specified
	* @var integer
	*/
	public static $_defaultSeverity    = self::DEBUG;
	/**
	* Default location of the log folder, if not specified
	* @var integer
	*/
	public static $_defaultLogLocation    = "log";
	/**
	* Valid PHP date() format string for log timestamps
	* @var string
	*/
	public static $_dateFormat         = 'Y-m-d G:i:s';

}
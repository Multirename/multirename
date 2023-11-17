<?php

/**
 * Multirename - Runner for the multirename tool at https://github.com/Multirename/
 * for MUMSYS Library for Multi User Management System
 *
 * @license LGPL Version 3 http://www.gnu.org/licenses/lgpl-3.0.txt
 * @copyright (c) 2015 by Florian Blasel
 * @author Florian Blasel <[ba|z|a]sh: echo 1l2b33.code@EmAil.c2m | tr 123AE foeag>
 *
 * @category    Mumsys
 * @package     Library
 * @subpackage  Multirename
 * @version     2.4.6
 * Created 28.02.2015
 */

 /**
  * Example (you may check INSTALL file first):
  * multirename --test --fileextensions "avi;ts;mpg" --keepcopy --path /tmp/ \
  * -s 'this-goes=that-is;regex:/^(\d{5})$/i'=%path2%_%path1%_$1; =_'
  */


$pathLibrary = __DIR__ . '/library/mumsys';

// phar file needs an absolut location!!
$pathLogfile = '/tmp/';

// --- misc -------------------------------------------------------------------
error_reporting( E_ALL );
ini_set( 'display_errors', '1' );
// abort possible BUT: some things take time! dont do it! test before!
ignore_user_abort( false );

ini_set( 'max_execution_time', '0' );
ini_set( 'memory_limit', '32M' );
date_default_timezone_set( 'Europe/Berlin' );

// --- bootstrap for Mumsys library -------------------------------------------
require_once( $pathLibrary . '/Mumsys_Loader.php');
spl_autoload_extensions( '.php' );
spl_autoload_register( array('Mumsys_Loader', 'autoload') );

// --- prepare the logger -----------------------------------------------------
if ( empty( $_SERVER['REMOTE_USER'] ) ) {
    $user = $_SERVER['USER'];
} else {
    $user = $_SERVER['REMOTE_USER'];
}

$logOptions = array(
    // user based or access problems take affect
    'logfile' => $pathLogfile . 'multirename.' . $user . '.log',
    'way' => 'a',
    'logLevel' => 7,
    'msglogLevel' => 7, // can be changed in cmd line! as --loglevel
    'msgLineFormat' => '[%3$s] %5$s',
    'msgEcho' => true,
    'msgReturn' => false,
    'maxfilesize' => 1024000 * 3,
    'msgColors' => false,
);
$fileLogger = new Mumsys_Logger_File( $logOptions );
$logger = new Mumsys_Logger_Decorator_Messages( $fileLogger, $logOptions );


// --- pipe uncachable errors to the logger -----------------------------------
function myExceptionHandler( $ex )
{
    global $logger;

    $logger->log( 'No catchable exception found:', 0 );
    $logger->log( $ex->getMessage(), 0 );
    $logger->log( 'Exception trace:' . PHP_EOL . $ex->getTraceAsString(), 0 );
}

function myErrorHandler( $code, $message, $file, $row )
{
    global $logger;
    $loglevel = 0;
    $codes = array(
        1 => 'E_ERROR',
        2 => 'E_WARNING',
        4 => 'E_PARSE',
        8 => 'E_NOTICE',
        16 => 'E_CORE_ERROR'
    );
    if ( isset( $codes[$code] ) ) {
        $code = $codes[$code];
    }

    $string = 'PHP-Error "%4$s" (%3$s) in "%1$s:%2$s"';
    $message = sprintf( $string, basename( $file ), $row, $code, $message );
    $logger->log( $message, $loglevel );
}

set_exception_handler( 'myExceptionHandler' );
set_error_handler( 'myErrorHandler' );

//
// --- run ---------------------------------------------------------------------
//

try {
    $options = Mumsys_Multirename::getSetup( true );
    // not needed in setup but for shell args
    $options['--help|-h'] = 'Show this help';

    $opts = new Mumsys_GetOpts( $options );
    $config = $opts->getResult();

    if ( !isset( $config['path'] ) && !isset( $config['from-config'] ) && !isset( $config['help'] )
        && !isset( $config['version'] ) && !isset( $config['version-long'] ) ) {
        $message = 'Parameters found but incomplete.' . PHP_EOL . 'Usage:' .
            PHP_EOL . $opts->getHelp() . PHP_EOL;
        $logger->log( $message, 5 );
    } else {
        if ( isset( $config['help'] ) ) {
            $logger->log( 'Usage:' . PHP_EOL . $opts->getHelp(), 6 );
        } elseif ( isset( $config['version'] ) ) {
            echo Mumsys_Multirename::getVersionShort();
        } elseif ( isset( $config['version-long'] ) ) {
            echo Mumsys_Multirename::getVersionLong();
        } else {
            $oFiles = new Mumsys_FileSystem();
            $oMultirename = new Mumsys_Multirename( $config, $oFiles, $logger );
        }
    }
}
catch ( Mumsys_Exception $err ) {
    $logger->log( 'Catched mumsys exception: ', 0 );
    $logger->log( $err->getMessage(), 0 );
}
catch ( Exception $e ) {
    $logger->log( 'Standard exception found: ', 0 );
    $logger->log( $e->getMessage(), 0 );
    $logger->log( $e->getTraceAsString(), 0 );
}

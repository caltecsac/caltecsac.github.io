<?
// Database connection details 
$DBHOST = 'localhost';
$DBNAME = 'fop2';
$DBUSER = 'root';
$DBPASS = 'root';

$language="en";

// ---------------------------------------------------------
// Do not modify below this line
// ---------------------------------------------------------

define('DEBUG',false);

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors',     0);
ini_set("session.cookie_lifetime", "0");    // conservar session hasta que se cierre el navegador
ini_set("session.gc_maxlifetime", 60*60*9); // duracion maxima de la session

// Site specific
define("MYAP",  "FOP2");
define("TITLE", "Flash Operator Panel 2");
if(isset($_SERVER['PATH_INFO'])) {
    define("SELF",  substr($_SERVER['PHP_SELF'], 0, (strlen($_SERVER['PHP_SELF']) - @strlen($_SERVER['PATH_INFO']))));
} else {
    define("SELF",  $_SERVER['PHP_SELF']);
}

// Parsing time calculation
$time      = explode(' ', microtime());
$time      = $time[1] + $time[0];
$begintime = $time;

// Session start
session_start();
//session_register(MYAP);

// General classes inclussion
require_once("lib/func.php");
require_once("lib/dblib.php");
require_once("lib/dbgrid.php");
require_once("lib/teldns.php");

set_error_handler("funcErrorHandler",E_ALL);

$db = new dbcon($DBHOST, $DBUSER, $DBPASS, $DBNAME, true);

set_config();

$traduccionesQueFaltan = Array();

require_once("lang/$language.php");

header('content-type: text/html; charset: utf-8'); 
?>

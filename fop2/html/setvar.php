<?
require_once("config.php");

if(isset($_REQUEST['sesvar'])) {
    $valid_vars[] = "context";
    $valid_vars[] = "extension";
    $valid_vars[] = "phonebook";
    $variable= $_REQUEST['sesvar'];
    $value   = $_REQUEST['value'];
    if(!in_array($variable,$valid_vars)) {
        die('no way');
    }
    $_SESSION[MYAP][$variable]=$value;
}

?>


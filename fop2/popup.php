<?
include_once "../../libs/paloSantoDB.class.php";
include_once "../../libs/misc.lib.php";
include_once "../../libs/paloSantoConfig.class.php";

$pConfig = new paloConfig("/etc", "amportal.conf", "=", "[[:space:]]*=[[:space:]]*");
$arrConfig = $pConfig->leer_configuracion(false);

//solo para obtener los devices (extensiones) creadas.
$dsnAsterisk = $arrConfig['AMPDBENGINE']['valor']."://".
               $arrConfig['AMPDBUSER']['valor']. ":".
               $arrConfig['AMPDBPASS']['valor']. "@".
               $arrConfig['AMPDBHOST']['valor']."/asterisk";


$telefono=base64_decode($_GET['clidnum']);

$pDBinterno = new paloDB($dsnAsterisk);
$query="select description from devices where id='$telefono'";
$result=$pDBinterno->getFirstRowQuery($query, true);
if(count($result)>0) {
   echo $telefono." ".$result['description']."<br>";
   exit;
}

$pDB = new paloDB("sqlite3:////var/www/db/address_book.db");
$query   = "SELECT * FROM contact WHERE telefono='$telefono'";
$result=$pDB->getFirstRowQuery($query, true);
if(count($result)>0) {
   echo $telefono." ".$result['name']." ".$result['last_name']."<br>";
   exit;
}
echo "No match<br>";
foreach($_GET as $key=>$val) {
    echo "$key = ".base64_decode($val)."<br/>";
}
?>

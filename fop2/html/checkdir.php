<?

require_once('config.php');


/*
// Here you can fire your own popups or do whatever you want
// Uncomment this function block to fire a popup to google.com

function custom_popup($clidnum,$clidname,$fromqueue,$exten) {

   header("Content-type: text/javascript");
   //echo "window.open('http://www.google.com/search?q=$clidname')";
   echo "alert('custom popup $clidnum, $clidname, $fromqueue, $exten');\n";
   
}
*/

if(isset($_GET['clidnum'])) {
    $clidnum   = base64_decode($_GET['clidnum']);
} else {
    $clidnum="";
}

if(isset($_GET['clidname'])) {
    $clidname  = base64_decode($_GET['clidname']);
} else {
    $clidname="";
}

if(isset($_GET['fromqueue'])) {
    $fromqueue = $_GET['fromqueue'];
} else {
    $fromqueue = "";
}

if(isset($_GET['exten'])) {
    $exten = $_GET['exten'];
} else {
    $exten = "";
}

if(isset($_GET['notify'])) {
    $notify = base64_decode($_GET['notify']);
} else {
    $notify = "";
}


if(function_exists("custom_popup")) {
    custom_popup($clidnum,$clidname,$fromqueue,$exten);
    exit;
}

$largo       = strlen($clidnum);
$significant = 8;
$startoffset = 0;

if($largo > $significant) {
    $startoffset=$largo-$significant;
}

$clid_significant = substr( $clidnum, $startoffset );

$res = $db->consulta("SET NAMES utf8");
$res = $db->consulta("SELECT concat(firstname,' ',lastname,'<br/>',company) as name,picture FROM visual_phonebook WHERE phone1 LIKE '%%%s'OR phone2 LIKE '%%%s' LIMIT 1",$clid_significant,$clid_significant);

if($res) {
    if($db->num_rows()>0) {
        $row = $db->fetch_assoc();
        echo $clidnum."<br/>".$row['name']."\n";
        echo "./uploads/".$row['picture'];
        echo "\n".$fromqueue."\n";
        if($notify <> "") {
            echo $notify."\n";
        } else {
            echo "\n";
        }
        $clidname = $row['name'];
        exit;
    }
}


echo $clidnum." ".$clidname."\n";
if($notify <> "") {
    echo "images/warning.gif";
} else {
    echo "images/telephone_128.png";
}
echo "\n".$fromqueue."\n";
if($notify <> "") {
    echo $notify."\n";
} else {
    echo "\n";
}


?>

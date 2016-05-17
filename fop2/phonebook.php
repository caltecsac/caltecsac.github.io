<?
header("Content-Type: text/html; charset=utf-8");
require_once("config.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us" lang="en-us" >
<head>
<?
if(isset($page_title)) { 
    echo "    <title>$page_title></title>\n"; 
} else {
    echo "    <title>".TITLE."</title>\n"; 
}
?>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <meta http-equiv="imagetoolbar" content="false"/>
    <meta name="MSSmartTagsPreventParsing" content="true"/>
    <meta name="description" content=""/>
    <meta name="keywords" content=""/>
    <link rel="stylesheet" type="text/css" href="css/fluid/reset.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/fluid/text.css" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/dbgrid.css" media="screen" />

<?
if(isset($extrahead)) {
    foreach($extrahead as $bloque) {
        echo "$bloque";
    }
}
?>
</head>
<body>
<div style='width: 90%; padding: 1em; margin: auto;'>
<?

$context   = $_SESSION[MYAP]['context'];
$extension = $_SESSION[MYAP]['extension'];
$allowed   = $_SESSION[MYAP]['phonebook'];

if($allowed <> "yes") {
   die("no way");
}

if($context=="") { 
    $addcontext="";
} else {
    $addcontext="${context}_";
}

// Sanitize Input
$addcontext = ereg_replace("\.[\.]+", "", $addcontext);
$addcontext = ereg_replace("^[\/]+", "", $addcontext);
$addcontext = ereg_replace("^[A-Za-z][:\|][\/]?", "", $addcontext);

$extension = ereg_replace("'", "",  $extension );
$extension = ereg_replace("\"", "", $extension );
$extension = ereg_replace(";", "",  $extension );

$grid =  new dbgrid($db);
$grid->set_table('visual_phonebook');
//$grid->set_caption(trans('Manage Phonebook'));
$grid->salt("dldli3ks");
$grid->hide_field('id');
$grid->set_per_page(5);
$grid->set_condition("context='$context' AND (owner='$extension' OR (owner<>'$extension' AND private='no'))");
$grid->set_default_values("context",$context);
$grid->set_default_values("owner",$extension);
$grid->set_input_type("context","hidden");
$grid->set_input_type("owner","hidden");
$grid->no_edit_field("context");
$grid->no_edit_field("owner");
$grid->hide_field('context');
$grid->hide_field('owner');
$grid->edit_field_condition("private",'owner','=',$extension);


$fieldname = Array();
$fieldname[]=trans('First Name');
$fieldname[]=trans('Last Name');
$fieldname[]=trans('Company');
$fieldname[]=trans('Phone 1');
$fieldname[]=trans('Phone 2');
$fieldname[]=trans('Private');
$fieldname[]=trans('Picture');

//$grid->set_fields ( "id,firstname,lastname,company,phone1,phone2,owner,private,picture"); 
$grid->set_display_name( array('firstname','lastname','company','phone1','phone2','private','picture'),
                         $fieldname);
$grid->allow_view(true);
$grid->allow_edit(true);
$grid->allow_delete(true);
$grid->allow_add(true);
$grid->allow_export(true);
$grid->allow_import(true);
$grid->allow_search(true);
$grid->set_search_fields(array('firstname','lastname','company','phone1','phone2'));

$grid->set_input_type('picture','img');
//$grid->set_input_type('owner','select',array($extension,''));

$grid->force_import_field("context",$context);
$grid->force_import_field("owner",$extension);

$grid->add_display_filter('picture','display_image');
//$grid->add_display_filter('owner','display_private');
//$grid->add_edit_filter('owner','display_private');

$grid->set_user_directory('./uploads/'.$addcontext);

//$grid->add_validation_type('email','email');
$grid->show_grid();


function display_image($img) {
    global $addcontext;
    if(is_file("./uploads/${addcontext}$img")) {
        return "<img src='./uploads/${addcontext}$img' height='50'/>";
    } else { 
        return "n/a"; 
    }
}

function display_private($owner) {
    if($owner=="") {
        return trans('no');
    } else {
        return trans('yes');
    }
}

?>
</div>
</body>
</html>

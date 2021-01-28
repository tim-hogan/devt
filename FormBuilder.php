<?php
session_start();

function var_error_log( $object=null,$text='')
{
    ob_start();
    var_dump( $object );
    $contents = ob_get_contents();
    ob_end_clean();
    error_log( "{$text} {$contents}" );
}


require_once "./includes/classSecure.php";

require_once "./includes/classnValuateCorpDB.php";
$DB = new nvaluatecorp($devt_environment->getDatabaseParameters());

require "./includes/classFormList2.php";

$r = $DB->query("show tables");
$a = $r->fetch_all(MYSQLI_ASSOC);
foreach($a as $table)
{
    echo "<p>";
    var_dump($table);
    echo "</p>";
}


?>
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
$a = $r->fetch_all(MYSQLI_NUM);
foreach($a as $table)
{
    $name = $table[0];

    echo "<p>Table name: ";
    var_dump($name);
    echo "</p>";

    $finfo = $DB->fieldsFromTable($table[0]);

    foreach ($finfo as $field)
    {
        echo "<p>   Field Info: ";
        var_dump($field);
        echo "</p>";
    }

}


?>
<?php
session_start();

/*
 * Define the field flags
 *
*/
define('FIELD_NOT_NULL_FLAG',1);
define('FIELD_PRI_KEY_FLAG', 2);
define('FIELD_UNIQUE_KEY_FLAG', 4);
define('FIELD_BLOB_FLAG', 16);
define('FIELD_UNSIGNED_FLAG', 32);
define('FIELD_ZEROFILL_FLAG', 64);
define('FIELD_BINARY_FLAG', 128);
define('FIELD_ENUM_FLAG', 256);
define('FIELD_AUTO_INCREMENT_FLAG', 512);
define('FIELD_TIMESTAMP_FLAG', 1024);
define('FIELD_SET_FLAG', 2048);
define('FIELD_NUM_FLAG', 32768);
define('FIELD_PART_KEY_FLAG', 16384);
define('FIELD_GROUP_FLAG', 32768);
define('FIELD_UNIQUE_FLAG', 65536);


function var_error_log( $object=null,$text='')
{
    ob_start();
    var_dump( $object );
    $contents = ob_get_contents();
    ob_end_clean();
    error_log( "{$text} {$contents}" );
}

function strAssociateEntry($n,$v)
{
    $ret = '';
    $ret .= "\"{$n}\" => ";
    switch (gettype($v))
    {
        case "boolean":
            if ($v)
                $ret .= "true";
            else
                 $ret .= "false";
            break;
        case "integer":
            $ret .= strval($v);
            break;
        case "double":
            $ret .= strval($v);
            break;
        default:
            $ret .= "\"{$v}\"";
            break;
    }

    $ret .= ",\n";
    return $ret;
}

function outputArray($a,$level,&$output)
{
    foreach($a as $name => $v)
    {
        if (gettype($v) == 'array')
        {
            outputArray($v,$level+1,$output);
        }
        else
            $output .= strAssociateEntry($name,$v,$level*4);
    }
    $output .= "],\n";
}

require_once "./includes/classSecure.php";

require_once "./includes/classnValuateCorpDB.php";
$DB = new nvaluatecorp($devt_environment->getDatabaseParameters());

require "./includes/classFormList2.php";

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta name="viewport" content="width=device-width" />
    <meta name="viewport" content="initial-scale=1.0" />
    <title>FromBuilder</title>
</head>
<body>
    <?php
    $tabledefs = array();
    $r = $DB->query("show tables");
    $a = $r->fetch_all(MYSQLI_NUM);
    foreach($a as $table)
    {

        echo "<p>Table name: {$table[0]}</p>";

        $finfo = $DB->fieldsFromTable($table[0]);

        $form = array();
        $global = array();

        $global['table'] = $table[0];


        foreach ($finfo as $field)
        {
            echo "<p> Field Info: {$field->name}";

            if ($field->flags & FIELD_AUTO_INCREMENT_FLAG)
            {
                $global['primary_key'] = $field->name;
            }
            echo "</p>";
        }

        $form['global'] = $global;


        $tabledefs[$table[0]] = $form;

    }

    //Output text
    $strtext = '';

    outputArray($tabledefs,0,$strtext);

    file_put_contents("/var/nvaluate/formbuilder/formparams.php",$strtext);

    ?>
</body>
</html>

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


//Field types
define('FIELD_TYPE_TINYINT', 1);
define('FIELD_TYPE_SMALLINT', 2);
define('FIELD_TYPE_INTEGER', 3);
define('FIELD_TYPE_FLOAT', 4);
define('FIELD_TYPE_DOUBLE', 5);
define('FIELD_TYPE_TIMESTAMP', 7);
define('FIELD_TYPE_BIGINT', 8);
define('FIELD_TYPE_MEDIUMINT', 9);
define('FIELD_TYPE_DATE', 10);
define('FIELD_TYPE_TIME', 11);
define('FIELD_TYPE_DATETIME', 12);
define('FIELD_TYPE_YEAR', 13);
define('FIELD_TYPE_BIT', 16);
define('FIELD_TYPE_DECIMAL', 246);
define('FIELD_TYPE_VARCHAR', 253);
define('FIELD_TYPE_CHAR', 254);

function var_error_log( $object=null,$text='')
{
    ob_start();
    var_dump( $object );
    $contents = ob_get_contents();
    ob_end_clean();
    error_log( "{$text} {$contents}" );
}

function strAssociateEntry($n,$v,$l)
{
    $ret = '';
    for($i=0;$i<$l;$i++)
        $ret .= " ";
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

function outputArray($a,$level)
{
    $ret = "";
    $l = $level*4;
    foreach($a as $name => $v)
    {


        if (gettype($v) == 'array')
        {
            for($i=0;$i<$l;$i++)
                $ret .= " ";
            $ret .= "\"{$name}\" => [\n";
            $ret .= outputArray($v,$level+1);
            for($i=0;$i<$l;$i++)
                $ret .= " ";
            $ret .= "],\n";
        }
        else
        {
            $ret .= strAssociateEntry($name,$v,$l);
        }
    }
    //$ret .= "],\n";
    return $ret;
}

require_once "./includes/classSecure.php";

require_once "./includes/classnValuateCorpDB.php";
$DB = new nvaluatecorp($devt_environment->getDatabaseParameters());

require "./includes/classFormList2.php";



if (! isset($_SESSION['csrf_key']))
    $_SESSION['csrf_key'] = base64_encode(openssl_random_pseudo_bytes(32));
?>


<?php
function buildDefault()
{
    global $DB;

    $tabledefs = array();
    $r = $DB->query("show tables");
    $a = $r->fetch_all(MYSQLI_NUM);
    foreach($a as $table)
    {


        $finfo = $DB->fieldsFromTable($table[0]);

        $params = array();
        $global = array();
        $form = array();

        $global['table'] = $table[0];
        $form['heading'] = "{$table[0]} Heading";
        $form['introduction'] = "";

        $classes = array();

        $div = array();

        $div["inputtext"] = "d_inputtext";
        $div["emailtext"] = "d_inputtext";
        $div["passwordtext"] = "d_inputtext";
        $div["textarea"] = "d_textarea";
        $div["checkbox"] = "d_inputtext";
        $div["choice"] = "d_choice";
        $div["dropdown"] = "d_dropdown";
        $div["fk"] = "d_dropdown";


        $classes['div'] = $div;
        $form['classes'] = $classes;

        $groups = array();
        $deatils1 = array();
        $deatils1['heading'] = "GROUP NAME";
        $deatils1['introduction1'] = "";
        $deatils1['introduction2'] = "";
        $deatils1['introduction3'] = "";

        $groups['details1'] = $deatils1;
        $form['groups'] = $groups;


        $list = array();

        $list['type'] = "checkbox";
        $list['record_selector'] = true;
        $list['heading'] = "{$table[0]} List heading text";
        $list['introduction'] = "";
        $list['default_order'] = "";
        $list['default_where'] = "where {$table[0]}_deleted = 0";

        $fields = array();


        $doneanchor = false;
        foreach ($finfo as $field)
        {
            if ($field->flags & FIELD_AUTO_INCREMENT_FLAG)
            {
                $global['primary_key'] = $field->name;
            }

            $fdata = array();
            $fdata["dbfield"] = true;
            $fdata["fk_table"] = "";
            $fdata["fk_index"] = "";
            $fdata["fk_display"] = "";
            $fdata["fk_where"] = "";
            $fdata["fk_order"] = "";

            $fdata["maxlength"] = $field->max_length;
            $fdata["cols"] = "50";
            $fdata["rows"] = "4";
            $fdata["errname"] = $field->name;

            $ff = array();
            $ff["display"] = true;
            $ff["formlabel"] = $field->name;
            $ff["title"] = "";
            if ($field->flags & FIELD_NOT_NULL_FLAG)
                $ff["required"] = true;
            else
                $ff["required"] = false;

            $ff["default"] = "";
            $ff["errtext"] = "";
            $ff["posttext"] = "";
            $ff["trim"] =  true;
            $ff["group"] = "details1";

            $choice = array();

            $choiceEntry1 = array();
            $choiceEntry1["text"] = "Text 1";
            $choiceEntry1["value"] = "1";
            $choiceEntry1["selected"] = "javascript()";

            array_push($choice,$choiceEntry1);

            $choiceEntry2 = array();
            $choiceEntry2["text"] = "Text 2";
            $choiceEntry2["value"] = "2";
            $choiceEntry2["selected"] = "javascript()";

            array_push($choice,$choiceEntry2);

            $ff["choice"] = $choice;


            $fdata["form"] = $ff;


            $fl = array();
            $fl["display"] = true;
            $fl["heading"] = $field->name;

            if (! $doneanchor && !($field->flags & FIELD_PRI_KEY_FLAG) )
            {
                $fl["anchor"] = true;
                $doneanchor = true;
            }
            else
                $fl["anchor"] = false;
            $fl["displayoption"] = "";

            $ff["list"] = $fl;

            switch ($field->type)
            {
                case FIELD_TYPE_TINYINT:
                    $fdata['type'] = 'boolean';
                    $fdata['tag'] = "checkbox";
                    $fdata['sub-tag'] = "";
                    break;

                case FIELD_TYPE_SMALLINT:
                case FIELD_TYPE_INTEGER:
                case FIELD_TYPE_BIGINT:
                    $fdata['type'] = 'integer';
                    $fdata['tag'] = "input";
                    $fdata['sub-tag'] = "text";
                    break;

                case FIELD_TYPE_FLOAT:
                case FIELD_TYPE_DOUBLE:
                case FIELD_TYPE_DECIMAL:
                    $fdata['type'] = 'decimal';
                    $fdata['tag'] = "input";
                    $fdata['sub-tag'] = "text";
                    break;

                case FIELD_TYPE_TIMESTAMP:
                case FIELD_TYPE_DATETIME:
                    $fdata['type'] = 'datetime';
                    $fdata['tag'] = "input";
                    $fdata['sub-tag'] = "datetime-local";
                    break;

                case FIELD_TYPE_DATE:
                    $fdata['type'] = 'date';
                    $fdata['tag'] = "input";
                    $fdata['sub-tag'] = "date";
                    break;

                case FIELD_TYPE_TIME:
                    $fdata['type'] = 'time';
                    $fdata['tag'] = "input";
                    $fdata['sub-tag'] = "time";
                    break;

                case FIELD_TYPE_YEAR:
                    $fdata['type'] = 'year';
                    $fdata['tag'] = "input";
                    $fdata['sub-tag'] = "text";
                    break;
                case FIELD_TYPE_DECIMAL:
                case FIELD_TYPE_VARCHAR:
                case FIELD_TYPE_CHAR:
                    $fdata['type'] = 'text';
                    $fdata['tag'] = "input";
                    $fdata['sub-tag'] = "text";
                    break;

                default:
                    break;
            }

            $fields[$field->name] = $fdata;

        }

        $params['global'] = $global;
        $params['form'] = $form;
        $params['list'] = $list;
        $params['fields'] = $fields;


        $tabledefs[$table[0]] = $params;

    }
    return $tabledefs;
}

function OutputToFile($t)
{
    $strtext = '';
    $strtext = outputArray($t,0);
    file_put_contents("/var/nvaluate/formbuilder/formparams.php",$strtext);
}

function bTF($txt,$fn,$v)
{
    echo "<div class='ff'><span>{$txt}</span><input type='text' name='{$fn}' value='{$v}' /></div>";
}

function bBF($txt,$fn,$v)
{
    echo "<div class='ff'><span>{$txt}</span><input type='checkbox' name='{$fn}'";
    if ($v)
        echo " checked ";
    echo "/></div>";
}

function updateTextrec(&$a,$t)
{
    if (isset($_POST[$t]))
    {
        $a = $_POST[$t];
    }
}

$g_def = null;
$g_table = null;
if (isset($_SESSION['def']))
    $g_def = $_SESSION['def'];

if (isset($_GET['v']))
{
    if ($_GET['v'] == 'buildfromdb')
    {
        $g_def = buildDefault();
        $_SESSION['def'] = $g_def;
    }
    if ($_GET['v'] == 'output')
    {
        if ($g_def)
            OutputToFile($g_def);
    }
}

if (isset($_GET['t']))
{
    $g_table = $_GET['t'];
}

//Post
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    if (isset($_POST['tableupdate']))
    {
        if ($g_def)
        {
            $table = $_POST['table'];
            updateTextrec($g_def[$table] ['global'] ['primary_key'],'priamry_key');
        }
    }
    $_SESSION['def'] = $g_def;
}

?>

<!DOCTYPE HTML>
<html>
<head>
    <meta name="viewport" content="width=device-width" />
    <meta name="viewport" content="initial-scale=1.0" />
    <title>FormBuilder</title>
    <link rel='stylesheet' type='text/css' href='css/form.css' />
    <link rel='stylesheet' type='text/css' href='css/list.css' />
    <style>
        body {font-family: Arial, Helvetica, sans-serif;font-size: 10pt;margin: 0;padding: 0;}
        #container {}
        #header {background-color: #666;color: white;padding: 10px;}
        #header p {font-size: 24pt; font-family:'Times New Roman', Times, serif; text-align:center;}
        #menu {padding: 8px;border:solid 1px #777;}
        #menu div {display:inline-block; margin-right: 12px;}
        #menu a {text-decoration: none;}
        #main {padding: 0;}
        #flex {display: flex;}
        #left {background-color: #ddf;padding: 8px;}
        #left ul {list-style-type: none;padding-left: 8px;}
        #right1 {padding: 20px;border: solid 1px #888;border-top: none;background-color: #ffd;}
        #right2 {padding: 20px; border-right: solid 1px #888;border-bottom: solid 1px #888;background-color: #f8f8f8}
        #form1 span {margin-right: 8px;}
        .section {margin-bottom: 16px; border: solid 1px #aaa;padding: 12px;border-radius: 6px;}
        .secheading {margin: 0;position: relative;top: -20px;background-color: #ffd;display: inline-block;}
        .ff {margin-bottom: 16px;}
    </style>
</head>
<body>
    <div id="container">
        <div id="header">
            <p>deVT Form Builder Version 1</p>
        </div>
        <div id="menu">
            <div><a href="FormBuilder.php?v=buildfromdb">BUILD FROM DATABASE</a></div>
            <div><a href="FormBuilder.php?v=output">SAVE TOP FILE</a></div>
        </div>
        <div id="main">
            <div id="flex">
                <div id="left"><?php
                        if ($g_def)
                        {
                            echo "<ul>";
                            foreach ($g_def as $name => $table)
                            {
                                echo "<li><a href='FormBuilder.php?t={$name}'>{$name}</a></li>";
                            }
                            echo "</ul>";
                        }
                        ?></div>
                <div id="right1"><?php
                        if ($g_table)
                        {
                            $params = $g_def[$g_table];
                            $global = $params['global'];
                            $form = $params['form'];
                            echo "<h1>TABLE {$g_table}</h1>";
                            echo "<div id='form1'>";
                            echo "<form method='POST' action='{$_SERVER["PHP_SELF"]}'>";
                                echo "<div class='section'>";
                                    echo "<p class='secheading'>GLOBAL</p>";
                                    bTF('table','table',$global['table']);
                                    bTF('primary_key','primary_key',$global['primary_key']);
                                    bBF('single_record','single_record',$global['single_record']);
                                echo "</div>";
                                echo "<div class='section'>";
                                    echo "<p class='secheading'>FORM</p>";
                                    echo "<form method='POST' action='{$_SERVER["PHP_SELF"]}'>";
                                    bTF('heading','formheading',$form['heading']);
                                    bTF('introduction','forminroduction',$form['introduction']);
                                echo "</div>";
                                echo "<input type='hidden' name='table' value='{$g_table}'/>";
                                echo "<input type='submit' name='tableupdate' value='CONFIRM CHANGE' />";
                            echo "</form>";
                            echo "</div>";
                        }
                    ?></div>
                <div id="right2"><?php
                    if ($g_table)
                    {
                            echo "<div class='form'>";
                            echo "<form method='POST' autocomplete='off' action='{$_SERVER["PHP_SELF"]}'>";
                            $FL = new FormList($g_def[$g_table]);
                            $FL->buildFormFields(null,$DB);
                            echo "<div class='submit'>";
                                $v = FormList::encryptParam("table=server&action=create");
                                echo "<input type='hidden' name='v' value='{$v}' />";
                                echo "<input type='submit' name='_server_new' value='CREATE NEW' />";
                            echo "</div>";
                            echo "</form>";
                            echo "</div>";
                    }
                    ?></div>
            </div>
        </div>
    </div>
</body>
</html>

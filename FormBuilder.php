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
        $global['single_record'] = true;

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
                $global['single_record'] = false;
            }

            $fdata = array();
            $fdata["dbfield"] = true;
            $fdata["fk_table"] = "";
            $fdata["fk_index"] = "";
            $fdata["fk_display"] = "";
            $fdata["fk_where"] = "";
            $fdata["fk_order"] = "";

            $fdata["size"] = max(4,$field->length);
            $fdata["maxlength"] = $field->length;
            $fdata["cols"] = "50";
            $fdata["rows"] = "4";
            $fdata["errname"] = $field->name;
            $fdata["security_view"] = 0;
            $fdata["security_edit"] = 0;

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

            $fdata["list"] = $fl;

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
    $strtext = "<?php\n";
    $strtext .= "return [\n";
    $strtext .= outputArray($t,0);
    $strtext .= "];\n";
    $strtext .= "?>";

    file_put_contents("/var/nvaluate/formbuilder/formparams.php",$strtext);
}

function bTF($txt,$fn,$v)
{
    echo "<tr>";
    echo "<td>{$txt}</td>";
    echo "<td><input type='text' name='{$fn}' value='{$v}' /></td>";
    echo "</tr>";
    //echo "<div class='ff'><span>{$txt}</span><input type='text' name='{$fn}' value='{$v}' /></div>";
}

function bBF($txt,$fn,$v)
{
    echo "<tr>";
    echo "<td>{$txt}</td>";
    echo "<td><input type='checkbox' name='{$fn}'";
    if ($v)
        echo " checked ";
    echo "/></td>";
    echo "</tr>";
    //echo "<div class='ff'><span>{$txt}</span><input type='checkbox' name='{$fn}'";
    //if ($v)
        //echo " checked ";
    //echo "/></div>";
}

function bIF($txt,$fn,$v)
{
    $vv = intval($v);
    echo "<tr>";
    echo "<td>{$txt}</td>";
    echo "<td><input type='text' name='{$fn}' value='{$vv}' /></td>";
    echo "</tr>";
    //echo "<div class='ff'><span>{$txt}</span><input type='text' name='{$fn}' value='{$vv}' /></div>";
}


function updateTextrec(&$a,$t)
{
    if (isset($_POST[$t]))
    {
        $a = $_POST[$t];
    }
}

function updateBoolanrec(&$a,$t)
{
    if (isset($_POST[$t]))
    {
        $a = FormList::getCheckboxField($t);
    }
}


function updateTextFieldInfo($table,$field,$attribute)
{
    global $g_def;
    if (isset($_POST["{$table}_{$field}_{$attribute}"]) )
        $g_def[$table] ['fields'] [$field] [$attribute] = $_POST["{$table}_{$field}_{$attribute}"];
}

function updateIntegerFieldInfo($table,$field,$attribute)
{
    global $g_def;
    if (isset($_POST["{$table}_{$field}_{$attribute}"]) )
        $g_def[$table] ['fields'] [$field] [$attribute] = intval($_POST["{$table}_{$field}_{$attribute}"]);
}


$mode = null;

$g_def = null;
$g_table = null;
$g_field = null;

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
    if ($_GET['v'] == 'loadfromfile')
    {
        $mode = "loadfile";
    }
}

if (isset($_GET['t']))
{
    $g_table = $_GET['t'];
}

if (isset($_GET['f']))
{
    $v = $_GET['f'];
    $a = explode (":", $_GET['f']);
    $g_table = $a[0];
    $g_field = $a[1];
}

//Post
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    if (isset($_POST['tableupdate']))
    {
        if ($g_def)
        {
            $g_table = $_POST['table'];
            updateTextrec($g_def[$g_table] ['global'] ['primary_key'],'primary_key');
            updateBoolanrec($g_def[$g_table] ['global'] ['single_record'],'single_record');

            updateTextrec($g_def[$g_table] ['form'] ['heading'],'formheading');
            updateTextrec($g_def[$g_table] ['form'] ['introduction'],'formintroduction');

            updateTextrec($g_def[$g_table] ['form'] ['classes'] ['div'] ['inputtext'],'form_inputtext');
            updateTextrec($g_def[$g_table] ['form'] ['classes'] ['div'] ['emailtext'],'form_emailtext');
            updateTextrec($g_def[$g_table] ['form'] ['classes'] ['div'] ['passwordtext'],'form_passwordtext');
            updateTextrec($g_def[$g_table] ['form'] ['classes'] ['div'] ['textarea'],'form_textarea');
            updateTextrec($g_def[$g_table] ['form'] ['classes'] ['div'] ['checkbox'],'form_checkbox');
            updateTextrec($g_def[$g_table] ['form'] ['classes'] ['div'] ['choice'],'form_choice');
            updateTextrec($g_def[$g_table] ['form'] ['classes'] ['div'] ['dropdown'],'form_dropdown');
            updateTextrec($g_def[$g_table] ['form'] ['classes'] ['div'] ['fk'],'form_fk');

            for($cnt=0;$cnt < 50;$cnt++)
            {
                if (isset($_POST["form_groupname{$cnt}"]) )
                {
                    $name = $_POST["form_groupname{$cnt}"];
                    if (! isset($g_def[$g_table] ['form'] ['groups'] [$name]) )
                        $g_def[$g_table] ['form'] ['groups'] [$name] = array();
                    updateTextrec($g_def[$g_table] ['form'] ['groups'] [$name] ['heading'],"form_heading{$cnt}");
                    updateTextrec($g_def[$g_table] ['form'] ['groups'] [$name] ['introduction1'],"form_introduction1{$cnt}");
                    updateTextrec($g_def[$g_table] ['form'] ['groups'] [$name] ['introduction2'],"form_introduction2{$cnt}");
                    updateTextrec($g_def[$g_table] ['form'] ['groups'] [$name] ['introduction3'],"form_introduction3{$cnt}");
                }
                else
                    break;
            }

            updateTextrec($g_def[$g_table] ['list'] ['type'] ,'list_type');
            updateBoolanrec($g_def[$g_table] ['list'] ['single_record'],'list_single_record');
            updateTextrec($g_def[$g_table] ['list'] ['heading'] ,'list_heading');
            updateTextrec($g_def[$g_table] ['list'] ['introduction'] ,'list_introduction');
            updateTextrec($g_def[$g_table] ['list'] ['default_order'] ,'list_default_order');
            updateTextrec($g_def[$g_table] ['list'] ['default_where'] ,'list_default_where');

            $fields = $g_def[$g_table] ['fields'];
            foreach($fields as $name => $field)
            {
                $b = boolval(FormList::getCheckboxField("{$g_table}_{$name}_dispform"));
                $g_def[$g_table] ['fields'] [$name] ['form'] ['display'] = $b;
                $b = boolval(FormList::getCheckboxField("{$g_table}_{$name}_lsitform"));
                $g_def[$g_table] ['fields'] [$name] ['list'] ['display'] = $b;
            }

        }
    }

    if (isset($_POST["fieldupdate"]))
    {
        $table = $_POST['table'];
        $field = $_POST['field'];

        updateTextFieldInfo($table,$field,"type");
        updateTextFieldInfo($table,$field,"tag");
        updateTextFieldInfo($table,$field,"sub-tag");
        updateIntegerFieldInfo($table,$field,"size");
        updateIntegerFieldInfo($table,$field,"maxlength");
        updateIntegerFieldInfo($table,$field,"cols");
        updateIntegerFieldInfo($table,$field,"rows");
        updateTextFieldInfo($table,$field,"errname");
        updateIntegerFieldInfo($table,$field,"security_view");
        updateIntegerFieldInfo($table,$field,"security_edit");
    }


    if (isset($_POST["loadform"]) )
    {
        $g_def = null;
        if (isset($_POST["filename"]))
        {
            $filename = trim($_POST["filename"]);
            if (file_exists($filename))
            {
                $g_def = require($filename);
            }
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
        #fileload {display: none;}
        #fileload h1 {color: #777;}
        #fileload input {display: block; font-size: 14pt;}
        #fileload input[type="submit"] {margin-top: 20px; display: block; font-size: 14pt;}
        #flex {display: flex;}
        #left {background-color: #ddf;padding: 8px;}
        #left ul {list-style-type: none;padding-left: 8px;}
        #right1 {padding: 20px;border: solid 1px #888;border-top: none;background-color: #ffd;}
        #right2 {padding: 20px; border-right: solid 1px #888;border-bottom: solid 1px #888;background-color: #ffd;}
        #right3 {padding: 20px; border-right: solid 1px #888;border-bottom: solid 1px #888;background-color: #f8f8f8}
        #form1 span {margin-right: 8px;}
        .section {margin-bottom: 16px; border: solid 1px #aaa;padding: 12px;border-radius: 6px;}
        .secheading {margin: 0;position: relative;top: -20px;background-color: #ffd;display: inline-block;}
        .ff {margin-bottom: 16px;}
    </style>
    <script>
                <?php
        if ($mode && $mode == "loadfile")
        {
            echo "var g_mode = 'loadfile';";
        }
        else
        {
            echo "var g_mode = null;";
        }
                ?>
        function start() {
            if (g_mode == 'loadfile') {
                document.getElementById('fileload').style.display = 'block';
                document.getElementById('flex').style.display = 'none';
            }
        }
    </script>
</head>
<body onload="start()">
    <div id="container">
        <div id="header">
            <p>deVT Form Builder Version 1</p>
        </div>
        <div id="menu">
            <div><a href="FormBuilder.php?v=loadfromfile">LOAD FROM FILE</a></div>
            <div><a href="FormBuilder.php?v=buildfromdb">BUILD FROM DATABASE</a></div>
            <div><a href="FormBuilder.php?v=output">SAVE TO FILE</a></div>
        </div>
        <div id="main">
            <div id="fileload">
                <h1>LOAD FROM FILE</h1>
                <form method='POST' action='<?php echo $_SERVER["PHP_SELF"]?>'>
                    <label for="loadfilename">ENTER FILE NAME</label>
                    <input id="loadfilename" type="text" name="filename" value="/var/nvaluate/formbuilder/formparams.php" size="60"/>
                    <input type="submit" value="LOAD" name="loadform" />
                </form>
            </div>
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
                            $list = $params['list'];
                            $fields = $params['fields'];
                            echo "<h1>TABLE {$g_table}</h1>";
                            echo "<div id='form1'>";
                            echo "<form method='POST' action='{$_SERVER["PHP_SELF"]}'>";
                                echo "<div class='section'>";
                                    echo "<p class='secheading'>GLOBAL</p>";
                                    echo "<table>";
                                    bTF('table','table',$global['table']);
                                    bTF('primary_key','primary_key',$global['primary_key']);
                                    bBF('single_record','single_record',$global['single_record']);
                                    echo "</table>";
                                echo "</div>";
                                echo "<div class='section'>";
                                    echo "<p class='secheading'>FORM</p>";
                                    echo "<table>";
                                    bTF('heading','formheading',$form['heading']);
                                    bTF('introduction','formintroduction',$form['introduction']);
                                    echo "</table>";
                                    echo "<div class='section'>";
                                        echo "<p class='secheading'>CLASSES</p>";
                                        echo "<div class='section'>";
                                            echo "<p class='secheading'>DIV</p>";
                                            echo "<table>";
                                            bTF('inputtext','form_inputtext',$form['classes'] ['div'] ['inputtext']);
                                            bTF('emailtext','form_emailtext',$form['classes'] ['div'] ['emailtext']);
                                            bTF('passwordtext','form_passwordtext',$form['classes'] ['div'] ['passwordtext']);
                                            bTF('checkbox','form_checkbox',$form['classes'] ['div'] ['checkbox']);
                                            bTF('choice','form_choice',$form['classes'] ['div'] ['choice']);
                                            bTF('dropdown','form_dropdown',$form['classes'] ['div'] ['dropdown']);
                                            bTF('fk','form_fk',$form['classes'] ['div'] ['fk']);
                                            echo "</table>";
                                            echo "</div>";
                                    echo "</div>";
                                    echo "<div class='section'>";
                                    echo "<p class='secheading'>GROUPS</p>";
                                    $idx = 0;
                                    foreach($form['groups'] as $name => $group)
                                    {
                                        echo "<table>";
                                        bTF('GroupName',"form_groupname{$idx}",$name);
                                        echo "</table>";
                                        echo "<div class='section'>";
                                            echo "<p class='secheading'>{$name}</p>";
                                            echo "<table>";
                                            bTF('heading',"form_heading{$idx}",$group['heading']);
                                            bTF('introduction1',"form_introduction1{$idx}",$group['introduction1']);
                                            bTF('introduction2',"form_introduction2{$idx}",$group['introduction2']);
                                            bTF('introduction3',"form_introduction3{$idx}",$group['introduction3']);
                                            echo "</table>";
                                            echo "</div>";
                                        $idx++;
                                    }
                                    echo "</div>";
                                echo "</div>";
                                echo "<div class='section'>";
                                echo "<p class='secheading'>LIST</p>";
                                echo "<table>";
                                bTF('type',"list_type",$list['type']);
                                bBF('record_selector',"list_record_selector",$list['record_selector']);
                                bTF('heading',"list_heading",$list['heading']);
                                bTF('introduction',"list_introduction",$list['introduction']);
                                bTF('default_order',"list_default_order",$list['default_order']);
                                bTF('default_where',"list_default_where",$list['default_where']);
                                echo "</table>";
                                echo "</div>";
                                echo "<div class='section'>";
                                echo "<p class='secheading'>FIELDS</p>";
                                echo "<table>";
                                echo "<tr><th></th><th colspan='2'>DISPLAY</th></tr>";
                                echo "<tr><th>NAME</th><th>FORM</th><th>LIST</th></tr>";
                                foreach($fields as $name => $field)
                                {
                                    echo "<tr>";
                                    echo "<td><a href='FormBuilder.php?f={$g_table}:{$name}'>{$name}</a></td>";
                                    echo "<td><input type='checkbox' name='{$g_table}_{$name}_dispform'";
                                    if ($field['form'] ['display'])
                                        echo " checked ";
                                    echo "/></td>";
                                    echo "<td><input type='checkbox' name='{$g_table}_{$name}_displist'";
                                    if ($field['list'] ['display'])
                                        echo " checked ";
                                    echo "/></td>";
                                    echo "</tr>";
                                }
                                echo "</table>";
                                echo "</div>";

                                echo "<input type='hidden' name='table' value='{$g_table}'/>";
                                echo "<input type='submit' name='tableupdate' value='CONFIRM CHANGE' />";
                            echo "</form>";
                            echo "</div>";
                        }
?></div>
                <?php
                    if ($g_field)
                    {
                        echo "<div id='right2'>";
                        echo "<div class='section'>";
                        echo "<p class='secheading'>FIELD DATA FOR {$g_field}</p>";
                        echo "<form method='POST' action='{$_SERVER["PHP_SELF"]}'>";
                        echo "<table>";
                        bTF('type',"{$g_table}_{$g_field}_type",$fields[$g_field] ['type']);
                        bTF('tag',"{$g_table}_{$g_field}_tag",$fields[$g_field] ['tag']);
                        bTF('sub-tag',"{$g_table}_{$g_field}_sub-tag",$fields[$g_field] ['sub-tag']);
                        bBF('dbfield',"{$g_table}_{$g_field}_dbfield",$fields[$g_field] ['dbfield']);
                        bIF('size',"{$g_table}_{$g_field}_size",$fields[$g_field] ['size']);
                        bIF('maxlength',"{$g_table}_{$g_field}_maxlength",$fields[$g_field] ['maxlength']);
                        bIF('cols',"{$g_table}_{$g_field}_cols",$fields[$g_field] ['cols']);
                        bIF('rows',"{$g_table}_{$g_field}_rows",$fields[$g_field] ['rows']);
                        bTF('errname',"{$g_table}_{$g_field}_errname",$fields[$g_field] ['errname']);
                        bIF('security_view',"{$g_table}_{$g_field}_secuity_view",$fields[$g_field] ['security_view']);
                        bIF('security_edit',"{$g_table}_{$g_field}_security_edit",$fields[$g_field] ['security_edit']);
                        echo "</table>";
                        echo "<input type='hidden' name='table' value='{$g_table}'/>";
                        echo "<input type='hidden' name='field' value='{$g_field}'/>";
                        echo "<input type='submit' name='fieldupdate' value='CONFIRM CHANGE' />";
                        echo "</form>";
                        echo "</div>";
                        echo "</div>";
                    }
                ?>
                <?php
                    if ($g_table)
                    {
                        echo "<div id='right3'>";
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
                        echo "</div>";
                   }
                ?>
            </div>
        </div>
    </div>
</body>
</html>

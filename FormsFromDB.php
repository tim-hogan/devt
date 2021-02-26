#!/usr/bin/env php
<?php

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

function usage()
{
    echo "FormsFromDB Usage:\n";
    echo "  FormsFromDB -d <database name> -u <username> -p <password> -o <output file> \n";
    echo "  -d <Database Name>  The name of the database\n";
    echo "  -u <username>  The username of the database\n";
    echo "  -p <password>  The password of the database\n";
    echo "  -o <filename>  The output file name\n";
}


function fieldsFromTable($table)
{
    global $DB;
    $r = $DB->query("select * from {$table} limit 1");
    if ($r)
        return $r->fetch_fields();
    return null;
}


function buildDefault()
{
    global $DB;

    $tabledefs = array();
    $r = $DB->query("show tables");
    $a = $r->fetch_all(MYSQLI_NUM);
    foreach($a as $table)
    {


        $finfo = fieldsFromTable($table[0]);

        $params = array();
        $global = array();
        $form = array();

        $global['table'] = $table[0];
        $global['single_record'] = true;
        $global['selector_text'] = $table[0];

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
            $fdata["decimalplaces"] = 2;
            $fdata["currency_symbol"] = "$";
            $fdata["readonly"] = false;
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
                    $fdata['tag'] = "input";
                    $fdata['sub-tag'] = "checkbox";
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

function outputArray($a,$level=0)
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
    return $ret;
}

function output($a)
{
    $ret = "<?php\n";
    $ret .= "return [\n";
    $ret .= outputArray($a);
    $ret .= "];\n";
    $ret .= "?>";
    return $ret;
}


//Start
$options = getopt("hd:o:p:u:");

if (isset($options['h']))
{
    usage();
    exit;
}

if (!isset($options['d']))
{
    echo "ERROR: No database name specified with -d option\n";
    usage();
    exit;
}

if (!isset($options['u']))
{
    echo "ERROR: No username  specified with -u option\n";
    usage();
    exit;
}

if (!isset($options['p']))
{
    echo "ERROR: No password name specified with -p option\n";
    usage();
    exit;
}

if (!isset($options['o']))
{
    echo "ERROR: No output filespecified with -o option\n";
    usage();
    exit;
}

$DB = new mysqli('127.0.0.1',$options['u'],$options['p'],$options['d']);
file_put_contents($options['o'],output(buildDefault()));


?>
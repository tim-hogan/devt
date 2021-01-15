<?php
/**
    classForm
*/
require_once dirname(__FILE__)  . "/classSQLPlus.php";

class Form
{
    private $config;
    
    /**
        Form::__construct
        @$params
    */  
    function __construct($params)
    {
        $this->config = $params;
    }
    
    function __destruct()
    {
    }
    
    function buildList()
    {
        $ret = '';
        $ret .= "<div id='classFormlist'>";
        $ret .= "</div>";
        return $ret;
    }
    
    function buildForm($tablename,$record=null)
    {
        $ret = '';
        $ret .= "<div id='classFormForm'>";
        
        $table = $this->config['tables'] [$tablename];
        $items = $table['form'];
        foreach($items as $field => $val)
        {
            $form = $val['form'];
            if ($form['display']) 
            {
                $ret .= "<div>";
                $ret .= "<p class='phd1'>";
                if ($form['required']) 
                    $ret.= "* ";
                $ret .= "{$form['heading']}</p>";
                if ($form['htmltype'] == 'input')
                {
                    
                    $ret .= "<input "; 
                    $ret .= "class='";
                    if ($form['required'])
                        $ret .= "req ";
                    if ($form['type'] == 'number' || $form['type'] == 'double' ||  $form['type'] == 'currency')
                        $ret .= "r ";
                    
                    $ret .= "'";
                    
                    
                    $ret .= "type='{$form['htmlsubtype']}' name='{$field}_f' ";
                    
                    if (isset($form['validation']) && $form['validation'] != 'none')
                        $ret .= "devtvalidation='{$form['validation']}' ";
                    if ($form['htmltype'] == 'input')
                        $ret .= "oninput='devt.form.input(this)' ";
                    $ret .="/>";
                }
                $ret .= "</div>";
            }
        }
        $ret .= "</div>";
        return $ret;
    }
}

class FormBuilder
{
    private $params;
    private $DB = null;
    
    function __construct($dbparams,$params = null)
    {
        $this->params = $params;
        try {
            $this->DB = new SQLPlus($dbparams);
        }
        catch (Exception $e) {
            throw new Exception($e);
        }
    }
    
    function __destruct()
    {
    }
    
    public function allTables()
    {
        $ret = array();
        if ($this->DB)
        {
            $r = $this->DB->query("SHOW TABLES");
            if ($r)
            {
                while ($t = $r->fetch_array(MYSQLI_NUM) )
                    array_push($ret,$t[0]);
            }
        }
        return $ret;
    }
    
    public function getFields($table)
    {
        $ret = null;
        if ($this->DB)
        {
            $r = $this->DB->query("SELECT * from {$table} LIMIT 1");
            if ($r)
               $ret = $r->fetch_fields();
        }
        return $ret;
    }
    
    public function getParams()
    {
       return $this->params;
    }
    
    public function createdefaultfield($dbfield)
    {
        $dbtype = $dbfield->type;
        $required = boolval(intval($dbfield->flags) & MYSQLI_NOT_NULL_FLAG);
        $autoincrement = boolval(intval($dbfield->flags) & MYSQLI_AUTO_INCREMENT_FLAG);
        $primarykey = boolval(intval($dbfield->flags) & MYSQLI_PRI_KEY_FLAG);
        $uniquekey = boolval(intval($dbfield->flags) & MYSQLI_UNIQUE_KEY_FLAG);
        $multiplekey = boolval(intval($dbfield->flags) & MYSQLI_MULTIPLE_KEY_FLAG);
        $field = array();
        $field['database'] = ['dbtypenum' => $dbtype,'primarykey' => $primarykey,'not null' => $required,'unique' => $uniquekey, 'multiplekey' => $multiplekey,'autoincrement' => $autoincrement];
        switch ($dbtype)
        {
            case MYSQLI_TYPE_VAR_STRING:
                $field ['database'] ['dbtype'] = 'text';
                $field['form'] = ['display' => true, 'heading' => '','type' => 'text','htmltype' => 'input', 'htmlsubtype' => 'text', 'readonly' => false,'validation' => 'none'];
                break;
            case MYSQLI_TYPE_INT24:
            case MYSQLI_TYPE_LONG:
            case MYSQLI_TYPE_SHORT:
                $field ['database'] ['dbtype'] = 'integer';
                $field['form'] = ['display' => true, 'heading' => '','type' => 'number','htmltype' => 'input', 'htmlsubtype' => 'text', 'readonly' => false,'validation' => 'integer'];
                break;
            case MYSQLI_TYPE_FLOAT:
                $field ['database'] ['dbtype'] = 'float';
                $field['form'] = ['display' => true, 'heading' => '','type' => 'double', 'htmltype' => 'input', 'htmlsubtype' => 'text', 'readonly' => false,'validation' => 'decimal'];
                break;
            case MYSQLI_TYPE_DOUBLE:
                $field ['database'] ['dbtype'] = 'double';
                $field['form'] = ['display' => true, 'heading' => '','type' => 'double','htmltype' => 'input', 'htmlsubtype' => 'text', 'readonly' => false,'validation' => 'decimal'];
                break;
            case MYSQLI_TYPE_TINY:
                $field ['database'] ['dbtype'] = 'boolean';
                $field['form'] = ['display' => true, 'heading' => '','type' => 'boolean','htmltype' => 'input', 'htmlsubtype' => 'checkbox', 'readonly' => false,'validation' => 'boolean'];
                break;
            case MYSQLI_TYPE_DATETIME:
                $field ['database'] ['dbtype'] = 'datetime';
                $field['form'] = ['display' => true, 'heading' => '','type' => 'date','htmltype' => 'input', 'htmlsubtype' => 'date', 'readonly' => false,'validation' => 'datetime'];
                break;
            case MYSQLI_TYPE_TIMESTAMP:
                $field ['database'] ['dbtype'] = 'timestamp';
                $field['form'] = ['display' => true, 'heading' => '','type' => 'date','htmltype' => 'input', 'htmlsubtype' => 'date', 'readonly' => false,'validation' => 'datetime'];
                break;
            default:
                $field ['database'] ['dbtype'] = $dbtype;
                $field['form'] = ['display' => true, 'heading' => '','type' => 'text','htmltype' => 'input', 'htmlsubtype' => 'text', 'readonly' => false,'validation' => 'none'];
                error_log("Unkown type for {$field->name} : {$dbtype}");
        }
        if ($autoincrement)
        {
            $field['form'] ['display'] = false;  
            $field['form'] ['readonly'] = true;  
        }
        if ($required)
            $field['form'] ['required'] = true;
        else
            $field['form'] ['required'] = false;
        $field['list'] = ['display' => true];
        return $field;
    }
    
    public function buildDatabaseFields($table)
    {
        $fields = $this->getFields($table);
        $ft = array();
        $form = array();
        
        foreach ($fields as $field)
        {
            if (!isset($form[$field->name]))
            {
                $form[$field->name] = $this->createdefaultfield($field);
            }
        }
        
        $ft['form'] = $form;
        return $ft;
    }
    
    public function showList()
    {
        $f = new Form($this->params);
        return $f->buildList();
    }
    
    public function showForm()
    {
        $f = new Form($this->params);
        return $f->buildForm();
    }
}
?>
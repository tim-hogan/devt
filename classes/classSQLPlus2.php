<?php
//Version 3.0
require_once dirname(__FILE__) . '/classParseText.php';

class TableRow
{
    protected $_values;
    protected $_tabledata;

    function __construct($tabledata=null)
    {
        if ($tabledata)
            $this->_tabledata = $tabledata;
        else
            $this->_tabledata = array();
    }

    public function __get($name)
    {
        if (array_key_exists($name,$this->_tabledata) )
        {
            if (isset($this->_tabledata[$name] ["type"]))
            {
                switch ($this->_tabledata[$name] ["type"])
                {
                    case "varchar":
                    case "char":
                    case "text":
                    case "tinytext":
                    case "mediumtext":
                    case "longtext":
                        if (isset($this->_values[$name]) && $this->_values[$name] !== null)
                            return new ParseText($this->_values[$name]);
                        else
                            return new ParseText("");
                    case "int";
                        if (isset($this->_values[$name]) && $this->_values[$name] !== null)
                            return intval($this->_values[$name]);
                        else
                            return null;
                    case "double";
                        if (isset($this->_values[$name]) && $this->_values[$name] !== null)
                            return floatval($this->_values[$name]);
                        else
                            return null;
                    case "boolean";
                        if (isset($this->_values[$name]) && $this->_values[$name] !== null)
                            return boolval($this->_values[$name]);
                        else
                            return null;
                    case "datetime":
                        if (isset($this->_values[$name]) && $this->_values[$name] !== null)
                            return $this->_values[$name];
                        else
                            return null;
                    default:
                        if (isset($this->_values[$name]) && $this->_values[$name] !== null)
                            return $this->_values[$name];
                        else
                            return null;
                        break;
                }
            }
            else
                return $this->_values[$name];
        }
        else
            throw (new Exception("Invalid variable {$name}"));
    }

    public function __set($name,$v)
    {
        if (! is_array($this->_values) )
            $this->_values = array();
        $this->_values[$name] = $v;
    }
}

class SQLPlus extends mysqli
{
    private $_open = false;
    private $_sqlerr;
    private $_params;
    public $version = 1.0;

    function __construct($params)
    {
        $this->_params = $params;
        $connected = false;
        while (!$connected)
        {
            parent::__construct($params['hostname'],$params['username'],$params['password'],$params['dbname']);
            if ($this->connect_error)
            {
                if ($this->connect_errno == 2006)
                    sleep(1000);
                else
                {
                    error_log("Unable to connect to database " . $params['dbname']);
                    throw new Exception("SQL Connect error {$this->connect_error} [{$this->connect_errno}]");
                }
            }
            else
                $connected = true;
        }
        $this->_open = true;
    }

    function __destruct()
    {
        $this->close();
    }

    public function close()
    {
        if ($this->_open)
        {
            parent::close();
            $this->_open = false;
        }
    }

    private function p_var_error_log( $object=null ,$additionaltext='')
    {
        ob_start();                    // start buffer capture
        var_dump( $object );           // dump the values
        $contents = ob_get_contents(); // put the buffer into a variable
        ob_end_clean();                // end capture
        error_log("{$additionaltext} {$contents}");        // log contents of the result of var_dump( $object )
    }

    protected function sqlError($q)
    {
        $this->_sqlerr = true;
        error_log("SQL Error in class SQLPlus: " . $this->error .  " Q: " . $q);
    }

    protected function sqlPrepareError($q)
    {
        $this->_sqlerr = true;
        error_log("SQL Prepare Error in class SQLPlus: " . $this->error .  " Q: " . $q);
    }

    protected function sqlBindError($q)
    {
        $this->_sqlerr = true;
        error_log("SQL Bined Error in class SQLPlus: " . $this->error .  " Q: " . $q);
    }

    public function query($q,$flags=MYSQLI_STORE_RESULT)
    {
        if ($flags == MYSQLI_USE_RESULT)
            return parent::query($q,$flags);
        else
            throw (new Exception("ERROR: SQLPlus:: Call to mysqli::query not permitted"));
    }

    public function p_query($q,$types,...$params)
    {
        if ($s = $this->prepare($q) )
        {
            $rslt = true;
            if (strlen($types) > 0)
                $rslt = $s->bind_param($types,...$params);
            if ( $rslt )
            {
                $s->execute();
                $r = $s->get_result();
                if (!$r) {$this->sqlError($q); return null;}
                return $r;
            }
            else
                $this->sqlBindError($q);
        }
        else
            $this->sqlPrepareError($q);
        return null;
    }

    /*
    public function singlequery($q)
    {
        throw
        $r = $this->query($q);
        if (!$r) {$this->sqlError($q); return null;}
        return $r->fetch_array(MYSQLI_ASSOC);
    }
    */

    public function p_singlequery($q,$types,...$params)
    {
        if ($s = $this->prepare($q) )
        {
            if (strlen($types) > 0)
                $s->bind_param($types,...$params);
            $s->execute();
            $r = $s->get_result();
            if (!$r) {$this->sqlError($q); return null;}
            return $r->fetch_array(MYSQLI_ASSOC);
        }
        else
            $this->sqlPrepareError($q);
        return null;
    }

    public function o_singlequery($obj,$q,$types,...$params)
    {
        if ($s = $this->prepare($q) )
        {
            if (strlen($types) > 0)
                $s->bind_param($types,...$params);
            $s->execute();
            $r = $s->get_result();
            if (!$r) {$this->sqlError($q); return null;}
            return $r->fetch_object($obj);
        }
        else
            $this->sqlPrepareError($q);
        return null;
    }

    public function query_useresult($q)
    {
        return $this->query($q,MYSQLI_USE_RESULT);
    }

    /*
    public function create($q)
    {
        $r = $this->query($q);
        if (!$r) {$this->sqlError($q); return false;}
        return true;
    }
    */

    public function p_create($q,$types,...$params)
    {
        if($s = $this->prepare($q))
        {
            $rslt = true;
            if (strlen($types) > 0)
                $rslt = $s->bind_param($types,...$params);
            if( $rslt )
            {
                if (!$s->execute() )
                {
                    $this->sqlError($q);
                    return null;
                }
                return true;
            }
            else
                $this->sqlBindError($q);
        }
        else
            $this->sqlPrepareError($q);
        return false;
    }

    public function update($q)
    {
        $r = $this->query($q);
        if (!$r) {$this->sqlError($q); return false;}
        return true;
    }

    public function p_update($q,$types,...$params)
    {
        if($s = $this->prepare($q))
        {
            $rslt = true;
            if (strlen($types) > 0)
                $rslt = $s->bind_param($types,...$params);
            if( $rslt )
            {
                if (!$s->execute() )
                {
                    $this->sqlError($q);
                    return null;
                }
                return true;
            }
            else
                $this->sqlBindError($q);
        }
        else
            $this->sqlPrepareError($q);
        return false;
    }

    public function delete($q)
    {
        $r = $this->query($q);
        if (!$r) {$this->sqlError($q); return false;}
        return true;
    }

    public function p_delete($q,$types,...$params)
    {
        if($s = $this->prepare($q))
        {
            $rslt = true;
            if (strlen($types) > 0)
                $rslt = $s->bind_param($types,...$params);
            if( $rslt )
            {
                if (!$s->execute() )
                {
                    $this->sqlError($q);
                    return null;
                }
                return true;
            }
            else
                $this->sqlBindError($q);
        }
        else
            $this->sqlPrepareError($q);
        return false;
    }

    public function all($q)
    {
        $r = $this->query($q);
        if (!$r) {$this->sqlError($q); return null;}
        return $r;
    }

    public function p_all($q,$types,...$params)
    {
        if($s = $this->prepare($q))
        {
            $rslt = true;
            if (strlen($types) > 0)
                $rslt = $s->bind_param($types,...$params);
            if( $rslt )
            {
                if ($s->execute() )
                {
                    $r = $s->get_result();
                    if (!$r) {$this->sqlError($q); return null;}
                    return $r;
                }
            }
            else
                $this->sqlBindError($q);
        }
        return null;
    }

    public function alter($q)
    {
        $r = $this->query($q);
        if (!$r) {$this->sqlError($q); return false;}
        return true;
    }

    public function fieldsFromTable($table)
    {
        $r = $this->query("select * from {$table} limit 1");
        if (!$r) {$this->sqlError($q); return false;}
        return $r->fetch_fields();
    }


    public function firstFromTable($table,$key,$id)
    {
        if ($key && strlen($key) > 0)
            return $this->p_singlequery("select * from {$table} where {$key} = ? limit 1","i",$id);
        else
            return $this->singlequery("select * from {$table} limit 1");
    }

    public function getFromTable($table,$key,$id)
    {
        if ($key && strlen($key) > 0)
            return $this->p_singlequery("select * from {$table} where {$key} = ?","i",$id);
        else
            return $this->singlequery("select * from {$table}");
    }

    public function deleteFromTable($table,$key,$id)
    {
        return $this->delete("delete from {$table} where {$key} = " . intval($id));
    }

    public function rows_in_table($table,$where='')
    {
        $q = "select * from {$table} {$where}";
        $r = parent::query($q);
        if (!$r) {$this->sqlError($q); return null;}
        return $r->num_rows;
    }

    public function allFromTable($table,$where='',$order='',$limit='')
    {
       $q = "select * from ".$table." " . $where . " " . $order . " " . $limit;
       $r = parent::query($q);
       if (!$r) {$this->sqlError($q); return null;}
       return $r;
    }

    public function every($table,$where='',$order='')
    {
        $q = "select * from ".$table." " . $where . " " . $order;
        $q = trim($q);
        $r = parent::query($q);
        if (!$r) {$this->sqlError($q); return null;}
        if ($r->num_rows > 0)
            return $r->fetch_all(MYSQLI_ASSOC);
        return null;
    }

    public function update_from_array($table,$a,$whereclause)
    {
        $bstart = true;
        $q = "update " . $table . " set ";

        $keys = array_keys ($a);
        for($idx = 0;$idx < count($keys);$idx++)
        {
            if (!is_numeric($keys[$idx]) )
            {
                if (isset($a[$keys[$idx]]))
                {
                    if (!$bstart)
                        $q .= ",";
                    if (gettype($a[$keys[$idx]]) == 'boolean')
                    {
                        $q .= $keys[$idx] . " = ";
                        if ($a[$keys[$idx]])
                            $q .= "true";
                        else
                            $q .= "false";
                    }
                    else
                        $q .=  $keys[$idx] . " = '". $a[$keys[$idx]] . "'";
                    $bstart = false;
                }
            }
        }
        $q .= " " . $whereclause;
        $r = $this->query($q);
        if (!$r) {$this->sqlError($q); return false;}
        return true;
    }

    public function p_update_from_array($table,$a,$whereclause)
    {

        $bstart = true;
        $q = "update " . $table . " set ";

        $types = '';
        $val = array();
        $cnt = 0;

        $keys = array_keys ($a);
        for($idx = 0;$idx < count($keys);$idx++)
        {
            if (!is_numeric($keys[$idx]) )
            {

                if (!$bstart)
                    $q .= ",";

                if (gettype($a[$keys[$idx]]) == 'boolean')
                {
                    $q .= $keys[$idx] . " = ";
                    if ($a[$keys[$idx]])
                        $q .= "true";
                    else
                        $q .= "false";
                }
                else
                {
                    $q .=  $keys[$idx] . " = ?";

                    if (null === $a[$keys[$idx]])
                    {
                        $val[$cnt] = null;
                        $types .= "i";
                        $cnt++;
                    }
                    else
                    {
                        switch (gettype($a[$keys[$idx]]))
                        {
                            case "double":
                                $types .= "d";
                                $val[$cnt] = floatval($a[$keys[$idx]]);
                                $cnt++;
                                break;
                            case "integer":
                                $types .= "i";
                                $val[$cnt] = intval($a[$keys[$idx]]);
                                $cnt++;
                                break;
                            case "string":
                                $types .= "s";
                                $val[$cnt] = $a[$keys[$idx]];
                                $cnt++;
                                break;
                            case "boolean":
                                break;
                            default:
                                $types .= "s";
                                $val[$cnt] = $a[$keys[$idx]];
                                $cnt++;
                                break;
                        }
                    }
                }
                $bstart = false;
            }
        }


        $q .= " " . $whereclause;

        if ($cnt != strlen($types))
        {
            error_log("classSQLPlus ERROR in p_update_from_array count of bind params different from types");
            return false;
        }

        if (!$s = $this->prepare($q))
        {
            error_log("classSQLPlus ERROR in p_update_from_array Prepare error Q: {$q}");
            return false;
        }

        if ($cnt > 0)
            $s->bind_param($types,...$val);

        if (!$s->execute())
        {
            error_log("classSQLPlus ERROR in p_update_from_array failed to execute count = {$cnt} types={$types}");
            $this->sqlError($q);
            return null;
        }
        return true;
    }

    public function create_from_array($table,$a)
    {
        $bstart = true;
        $q = "insert into " . $table . "(";

        $keys = array_keys ($a);
        for($idx = 0;$idx < count($keys);$idx++)
        {
            if (!is_numeric($keys[$idx]) )
            {
                if (isset($a[$keys[$idx]]))
                {
                    if (!$bstart)
                        $q .= ",";
                    $q .=  $keys[$idx];
                    $bstart = false;
                }
            }
        }

        $q .= ") values (";
        $bstart = true;
        for($idx = 0;$idx < count($keys);$idx++)
        {
            if (!is_numeric($keys[$idx]) )
            {
                if (isset($a[$keys[$idx]]))
                {
                    if (!$bstart)
                        $q .= ",";
                    if (gettype($a[$keys[$idx]]) == 'boolean')
                    {
                        if ($a[$keys[$idx]])
                            $q .= "true";
                        else
                            $q .= "false";
                    }
                    else
                        $q .=  "'".$a[$keys[$idx]]."'";
                    $bstart = false;
                }
            }
        }
        $q .= ")";
        $r = $this->query($q);
        if (!$r) {$this->sqlError($q); return false;}
        return true;
    }

    public function p_create_from_array($table,$a)
    {
        $bstart = true;
        $q = "insert into " . $table . "(";

        $keys = array_keys ($a);
        for($idx = 0;$idx < count($keys);$idx++)
        {
            if (!is_numeric($keys[$idx]) )
            {
                if (isset($a[$keys[$idx]]))
                {
                    if (!$bstart)
                        $q .= ",";
                    $q .=  $keys[$idx];
                    $bstart = false;
                }
            }
        }

        $q .= ") values (";

        $bstart = true;
        $types = '';
        $val = array();
        $cnt = 0;

        for($idx = 0;$idx < count($keys);$idx++)
        {
            if (!is_numeric($keys[$idx]) )
            {
                if (isset($a[$keys[$idx]]))
                {
                    if (!$bstart)
                        $q .= ",";

                    if (gettype($a[$keys[$idx]]) == 'boolean')
                    {
                        if ($a[$keys[$idx]])
                            $q .= "true";
                        else
                            $q .= "false";
                    }
                    else
                    {
                        switch (gettype($a[$keys[$idx]]))
                        {
                            case "double":
                                $q .= "?";
                                $types .= "d";
                                $val[$cnt] = floatval($a[$keys[$idx]]);
                                $cnt++;
                                break;
                            case "integer":
                                $q .= "?";
                                $types .= "i";
                                $val[$cnt] = intval($a[$keys[$idx]]);
                                $cnt++;
                                break;
                            case "string":
                                $q .= "?";
                                $types .= "s";
                                $val[$cnt] = $a[$keys[$idx]];
                                $cnt++;
                                break;
                            case "boolean":
                                break;
                            default:
                                $q .= "?";
                                $types .= "s";
                                $val[$cnt] = $a[$keys[$idx]];
                                $cnt++;
                                break;
                        }
                    }
                    $bstart = false;
                }
            }
        }
        $q .= ")";

        if ($cnt != strlen($types))
        {
            error_log("classSQLPlus ERROR in p_create_from_array count of bind params different from types");
            return false;
        }

        if (!$s = $this->prepare($q))
        {
            error_log("classSQLPlus ERROR in p_create_from_array Prepare error Q: {$q} SQL Error: [{$this->errno}] {$this->error}");
            return false;
        }

        if ($cnt > 0)
            $s->bind_param($types,...$val);

        if (!$s->execute())
        {
            error_log("classSQLPlus ERROR in p_create_from_array failed to execute count = {$cnt} types={$types}");
            $this->sqlError($q);
            return false;
        }

        return true;
    }

    //Fields
    public function allFieldsFromTable($table)
    {
        $r = $this->p_query("select * from {$table} limit 1",null,null);
        if ($r)
            return $r->fetch_fields();
        return null;
    }

    public function fieldLength($table,$field)
    {
        $r = $this->p_query("select * from {$table} limit 1",null,null);
        try {
            $fields = $r->fetch_fields();
            foreach ($fields as $f)
            {
                if ($f->name == $field)
                    return $f->length;
            }
        }
        catch (Exception $e) {
            return 0;
        }
        return 0;
    }

    public function buildBlankFieldArray($table)
    {
        $r = $this->p_query("select * from {$table} limit 1",null,null);
        if ($r)
        {
            $ret = array();
            $a = $r->fetch_fields();
            foreach($a as $f)
            {
                $ret[$f->name] = null;
            }
            return $ret;
        }
        return null;
    }

    public function BeginTransaction()
    {
        $this->_sqlerr = false;
        $this->autocommit(false);
    }

    public function TransactionError()
    {
        $this->_sqlerr = true;
    }

    public function EndTransaction()
    {
        if (!$this->_sqlerr)
            $this->commit();
        else
            $this->rollback();

        $this->autocommit(true);
        $this->_sqlerr = false;
    }

    public function isTransactionError()
    {
        return $this->_sqlerr;
    }

    public function BackupToFile($dir)
    {
        $ret = array();
        $filename = $dir;
        if (substr($dir, -1) != "/" )
            $filename .= "/";
        $filename .= date('YmdHis') . ".sql";
        $fred = 'crap';
        $return_var = 0;
        $ouput = array();

        error_log("MySQL DUMP COMMAND: mysqldump --user={$this->_params['username']} --password={$this->_params['password']} {$this->_params['dbname']} > ".$filename);
        $ret['dumprslt'] = exec("mysqldump --user={$this->_params['username']} --password={$this->_params['password']} {$this->_params['dbname']} > ".$filename);
        //$ret['dumprslt'] = exec("mysqldump --user={$this->_params['username']} --password={$this->_params['password']} --host={$this->_params['hostname']} {$this->_params['dbname']} > ".$filename);
        $ret['dir'] = $dir;
        $ret['file'] = $filename;
        $ret['status'] = $return_var;
        $ret['output'] = $ouput;
        return $ret;
    }

    public function loadFromSQL($filename)
    {
        $ret = array();
        $ret['rslt'] = exec("mysql -u{$this->_params['username']} -p{$this->_params['password']} -h{$this->_params['hostname']} {$this->_params['dbname']} < {$filename}");
        return $ret;
    }

    /**
     * Summary of parseSQLTable
     * Use to parse from a sql schema file a table into an array of fields
     *
     * @param mixed $table The name of the table
     * @param mixed $sql The sql schema
     * @return string string in php synatx for retruned result.
     */
    static public function parseSQLTable($table,$sql)
    {

        $ret = '';

        $sql = strtolower($sql);
        $sql = str_replace ("`","'",$sql);

        //Replace all double spaces
        while (strpos($sql,"  ") !== false)
            $sql = str_replace("  "," ",$sql);

        //Replace all lf and cr spaces
        $sql = str_replace("\n"," ",$sql);
        $sql = str_replace("\r"," ",$sql);

        //Convert tabel name to lower case
        $table = strtolower($table);
        $from = 0;

        while (($from = strpos($sql,"create table")) !== FALSE)
        {
            $sql = substr($sql,$from+12);

            //Now read up until we find "(";
            if (($pos2 = strpos($sql,"(")) !== false)
            {
                $pos1 = strpos($sql,"'");
                $tablename = substr($sql,$pos1,$pos2-$pos1);
                $tablename = trim($tablename);
                $tablename = str_replace("'","",$tablename);
                if (strpos($tablename,".") !== false)
                {
                    $tablename = (explode(".",$tablename)) [1];
                }

                if ($tablename == $table)
                {
                    $ret = "$" . "this->_data = array(\n";
                    $sql = $sql = substr($sql,$pos2);
                    //Now we need to find the clsoing ")"
                    $depth = 0;
                    $found = false;
                    $pos2 = 0;
                    while (!$found)
                    {
                        $oldpos2 = $pos2;
                        $pos2 = strpos($sql,")",$oldpos2);
                        $pos1 = strpos($sql,"(",$oldpos2);
                        if ($pos1 !== false && $pos1 < $pos2)
                            $pos2++;
                        else
                            $found = true;
                    }
                    $allf = substr($sql,1,$pos2-1);
                    $fields = explode(",",$allf);
                    foreach($fields as $field)
                    {
                        $d = strtok(trim($field)," ");
                        if (strpos($d,"'") === 0 )
                        {
                            $name = trim($d,"'");
                            $type = strtok(" ");
                            if ( ($p1 = strpos($type,"(")) !== false)
                            {
                                $type = substr($type,0,$p1);
                            }

                            $ret .= "\"{$name}\" =>[\"type\" => \"{$type}\"],\n";
                        }
                    }
                    $ret .= ");";
                    return $ret;
                }
            }
        }
    }
}
?>
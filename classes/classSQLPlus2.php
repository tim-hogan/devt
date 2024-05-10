<?php
use Vtiful\Kernel\Format;
//Version 3.0
require_once dirname(__FILE__) . '/classParseText.php';

class TableRow
{
	protected $_values;
	protected $_tabledata;

	private $_DB;

	private $_primary_key;
	private $_result;

	function __construct($tabledata=null,$DB=null)
	{
		if ($tabledata)
			$this->_tabledata = $tabledata;
		else
			$this->_tabledata = array();
		$this->_DB = $DB;
		$this->_result = null;
		$this->_primary_key = null;
		foreach($this->_tabledata as $fieldname => $f)
		{
			if (isset($f["pk"]) && $f["pk"])
				$this->_primary_key = $fieldname;
		}
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
					case "date":
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

	public function setDatabase($DB)
	{
		$this->_DB = $DB;
		$this->_result = null;
	}

	public function create($DB=null)
	{
		//Creates a new tablerow class record
		if ($DB)
			$this->_DB = $DB;
		if (!$this->_DB)
			throw (new Exception("No database object assigend"));
		if (!$this->_values)
			throw (new Exception("No record"));
		if (!$this->_primary_key)
			throw (new Exception("No primary key declared in tabledata"));
		$tablename = $this->whoami();
		unset($this->_values[$this->_primary_key]);
		if ($this->_DB->p_create_from_array($tablename, $this->_values) )
		{
			return $this->_DB->o_singlequery($tablename, "select * from {$tablename} where {$this->_primary_key} = ?", "i", $this->_DB->insert_id);
		}
		return null;
	}

	public function update($DB=null)
	{
		if ($DB)
			$this->_DB = $DB;
		if (!$this->_DB)
			throw (new Exception("No database object assigend"));
		if (!$this->_values)
			throw (new Exception("No record"));
		if (!$this->_primary_key)
			throw (new Exception("No primary key declared in tabledata"));
		$tablename = $this->whoami();
		if ($this->_DB->p_update_from_array($tablename, $this->_values, "where {$this->_primary_key} = {$this->_values[$this->_primary_key]}"))
			return true;
		return false;
	}

	public function whoami()
	{
		return get_class($this);
	}

	public function position_head()
	{
		$this->_result = null;
	}

	public function first()
	{
		if (!$this->_DB)
			throw (new Exception("No database object assigend"));

		$tablename = $this->whoami();
		//Do we have the primary keys
		$keys = $this->_DB->getPrimaryKeysForTable($tablename);
		$q = "select * from {$tablename} order by ";
		foreach($keys as $key)
			$q .= "{$key},";
		$q = trim($q,",");
		$this->_result = $this->_DB->p_query($q,null,null);
		if ($this->_result)
			$this->_values = $this->_result->fetch_assoc();
		else
			$this->_values = null;
		return $this->_values;
	}

	public function next()
	{
		if (!$this->_DB)
			throw (new Exception("No database object assigend"));

		if ($this->_result)
			$this->_values = $this->_result->fetch_assoc();
		else
			$this->_values = $this->first();
		return $this->_values;
	}

}

class LinkList extends TableRow
{
	private $_DB;
	private $_table;
	private $_listnum;
	private $_objectname;

	function __construct($DB,$listnum,$table=null,$object=null)
	{
		$this->_DB = $DB;
		$this->_listnum = $listnum;
		$this->_table = $table;
		$this->_objectname = $object;

		parent::__construct(
			[
			"id" => ["type" => "int"],
			"list" => ["type" => "int"],
			"prev" => ["type" => "int"],
			"next" => ["type" => "int"],
			"object" => ["type" => "int"]
			]
			);

	}

	public function copy($o)
	{
		foreach (get_object_vars($o) as $key => $name)
			$this->$key = $name;
	}

	public function castAs($newClass) {
		$obj = new $newClass;
		foreach (get_object_vars($this) as $key => $name) {
			$obj->$key = $name;
		}
		return $obj;
	}

	public function getLink($id)
	{
		$r = $this->_DB->p_query("select * from {$this->_table} where list = ? and id = ?","ii",$this->_listnum,$id);
		if ($r)
			return $r->fetch_object(get_class($this),[$this->_DB,$this->_listnum]);
	}

	public function getObject()
	{
		if ($this->object > 0)
		{
			return $this->_DB->o_singlequery("{$this->_objectname}","select * from {$this->_objectname} where id{$this->_objectname} = ?","i",$this->object);
		}
		return null;
	}
	public function getHead()
	{
		$r = $this->_DB->p_query("select * from {$this->_table} where list = ? and prev is null","i",$this->_listnum);
		if ($r)
		{
			$v = $r->fetch_object(get_class($this),[$this->_DB,$this->_listnum]);
			$this->copy($v);
			return $v;
		}
	}

	public function getHeadObject()
	{
		$v = $this->getHead();
		if ($v)
			return $v->getObject();
		return null;
	}

	public function getTail()
	{
		$r = $this->_DB->p_query("select * from {$this->_table} where list = ? and next is null","i",$this->_listnum);
		if ($r)
		{
			$v = $r->fetch_object(get_class($this),[$this->_DB,$this->_listnum]);
			$this->copy($v);
			return $v;
		}
	}

	public function getTailObject()
	{
		$v = $this->getTail();
		if ($v)
			return $v->getObject();
		return null;
	}

	public function getNext()
	{
		if ($this->next)
		{
			$v = $this->getLink($this->next);
			$this->copy($v);
			return $v;
		}
		return null;
	}

	public function getNextObject()
	{
		$v = $this->getNext();
		if ($v)
			return $v->getObject();
		return null;
	}

	public function getPrev()
	{
		if ($this->prev)
		{
			$v = $this->getLink($this->prev);
			$this->copy($v);
			return $v;
		}
		return null;
	}

	public function getPrevObject()
	{
		$v = $this->getPrev();
		if ($v)
			return $v->getObject();
		return null;
	}

	public function insertTail($object_id)
	{
		$newid = null;
		$r = $this->_DB->p_query("select * from {$this->_table} where list = ? and next is null","i",$this->_listnum);
		if ($r)
		{
			if ($r->num_rows > 1)
				throw (new Exception("LinkList: More than one tail detected for list {$this->_listnum}"));

			$a = $r->fetch_assoc();

			//Now create a new one
			$this->_DB->BeginTransaction();
			if ($a)
			{
				echo " Inserting at end of last\n";
				$r = $this->_DB->p_create("insert into {$this->_table} (list,prev,next,object) values (?,?,?,?)","iiii",$this->_listnum,$a["id"],null,$object_id);
				if ($r)
				{
					$newid = $this->_DB->insert_id;
					$this->_DB->p_update("update {$this->_table} set next = ? where id=?","ii",$newid,$a["id"]);
				}
				else
					TransactionError();
			}
			else
			{
				echo " No existing inserting new\n";
				$r = $this->_DB->p_create("insert into {$this->_table} (list,prev,next,object) values (?,?,?,?)","iiii",$this->_listnum,null,null,$object_id);
				if ($r)
					$newid = $this->_DB->insert_id;
				else
					$this->_DB->TransactionError();
			}
			$this->_DB->EndTransaction();
		}

		if ($newid)
			return $this->getLink($newid);

		return null;
	}

	public function allObjects()
	{
		$ret = array();
		$o = $this->getHeadObject();
		while ($o)
		{
			$ret[] = $o;
			$o = $this->getNextObject();
		}

		//Reset the list back to the head.
		$this->getHead();
		return $ret;
	}

	public function isObejctIdInList($id)
	{
		return ($this->_DB->p_singlequery("select * from list where list = ? & object = ?","ii",$this->_listnum,$id) ) ? true : false;
	}

	public static function getNewListNumber($DB,$table)
	{
		$rec = $DB->p_singlequery("select list from {$table} order by list desc limit 1",null,null);
		if ($rec)
			return intval($rec["list"]) + 1;
		else
			return 0;
	}

	public static function enumLists($DB,$table)
	{
		$ret = array();
		$r = $DB->p_query("select list from {$table} group by list order by list",null,null);
		if ($r)
		{
			while ($rec = $r->fetch_assoc())
				$ret[] = $rec["list"];
		}
		return $ret;
	}
}

class UndoAction
{
	private $_action;

	function __construct($action,$table=null,$field=null,$value=null)
	{
		if (gettype($action) == "array")
			$this->_action = $action;
		else
			$this->_action = ["a" => $action, "t" => $table, "f" => $field, "v" => $value];
	}

	private function getValueType($v)
	{
		$b = null;
		switch (gettype($v))
		{
			case "boolean":
			case "integer":
				$b = "i";
				break;
			case "string":
				$b = "s";
				break;
			case "double":
				$b = "d";
				break;
			case "default":
				$b = "s";
		}
		return $b;
	}

	public function apply($DB)
	{
		$b = null;
		$b = $this->getValueType($this->_action["v"]);
		switch (strtolower($this->_action["a"]))
		{
			case "delete":
				$v = $this->_action['v'];
				return $DB->p_delete("delete from {$this->_action['t']} where {$this->_action['f']} = ?",$b,$v);
				break;
		}
	}

	public function dump()
	{
		$ret = "";
		$b = $this->getValueType($this->_action["v"]);
		switch (strtolower($this->_action["a"]))
		{
			case "delete":
				$ret = "delete from {$this->_action["t"]} where {$this->_action["f"]} = ";
				if ($b == "s")
					$ret .= "\"{$this->_action['v']}\"\n";
				else
					$ret .= "{$this->_action['v']}\n";
				break;
			default:
				break;
		}
		return $ret;
	}

	public function serialise()
	{
		return $this->_action;
	}
}

class Undo
{
	private $_title;
	private $_timestamp;
	private $_actions;

	function __construct($title,UndoAction $action = null)
	{
		$this->_title = $title;
		$this->_timestamp = new DateTime();
		$this->_actions = array();
		if ($action)
			$this->_actions[] = $action;
	}

	function add(UndoAction $action )
	{
		$this->_actions[] = $action;
	}

	public function undo($DB)
	{
		$DB->BeginTransaction();
		$cnt = count($this->_actions);
		for ($i = 0; $i < $cnt; $i++ )
			(array_pop($this->_actions))->apply($DB);
		return $DB->EndTransaction();
	}

	public function timestamp()
	{
		return $this->_timestamp;
	}

	public function title()
	{
		return $this->_title;
	}

	public function setTimestamp($ts)
	{
		$this->_timestamp = new DateTime();
		$this->_timestamp->setTimestamp($ts);
	}

	public function dump()
	{
		$ret = "Undo dump for {$this->_title}\n";
		$ret .= " [{$this->_timestamp->format('Y-m-d H:i:s')}\n";
		for ($i = 0; $i < count($this->_actions); $i++ )
		{
			$ret .= (array_pop($this->_actions))->dump();
		}
		return $ret;
	}

	static public function fromArray($a)
	{
		$undo = new Undo($a["title"]);
		$undo->setTimestamp($a["ts"]);
		foreach($a["actions"] as $u)
			$undo->add(new UndoAction($u));
		return $undo;
	}

	public function serialise()
	{
		$s = array();
		$s["title"] = $this->_title;
		$s["ts"] = $this->_timestamp->getTimestamp();
		$actions = array();
		foreach($this->_actions as $a)
		{
			$actions[] = $a->serialise();
		}
		$s["actions"] = $actions;
		return $s;
	}
}

class UndoList
{
	private $_list = array();

	function __construct($strJSON = null)
	{
		if ($strJSON)
		{
			$list = json_decode($strJSON,true);
			foreach($list as $l)
				$this->push(Undo::fromArray($l));
		}
	}

	public function push(Undo $undo)
	{
		$this->_list[] = $undo;
	}

	public function pop()
	{
		return array_pop($this->_list);
	}

	public function removeOldTime($seconds)
	{
		//Removes for list those itmes that were created more than $seconds earlier
		$tsNow = (new DateTime())->getTimestamp();
		for($i = 0; $i < count($this->_list);$i++)
		{
			if ( $tsNow - ($this->_list[$i]->timestamp())->getTimestamp() > $seconds)
				array_splice($this->_list, $i, 1);
		}
	}

	public function removeOldCount($count)
	{
		//Removes from list older items so the count is less or equal to count
		while (count($this->_list) > $count)
			array_pop($this->_list);
	}

	public function count()
	{
		return count($this->_list);
	}

	public function titles()
	{
		//These need to be in reveres order as it is a stack
		$ret = array();
		for ($i = count($this->_list)-1; $i >= 0; $i--)
		{
			$ret[] = $this->_list[$i]->title();
		}
		return $ret;
	}

	public function toJSON()
	{
		$a = array();
		foreach($this->_list as $l)
			$a[] = $l->serialise();
		return json_encode($a);
	}

}

class SQLPlus extends mysqli
{
	private $_open = false;
	private $_inTransaction = false;
	private $_sqlerr;
	private $_params;
	public $version = 1.0;
	public $lasterrno = 0;

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
		$strBacktrace = "";
		$backtrace = debug_backtrace();
		foreach($backtrace as $b)
			$strBacktrace .= "{$b['function']} [{$b["line"]}] {$b["file"]};";
		error_log("SQL Error in class SQLPlus: " . $this->error .  " [{$this->errno}] " . "Q: " . $q . " BACKTRACE: " . $strBacktrace);
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
				if (!$r) {$this->sqlError($q);return null;}
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
		$this->lasterrno = 0;
		if($s = $this->prepare($q))
		{
			$rslt = true;
			if (strlen($types) > 0)
				$rslt = $s->bind_param($types,...$params);
			if( $rslt ) {
				try {
					if (!$s->execute()) {
						$this->lasterrno = $this->errno;
						$this->sqlError($q);
						return null;
					}
				} catch (Exception $e) {
					echo "classSQLPlus2 exception [" . __LINE__ . "] {$e->getMessage()} \n";
					$this->lasterrno = $this->errno;
					$this->sqlError($q);
					return null;
				}
				return true;
			} else
				$this->sqlBindError($q);
		} else
			$this->sqlPrepareError($q);
		return false;
	}

	public function p_create_ignore_duplicates($q, $types, ...$params) //Ignores duplicates
	{
		$this->lasterrno = 0;
		if ($s = $this->prepare($q)) {
			$rslt = true;
			if (strlen($types) > 0)
				$rslt = $s->bind_param($types, ...$params);
			if( $rslt )
			{
				try {
					if (!$s->execute() )
					{
						$this->lasterrno = $this->errno;
						$this->sqlError($q);
						return null;
					}
				}
				catch (Exception $e) {
					if ($this->errno == 1062)  //Duplicate
						return null;
					echo "classSQLPlus2 exception [".__LINE__."] {$e->getMessage()} \n";
					$this->lasterrno = $this->errno;
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
		$r = $this->query($q,MYSQLI_USE_RESULT);
		if (!$r) {$this->sqlError($q); return false;}
		return true;
	}

	public function fieldsFromTable($table)
	{
		$q = "select * from {$table} limit 1";
		$r = $this->p_query($q,null,null);
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
			return $this->p_singlequery("select * from {$table}",null,null);
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

	public function p_every($table,$where='',$order='',$mask,...$params)
	{
		$q = "select * from {$table}";
		if (strlen($where) > 0)
			$q .= " {$where}";
		if (strlen($order) > 0)
			$q .= " {$order}";

		$r = $this->p_query($q,$mask,...$params);

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
			$this->sqlPrepareError($q);
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

	//Keys
	public function getPrimaryKeysForTable($table)
	{
		$ret = array();
		$r = $this->p_query("SHOW KEYS FROM {$table} WHERE Key_name = 'PRIMARY'",null,null);
		while ($row = $r->fetch_assoc())
		{
			$ret[] = $row["Column_name"];
		}
		return $ret;
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
		if (! $this->_inTransaction)
		{
			$this->_inTransaction = true;
			$this->_sqlerr = false;
			$this->autocommit(false);
		}
	}

	public function TransactionError()
	{
		$this->_sqlerr = true;
	}

	public function EndTransaction()
	{
		if ($this->_inTransaction)
		{
			$err = $this->_sqlerr;
			if (!$err)
				$this->commit();
			else
				$this->rollback();

			$this->autocommit(true);
			$this->_sqlerr = false;
			return $err ? false : true;
		}
		return false;
	}

	public function isTransactionError()
	{
		return $this->_sqlerr;
	}

	public function hasTableIndexOnField($table,$field)
	{
		$r = $this->p_query("show index from {$table} where column_name = '{$field}'",null,null);
		if ($r->num_rows > 0)
			return true;
		return false;
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
<?php
/**
 * Class CCollection for managing a collection of associated row items
 * @author Tim Hogan
 * @version 1.0
*/
class CCollection
{
	protected $_keys = null;
	protected $_data = null;
	protected $_idx = -1;
	private $_null = null;
	private $_sortKey = null;

	//*************************************************************************************************
	//Constructor
	//*************************************************************************************************
	function __construct(array $data)
	{
		if ($data)
		{
			if (!is_array($data))
				throw new Exception("CCollection::construct data parameter not an array");
			if (count($data) > 0)
			{
				$this->_keys = array_keys($data[0]);
				if (array_search("__idx__", $this->_keys) === false)
					$this->_keys[] = "__idx__";
				$idx = 0;
				foreach($data as $d)
				{
					$d["__idx__"] = $idx++;
					$this->_data[] = $d;
				}
			}
		}
	}

	//*************************************************************************************************
	// Private Functions
	//*************************************************************************************************
	private function reIndex()
	{
		for ($idx = 0; $idx < count($this->_data); $idx++) {
			$this->_data[$idx]["__idx__"] = $idx;
		}
	}

	private function sort_asc($a, $b)
	{
		return $a[$this->_sortKey] <=> $b[$this->_sortKey];
	}

	private function sort_desc($a, $b)
	{
		$v = $a[$this->_sortKey] <=> $b[$this->_sortKey];
		if ($v == 0)
			return $v;
		return ($v == 1) ? -1 : 1;
	}

	//*************************************************************************************************
	// Public Functions
	//*************************************************************************************************
	public function create(array $keys)
	{
		if (!is_array($keys))
			throw new Exception("CCollection::create keys parameter not an array");
		$this->_keys = $keys;
		if (array_search("__idx__",$this->_keys) === false)
			$this->_keys[] = "__idx__";
	}

	public function addRow(array $row)
	{
		if (!is_array($row))
			throw new Exception("CCollection::addRow row parameter not an array");
		$this->_data[] = $row;
		return count($this->_data) - 1;
	}

	public function addColumn($name, $value = null)
	{
		if (array_search($name,$this->_keys) === false)
		{
			$this->_keys[] = $name;
			for($idx = 0; $idx < count($this->_data);$idx++)
			{
				$this->_data[$idx][$name] = null;
			}
		}
		if ($value)
		{
			//We add to the current record
			if ($this->_idx >= 0 && $this->_idx < count($this->_data))
				$this->_data[$this->_idx][$name] = $value;
		}
	}

	/**
	 * Summary of count
	 * @return int
	 */
	public function count()
	{
		return count($this->_data);
	}

	/**
	 * Summary of current
	 * @return int  The current position in the list.  If -1 then no position
	 */
	public function current() : int
	{
		return $this->_idx;
	}

	/**
	 * Summary of columns
	 * @return array
	 */
	public function columns()
	{
		return $this->_keys;
	}

	public function every()
	{
		return $this->_data;
	}

	/**
	 * Summary of get
	 * @param int $pos
	 * @return mixed
	 */

	public function &get(int $pos)
	{
		if ($pos !== null && $pos >= 0 && $pos < count($this->_data))
			return $this->_data[$pos];
		return $this->_null;
	}

	public function oget(int $pos,$class=null)
	{
		if ($pos !== null && $pos >= 0 && $pos < count($this->_data))
		{
			if ($class)
			{
				$o = new $class;
				foreach($this->_data[$pos] as $key => $value)
				{
					$o->{$key} = $value;
				}
				return $o;
			}
			else
				return (object) $this->_data[$pos];
		}
		return null;
	}

	/**
	 * Summary of reset
	 * This will reset the record pointer back to the beginning.
	 */
	public function reset()
	{
		$this->_idx = -1;
	}

	public function &first()
	{
		if (count($this->_data) > 0)
		{
			$this->_idx = 0;
			return $this->_data[$this->_idx];
		}
		return $this->_null;
	}

	public function &next($pos = null)
	{
		if ($pos)
			$this->_idx = $pos;
		$this->_idx++;
		if ($this->_idx >= 0 && $this->_idx < count($this->_data))
			return $this->_data[$this->_idx];
		return $this->_null;
	}


	public function &last()
	{
		if (count($this->_data) > 0)
		{
			$this->_idx = count($this->_data) - 1;
			return $this->_data[$this->_idx];
		}
		return $this->_null;
	}

	public function &prev($pos = null)
	{
		if ($pos)
			$this->_idx = $pos;
		$this->_idx--;
		if ($this->_idx >= 0 && $this->_idx < count($this->_data))
			return $this->_data[$this->_idx];
		return $this->_null;
	}

	public function __get($name)
	{
		if (array_search($name,$this->_keys) !== false)
		{
			if ($this->_idx >= 0 && $this->_idx < count($this->_data))
			{
				if (isset($this->_data[$this->_idx][$name]))
					return $this->_data[$this->_idx] [$name];
			}
		}
		return null;
	}

	public function __set($name, $value)
	{
		if (array_search($name, $this->_keys) !== false)
		{
			if ($this->_idx >= 0 && $this->_idx < count($this->_data))
			{
				$this->_data[$this->_idx][$name] = $value;
			}
		}
	}

	/**
	 * Summary of find
	 * @param mixed $column
	 * @param mixed $value
	 * @return mixed
	 */
	public function &find($column, $value)
	{
		$idx = 0;
		for ($idx = 0; $idx < count($this->_data); $idx++) {
			if (isset($this->_data[$idx][$column]) && $this->_data[$idx][$column] == $value)
			{
				$this->_idx = $idx;
				return $this->_data[$idx];
			}
		}
		return $this->_null;
	}

	/**
	 * Summary of sort
	 * @param string $key
	 * @param string $direction  "asc" | "desc"
	 */
	public function sort(string $key,string $direction)
	{
		$this->_sortKey = $key;
		if ($direction == "asc")
		{
			usort($this->_data, array($this, "sort_asc"));
			$this->reIndex();
		} elseif ($direction == "desc") {
			usort($this->_data, array($this, "sort_desc"));
			$this->reIndex();
		} else
			throw new Exception("CCollection::sort direction of sort not set");
	}

	public function blend($records, $key, $items)
	{
		$idx = 0;
		for ($idx = 0; $idx < count($this->_data);$idx++)
		{
			if ($records->find($key,$this->_data[$idx] [$key]) )
			{
				foreach($items as $item)
				{
					$this->_data[$idx] [$item] = $records->__get($item);
				}
			}
		}
		foreach($items as $item)
		{
			$this->_keys [] = $item;
		}
	}

	public function exportCSV($filename)
	{
		$str = "";
		$f = fopen($filename, "w");
		if ($f)
		{
			foreach ($this->_keys as $k)
				$str .= "{$k},";
			$str = trim($str, ",") . "\r\n";
			fwrite($f, $str);

			foreach($this->_data as $r)
			{
				$str = "";
				foreach ($this->_keys as $k)
				{
					$str .= "\"{$r[$k]}\",";
				}
				$str = trim($str, ",") . "\r\n";
				fwrite($f, $str);
			}
			fclose($f);
			return true;
		}
		return false;
	}

	public function exportJSON()
	{
		return json_encode($this->_data);
	}
}
?>
<?php
class CSVRec
{
	private $_filename = null;
	private $_keys = array();
	private $_records = array();

	private $_idx = -1;

	private $_null = null;

	public function __construct($filename = null,$subsetname=null,$subsetvalue=null)
	{
		if ($filename)
		{
			$this->_filename = $filename;
			if (! file_exists($filename))
				throw new Exception('File {$filename} does not exist');

			$f = fopen($filename, "r");
			if (!$f)
				throw new Exception('Unable to open {$filename} in read mode');

			$recid = 0;
			while (($row = fgetcsv($f, 32000)) !== false)
			{
				if (empty($this->_keys))
				{
					$this->_keys = $row;
					$this->_keys[0] = $this->removeUnicodeString($this->_keys[0]);
					$this->_keys[] = "__idx__";
					continue;
				}

				$d = array();
				$idx = 0;
				foreach ($this->_keys as $key)
				{
					if (isset($row[$idx]))
						$d[$key] = $row[$idx];
					else
						$d[$key] = null;
					$idx++;
				}

				$d["__idx__"] = $recid;

				if ($subsetname && $subsetvalue) {
					if (isset($d[$subsetname]) && $d[$subsetname] == $subsetvalue) {
						$this->_records[] = $d;
						$recid++;
					}
				}
				else
				{
					$this->_records[] = $d;
					$recid++;
				}
			}
		}
	}

	private function removeUnicodeString($s)
	{
		if (substr(bin2hex($s), 0, 6) == "efbbbf")
			return substr($s, 3);
		else
			return $s;
	}

	public function __get($name)
	{
		if (array_search($name,$this->_keys) !== false)
		{
			if ($this->_idx >= 0)
			{
				if (isset($this->_records[$this->_idx][$name]))
					return $this->_records[$this->_idx] [$name];
			}
		}
		return null;
	}

	public function __set($name,$value)
	{
		if (array_search($name,$this->_keys) !== false)
		{
			if ($this->_idx >= 0)
			{
				$this->_records[$this->_idx][$name] = $value;
			}
		}
	}

	public function count()
	{
		return count($this->_records);
	}

	public function columns()
	{
		return $this->_keys;
	}

	public function addColumn($key,$value=null)
	{
		if (array_search($key, $this->_keys) === false)
		{
			$this->_keys[] = $key;

			//Update all records
			for ($idx = 0; $idx < count($this->_records); $idx++)
				$this->_records[$idx][$key] = null;
		}

		if ($this->_idx >= 0 && $value !== null)
		{
			$this->_records[$this->_idx] [$key] = $value;
			return $this->_records[$this->_idx];
		}
		return null;
	}

	public function resetPosition()
	{
		$this->_idx = -1;
	}

	public function &getRecord($idx)
	{
		if ($idx >= 0 && $idx < count($this->_records))
			return $this->_records[$idx];
		return $this->_null;
	}

	public function &first()
	{
		$this->_idx = 0;
		if ($this->_idx < count($this->_records))
			return $this->_records[$this->_idx];
		return $this->_null;
	}

	public function &last()
	{
		$this->_idx = count($this->_records) -1;
		if ($this->_idx >= 0)
			return $this->_records[$this->_idx];
		return $this->_null;
	}

	public function &next($current_idx=null)
	{
		if ($current_idx !== null)
			$this->_idx = $current_idx + 1;
		else
			$this->_idx++;
		if ($this->_idx < count($this->_records))
			return $this->_records[$this->_idx];
		return $this->_null;
	}

	public function &prev($current_idx=null)
	{
		if ($current_idx !== null)
			$this->_idx = $current_idx-1;
		else
			$this->_idx--;
		if ($this->_idx >= 0 )
			return $this->_records[$this->_idx];
		return $this->_null;
	}

	public function every()
	{
		return $this->_records;
	}

	public function &find($column,$value)
	{
		$idx = 0;
		for ($idx = 0; $idx < count($this->_records);$idx++)
		{
			if (isset($this->_records[$idx] [$column]) && $this->_records[$idx] [$column] == $value)
			{
				$this->_idx = $idx;
				return $this->_records[$idx];
			}
		}
		return $this->_null;
	}

	public function blend($csvrecs,$key,$items)
	{
		$recidx = 0;
		for($recidx = 0;$recidx < count($this->_records);$recidx++)
		{
			if ( $csvrecs->find($key,$this->_records[$recidx] [$key]) )
			{
				foreach($items as $item)
				{
					$this->_records[$recidx] [$item] = $csvrecs->__get($item);
				}
			}
		}
		foreach($items as $item)
		{
			$this->_keys [] = $item;
		}
	}

	public function saveas($filename)
	{
		$str = "";
		$f = fopen($filename, "w");
		foreach ($this->_keys as $k)
			$str .= "{$k},";
		$str = trim($str, ",") . "\r\n";
		fwrite($f, $str);

		foreach($this->_records as $r)
		{
			$str = "";
			foreach ($this->_keys as $k)
			{
				$str .= "{$r[$k]},";
			}
			$str = trim($str, ",") . "\r\n";
			fwrite($f, $str);
		}
		fclose($f);
	}
}
?>
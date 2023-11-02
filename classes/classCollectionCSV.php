<?php
require_once dirname(__FILE__) . '/classCollection.php';

class CSVCollection extends CCollection
{
	private $_filename = null;

	public function __construct($filename = null,$subsetname=null,$subsetvalue=null)
	{
		if ($filename)
		{
			$data = array();
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
					$this->_keys[0] = self::removeUnicodeString($this->_keys[0]);
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

				if ($subsetname && $subsetvalue) {
					if (isset($d[$subsetname]) && $d[$subsetname] == $subsetvalue) {
						$data[] = $d;
						$recid++;
					}
				}
				else
				{
					$data[] = $d;
					$recid++;
				}
			}
			parent::__construct($data);
		}
	}

	//****************************************************************************************************
	// Private functions
	//****************************************************************************************************

	static public function removeUnicodeString($s)
	{
		if (substr(bin2hex($s), 0, 6) == "efbbbf")
			return substr($s, 3);
		else
			return $s;
	}

	static public function fileColumnKeys($filename)
	{
		$keys = array();

		if (!file_exists($filename))
			throw new Exception('File {$filename} does not exist');

		$f = fopen($filename, "r");
		if (!$f)
				throw new Exception('Unable to open {$filename} in read mode');

		while (($row = fgetcsv($f, 32000)) !== false)
		{
			if (empty($keys))
			{
				$keys = $row;
				$keys[0] = self::removeUnicodeString($keys[0]);
				break;
			}
		}
		return $keys;
	}

	static public function enumerateAll($filename, $key)
	{
		$ret = array();
		$keys = array();

		if (!file_exists($filename))
			throw new Exception('File {$filename} does not exist');

		$f = fopen($filename, "r");
		if (!$f)
			throw new Exception('Unable to open {$filename} in read mode');

		while (($row = fgetcsv($f, 32000)) !== false) {
			if (empty($keys)) {
				$keys = $row;
				$keys[0] = self::removeUnicodeString($keys[0]);
				continue;
			}


			if (array_search($key, $keys) === false)
				return null;
			$idx = 0;
			foreach ($keys as $k)
			{
				if (isset($row[$idx]))
					$d[$k] = $row[$idx];
				else
					$d[$k] = null;
				$idx++;
			}

			if (isset($d[$key]) && array_search($d[$key], $ret) === false)
				$ret[] = $d[$key];
		}
		return $ret;
	}

	static public function firstRow($filename, $key,$value)
	{
		$keys = array();

		if (!file_exists($filename))
			throw new Exception('File {$filename} does not exist');

		$f = fopen($filename, "r");
		if (!$f)
			throw new Exception('Unable to open {$filename} in read mode');

		while (($row = fgetcsv($f, 32000)) !== false)
		{
			if (empty($keys)) {
				$keys = $row;
				$keys[0] = self::removeUnicodeString($keys[0]);
				continue;
			}


			if (array_search($key, $keys) === false)
				return null;

			$idx = 0;
			foreach ($keys as $k) {
				if (isset($row[$idx]))
					$d[$k] = $row[$idx];
				else
					$d[$k] = null;
				$idx++;
			}

			if (isset($d[$key]) && $d[$key] == $value)
				return $d;
		}
		return null;

	}

	static public function stats($filename,$column,$subsetcolumn = null,$subsetvalue = null)
	{
		$ret = array();
		$keys = array();
		$haveStats = false;

		if (!file_exists($filename))
			throw new Exception('File {$filename} does not exist');

		$f = fopen($filename, "r");
		if (!$f)
			throw new Exception('Unable to open {$filename} in read mode');

		$ret["min"] = PHP_FLOAT_MAX;
		$ret["max"] = PHP_FLOAT_MIN;
		$ret["cnt"] = 0;
		$ret["sum"] = 0;

		while (($row = fgetcsv($f, 32000)) !== false)
		{
			if (empty($keys)) {
				$keys = $row;
				$keys[0] = self::removeUnicodeString($keys[0]);
				continue;
			}

			if ($column && array_search($column, $keys) === false)
				return null;

			$idx = 0;
			foreach ($keys as $k)
			{
				if (isset($row[$idx]))
					$d[$k] = $row[$idx];
				else
					$d[$k] = null;
				$idx++;
			}

			if (!$subsetcolumn || $d[$subsetcolumn] == $subsetvalue)
			{
				if ($ret["min"] > $d[$column])
					$ret["min"] = $d[$column];
				if ($ret["max"] < $d[$column])
					$ret["max"] = $d[$column];
				$ret["cnt"]++;
				$ret["sum"] += $d[$column];
				$haveStats = true;
			}
		}
		if ($haveStats)
			return $ret;
		return null;
	}
}
?>
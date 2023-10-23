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
					$this->_keys[0] = $this->removeUnicodeString($this->_keys[0]);
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

	private function removeUnicodeString($s)
	{
		if (substr(bin2hex($s), 0, 6) == "efbbbf")
			return substr($s, 3);
		else
			return $s;
	}

}
?>
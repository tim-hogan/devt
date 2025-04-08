<?php
require_once "./includes/classTime.php";
class FormList
{
	private $config;

	function __construct($params)
	{
		if ($params)
			$this->config = $params;
	}

	static public function var_error_log( $object=null,$text='')
	{
		ob_start();
		var_dump( $object );
		$contents = ob_get_contents();
		ob_end_clean();
		error_log( "{$text} {$contents}" );
	}

	public function getConfiguration()
	{
		if ($this->config)
			return $this->config;
		return null;
	}

	public function getForm()
	{
		if ($this->config)
		{
			if (isset($this->config["form"]))
				return $this->config["form"];
		}
		return null;
	}

	private function isVariable($v)
	{
		$s = trim($v);
		if (substr($s,0,1) == "{" && substr($s,strlen($s)-1,1) == "}")
			return true;
		return false;
	}

	private function haveParameterText($a,$v)
	{
		if (isset($a[$v]) && strlen($a[$v]) > 0)
			return true;
		return false;
	}

	private function haveParameterBoolean($a,$v)
	{
		if (isset($a[$v]) && $a[$v] )
			return true;
		return false;
	}

	private function getVariable($a,$v)
	{
		$s = trim($v);
		$s = trim($s,"{");
		$s = trim($s,"}");
		$s = trim($s);
		if (isset ($a[$s]))
			return $a[$s];
		return "";
	}

	private function parseVariable($str,$data)
	{
		if ($data)
		{
			if (($start = strpos($str,"{")) !== false)
			{
				if (($end = strpos($str,"}",$start)) !== false)
				{
					$v = substr($str,$start,($end-$start)+1);
					$v = $this->getVariable($data,$v);
					$ret = substr($str,0,$start) . $v . substr($str,$end+1);
					return $ret;
				}
			}
		}
		return $str;
	}

	static public function getField($f,$trimit=true)
	{
		$data = null;
		if (isset($_POST[$f]))
		{
			$data = $_POST[$f];
			if ($trimit)
				$data = trim($data);
			$data = stripslashes($data);
			$data = strip_tags(htmlspecialchars_decode($data));
		}
		return $data;
	}

	static public function getIndexField($f,$idx,$trimit=true)
	{
		$data = null;
		if (isset($_POST[$f]) && isset($_POST[$f] [$idx]))
		{
			$data = $_POST[$f] [$idx];
			if ($trimit)
				$data = trim($data);
			$data = stripslashes($data);
			$data = strip_tags(htmlspecialchars_decode($data));
		}
		return $data;
	}

	static public function getIntegerField($f,$trimit=true)
	{
		$data = null;
		if (isset($_POST[$f]))
			$data = intval(FormList::getField($f,$trimit));
		return $data;
	}

	static public function getIntegerIndexField($f,$idx, $trimit = true)
	{
		$data = null;
		if (isset($_POST[$f]))
			$data = intval(FormList::getIndexField($f, $idx,$trimit));
		return $data;
	}

	static public function getDecimalField($f,$trimit=true)
	{
		$data = null;
		if (isset($_POST[$f]))
		{
			$data = FormList::getField($f,$trimit);
			$data = str_replace(",", "", $data);
			$data = floatval($data);
		}
		return $data;
	}

	static public function getDecimalIndexField($f,$idx,$trimit = true)
	{
		$data = null;
		if (isset($_POST[$f]))
			$data = floatval(FormList::getIndexField($f, $idx,$trimit));
		return $data;
	}

	static public function getPercentField($f,$trimit=true)
	{
		$data = null;
		if (isset($_POST[$f]))
			$data = FormList::getField($f,$trimit);
		if (strpos($data,"%") !== false)
		{
			$data = str_replace("%","",$data);
			$data = floatval($data) / 100.0;
		}
		else
			$data = floatval($data);
		return $data;
	}

	static public function getCurrencyField($f,$trimit=true,$symbol="$")
	{
		$data = null;
		if (isset($_POST[$f]))
		{
			$data = FormList::getField($f,$trimit);
			$data = str_replace($symbol,"",$data);
			$data = str_replace(",","",$data);
			$data = floatval($data);
		}
		return $data;
	}

	static public function getCurrencyIndexField($f, $idx,$trimit = true, $symbol = "$")
	{
		$data = null;
		if (isset($_POST[$f])) {
			$data = FormList::getIndexField($f, $idx,$trimit);
			$data = str_replace($symbol, "", $data);
			$data = str_replace(",", "", $data);
			$data = floatval($data);
		}
		return $data;
	}

	static public function getIndexedCurrencyField($f,$idx,$trimit=true,$symbol="$")
	{
		$data = null;
		if (isset($_POST[$f] [$idx]))
		{
			$data = FormList::getIndexField($f,$idx,$trimit);
			$data = str_replace($symbol,"",$data);
			$data = str_replace(",","",$data);
			$data = floatval($data);
		}
		return $data;
	}

	static public function getDateField($f,$trimit=true)
	{
		//Uses $_SESSION['tz'] or $_SESSION['timezone']
		//$tz = 'UTC';
		//if (isset($_SESSION['tz']))
		//    $tz = $_SESSION['tz'];
		//elseif (isset($_SESSION['timezone']))
		//    $tz = $_SESSION['timezone'];
		if (isset($_POST[$f]))
		{
			$data = FormList::getField($f,$trimit);
			$data = substr($data, 0, 10);
			$date = new DateTime($data ."00:00:00");
			return $date->format('Y-m-d');
		}
		return null;
	}

	static public function getDateFieldWithTimezone($f, $tz='UTC', $trimit = true)
	{
		if (isset($_POST[$f]))
		{
			$data = FormList::getField($f, $trimit);
			if (strlen($data) > 0)
			{
				$date = new DateTime($data);
				$date->setTimezone(new DateTimeZone($tz));
				return $date->format('Y-m-d H:i:s');
			}
		}
		return null;
	}

	static public function getDateTimeField($f,$trimit=true)
	{
		//Uses $_SESSION['tz'] or $_SESSION['timezone']
		$tz = 'UTC';
		if (isset($_SESSION['tz']))
			$tz = $_SESSION['tz'];
		elseif (isset($_SESSION['timezone']))
			$tz = $_SESSION['timezone'];
		if (isset($_POST[$f]))
		{
			$data = FormList::getField($f,$trimit);
			$date = new DateTime($data,new DateTimeZone($tz));
			$date->setTimezone(new DateTimeZone('UTC'));
			return $date->format('Y-m-d H:i:s');
		}
		return null;

	}

	static public function getCheckboxField($f)
	{
		if (isset($_POST[$f]))
		{
			if (strtoupper($_POST[$f]) == "ON")
				return true;
		}
		return false;
	}

	public function value($f)
	{
		if (! $this->config)
			throw new Exception(__FILE__ . "[" . __LINE__ ."] FormList has not been constructed with parameters" );

		if (! isset ($this->config['fields']) )
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No fields are sepcified in parameters" );

		$fields = $this->config['fields'];
		if (isset($fields[$f]) && isset($fields[$f] ['value']) )
			return $fields[$f] ['value'];
		return null;
	}

	public function setFieldValue($f,$v)
	{
		if (! $this->config)
			throw new Exception(__FILE__ . "[" . __LINE__ ."] FormList has not been constructed with parameters" );

		if (! isset ($this->config['fields']) )
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No fields are sepcified in parameters" );

		if (!isset($this->config['fields'] [$f]) )
			$this->config['fields'] [$f] = array();
		$this->config['fields'] [$f] ['value'] = $v;

	}

	public function haserror($f)
	{
		if (! $this->config)
			throw new Exception(__FILE__ . "[" . __LINE__ ."] FormList has not been constructed with parameters" );

		if (! isset ($this->config['fields']) )
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No fields are sepcified in parameters" );

		$fields = $this->config['fields'];
		if (isset($fields[$f]) && isset($fields[$f] ['error']) )
			return $fields[$f] ['error'];
		return false;
	}

	public function setFieldError($f)
	{
		if (! $this->config)
			throw new Exception(__FILE__ . "[" . __LINE__ ."] FormList has not been constructed with parameters" );

		if (! isset ($this->config['fields']) )
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No fields are sepcified in parameters" );

		if (isset($this->config['fields'] [$f]) )
			$this->config['fields'] [$f] ["error"] = true;
	}

	public function errormessage($f)
	{
		if (! $this->config)
			throw new Exception(__FILE__ . "[" . __LINE__ ."] FormList has not been constructed with parameters" );

		if (! isset ($this->config['fields']) )
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No fields are sepcified in parameters" );

		$fields = $this->config['fields'];
		if (isset($fields[$f]) && isset($fields[$f] ['error_reason']) )
			return $fields[$f] ['error_reason'];
		return "";
	}

	public function fieldsWithError()
	{
		$ret = array();

		if (! $this->config)
			throw new Exception(__FILE__ . "[" . __LINE__ ."] FormList has not been constructed with parameters" );

		if (! isset ($this->config['fields']) )
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No fields are sepcified in parameters" );

		$fields = $this->config['fields'];
		foreach ($fields as $name => $field)
		{
			if (isset($field['error']) && $field['error'])
			{
				$strName = "";
				if (isset($field['errname']) )
					$strName = $field['errname'];

				$ret[$name] = array();
				$ret[$name] ['name'] = $strName;
				if ( isset($field['form']['errtext']) && strlen($field['form']['errtext']) > 0)
					$ret[$name] ['reason'] = $field['form'] ['errtext'];
				else
					$ret[$name] ['reason'] = $field['error_reason'];
			}
		}
		return $ret;
	}

	public function getFormInputFields()
	{
		$valid=true;

		if (! $this->config)
			throw new Exception(__FILE__ . "[" . __LINE__ ."] FormList has not been constructed with parameters" );

		if (! isset ($this->config['fields']) )
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No fields are sepcified in parameters" );

		$fields = $this->config['fields'];
		foreach($fields as $name => $field)
		{
			if (isset($field['form']) && isset($field['form'] ['display']) && $field['form'] ['display'] )
			{
				$trim = true;
				if (isset($field ['form'] ['trim']) && ! $field ['form'] ['trim'])
					$trim = false;

				switch ($this->config['fields'] [$name] ["type"])
				{
					case "boolean":
						switch ($this->config['fields'] [$name] ["sub-tag"])
						{
							case "checkbox":
								$this->config['fields'] [$name] ["value"] = FormList::getCheckboxField($name . "_f");
								break;
						}
						break;
					case "integer":
						$this->config['fields'] [$name] ["value"] = FormList::getIntegerField($name . "_f",$trim);
						break;
					case "decimal":
						$this->config['fields'] [$name] ["value"] = FormList::getDecimalField($name . "_f",$trim);
						break;
					case "currency":
						$symbol = "$";
						if (isset($this->config['fields'] [$name] ['currency_symbol'] ))
							$symbol = $this->config['fields'] [$name] ['currency_symbol'];
						$this->config['fields'] [$name] ["value"] = FormList::getCurrencyField($name . "_f",$trim,$symbol);
						break;
					case "percent":
						$this->config['fields'] [$name] ["value"] = FormList::getPercentField($name . "_f",$trim);
						break;
					case "date":
						$this->config['fields'] [$name] ["value"] = FormList::getDateField($name . "_f",$trim);
						break;
					case "datetime":
						$this->config['fields'] [$name] ["value"] = FormList::getDateTimeField($name . "_f",$trim);
						break;
					case "choice":
						$this->config['fields'] [$name] ["value"] = FormList::getField($name . "_f",$trim);
						break;
					case "text":
					   $this->config['fields'] [$name] ["value"] = FormList::getField($name . "_f",$trim);
					   break;
					case "dropdown":
						$this->config['fields'] [$name] ["value"] = FormList::getField($name . "_f",$trim);
						break;
					case "fk":
						$this->config['fields'] [$name] ["value"] = FormList::getIntegerField($name . "_f",$trim);
						break;
					case "hidden":
						$hiddenValue = FormList::getField($name . "_f",false);
						$decode = FormList::decryptParamRaw($hiddenValue);
						$this->config['fields'] [$name] ["value"] = $decode['hidden'];
						break;

				}

				//Check required
				if ( isset($field['form'] ['required']) && $field['form'] ['required'] )
				{
					if (!isset($this->config['fields'] [$name] ["value"]) || strlen($this->config['fields'] [$name] ["value"]) == 0)
					{
						$this->config['fields'] [$name] ["error"] = true;
						$errorText = '';
						if (isset($field['form'] ['formlabel']) && strlen($field['form'] ['formlabel'] ) > 0)
							$errorText = $field['form'] ['formlabel'] . ": ";
						$errorText .= "Entry is required";
						$this->config['fields'] [$name] ["error_reason"] = $errorText;
						$valid = false;
					}
				}
			}
		}

		return $valid;
	}

	public function AddRecord($DB)
	{
		if (! $this->config)
			throw new Exception(__FILE__ . "[" . __LINE__ ."] FormList has not been constructed with list parameters" );

		if (! isset ($this->config['fields']) )
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No fields are sepcified in parameters" );

		if (!isset($this->config['global']))
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No globals sepcified in parameters" );

		if (!isset($this->config['global'] ['table'] ))
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No table sepcified in global section of parameters" );

		$fields = $this->config['fields'];
		$row = array();
		foreach($fields as $name => $field)
		{
			if (! isset($field['dbfield']) || (isset($field['dbfield']) && $field['dbfield']) )
			{
				if (isset($field['value']))
					$row[$name] = $field['value'];
				if ($field["type"] == "fk" && $field['value'] == 0)
					$row[$name] = null;
			}
		}

		return $DB->p_create_from_array($this->config['global'] ['table'],$row);
	}

	public function DeleteRecord($DB,$id)
	{

		if (! $this->config)
			throw new Exception(__FILE__ . "[" . __LINE__ ."] FormList has not been constructed with list parameters" );

		if (! isset ($this->config['fields']) )
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No fields are sepcified in parameters" );

		if (!isset($this->config['global']))
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No globals sepcified in parameters" );

		if (!isset($this->config['global'] ['table'] ))
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No table sepcified in global section of parameters" );

		if (!isset($this->config['global'] ['primary_key'] ))
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No primary ket set for table" );

		$table = $this->config['global'] ['table'];
		$fields = $this->config['fields'];
		$pk = $this->config['global'] ['primary_key'];

		if (isset($fields["{$table}_deleted"]))
		{
			return $DB->p_update("update {$table} set {$table}_deleted = 1 where {$pk} = ?","i",$id );
		}
		else
		{
			return $DB->p_delete("delete from {$table} where {$pk} = ?","i",$id );
		}

	}

	public function ModifyRecord($DB,$id)
	{
		if (! $this->config)
			throw new Exception(__FILE__ . "[" . __LINE__ ."] FormList has not been constructed with list parameters" );

		if (! isset ($this->config['fields']) )
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No fields are sepcified in parameters" );

		if (!isset($this->config['global']))
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No globals sepcified in parameters" );

		if (!isset($this->config['global'] ['table'] ))
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No table sepcified in global section of parameters" );

		$fields = $this->config['fields'];
		$row = array();

		foreach($fields as $name => $field)
		{
			if (! isset($field['dbfield']) || (isset($field['dbfield']) && $field['dbfield']) )
			{
				if (isset($field['value']))
				{
					if ($field["type"] == "fk" && intval($field['value']) == 0)
						$row[$name] = null;
					else
						$row[$name] = $field['value'];
				}
			}
		}

		if ($id == -99)
			return $DB->p_update_from_array($this->config['global'] ['table'],$row,"");
		else
			return $DB->p_update_from_array($this->config['global'] ['table'],$row,"where {$this->config['global'] ['primary_key']} = {$id}");
	}

	private function buildTextField($n,$f,$data=null)
	{
		$fid = $n . "_id";
		$divid = $n . "_divid";
		$fname = $n ."_f";
		$tag = 'input';

		if (isset($this->config['form']))
		{
			$form = $this->config['form'];
			if (isset($form['classes']))
			{
				$formclasses = $form['classes'];
				if (isset($formclasses['div']))
					$formclassesdiv = $formclasses['div'];
			}
		}

		if (isset($f['tag']))
			$tag = $f['tag'];

		echo "<div id='{$divid}'";
		switch ($tag)
		{
			case "input":
				$subtag = "text";
				if (isset($f['sub-tag']))
					$subtag = $f['sub-tag'];

				switch ($subtag)
				{
					case "text";
						if ($formclassesdiv && isset($formclassesdiv['inputtext']))
							echo " class='{$formclassesdiv['inputtext']}'";
						break;
					case "email";
						if ($formclassesdiv && isset($formclassesdiv['emailtext']))
							echo " class='{$formclassesdiv['emailtext']}'";
						break;
					case "password";
						if ($formclassesdiv && isset($formclassesdiv['passwordtext']))
							echo " class='{$formclassesdiv['passwordtext']}'";
						break;
				}
				break;
			case "textarea":
				if ($formclassesdiv && isset($formclassesdiv['textarea']))
					echo " class='{$formclassesdiv['textarea']}'";
				break;
			default:
				break;
		}
		echo ">";

		$prefix = "";
		if (isset($f ['form'] ['required']) && $f ['form'] ['required'])
			$prefix="* ";
		if (isset($f ['form'] ['formlabel']))
			echo "<label for='{$fid}'>{$prefix}{$f ['form'] ['formlabel']}</label>";

		//Default values
		if (! isset ($f['value']))
		{
			if (isset($f['form'] ['default']))
			{
				if ($this->isVariable($f['form'] ['default']) )
				{
					$f['value'] = $this->getVariable($data,$f['form'] ['default']);
				}
				else
					$f['value'] = $f['form'] ['default'];
			}
		}



		switch ($tag)
		{
			case "input":
				$subtag = "text";
				if (isset($f['sub-tag']))
					$subtag = $f['sub-tag'];
				echo "<input ";
				if (isset($f['error']) && $f['error'])
				{
					echo "class='err'";
				}
				echo "type='{$subtag}' id='{$fid}' name='{$fname}'";
				if (isset ($f['value']))
				{
					$v = htmlspecialchars($f['value']);
					echo "value='{$v}' ";
				}
				if (isset($f['size']))
					echo " size='{$f['size']}' ";
				if (isset ($f['form'] ['title']) && strlen($f['form'] ['title'] ) > 0)
					echo "title='{$f['form'] ['title']}' ";
				if (isset($f['readonly']) && $f['readonly'])
					echo "readonly ";
				if (isset($f['form'] ['onchange']))
					echo "onchange='{$f['form'] ['onchange']}' ";
				echo " />";
				break;
			case "textarea":
				echo "<textarea id='{$fid}' name='{$fname}'";
				if (isset($f['cols']))
					echo " cols='{$f['cols']}' ";
				if (isset($f['rows']))
					echo " rows='{$f['rows']}' ";
				if (isset ($f['form'] ['title']) && strlen($f['form'] ['title'] ) > 0)
					echo "title='{$f['form'] ['title']}' ";
				if (isset($f['readonly']) && $f['readonly'])
					echo "readonly ";
				echo " >";
				if (isset ($f['value']))
				{
					$v = htmlspecialchars($f['value']);
					echo $v;
				}
				echo "</textarea>";
				break;
		}

		//Check for post text
		if ( isset ($f['form'] ['posttext']) && strlen($f['form'] ['posttext']) > 0)
		{
			$v = $f['form'] ['posttext'];
			if ($data && $this->isVariable($v))
			{
				$v = $this->getVariable($data,$v);
			}
			echo "<span>{$v}</span>";
		}

		echo "</div>";
	}

	private function buildBoolField($n,$f)
	{
		$fid = $n . "_id";
		$divid = $n . "_divid";
		$fname = $n ."_f";
		$tag = 'input';

		if (isset($this->config['form']))
		{
			$form = $this->config['form'];
			if (isset($form['classes']))
			{
				$formclasses = $form['classes'];
				if (isset($formclasses['div']))
					$formclassesdiv = $formclasses['div'];
			}
		}

		if (isset($f['tag']))
			$tag = $f['tag'];

		echo "<div id='{$divid}'";
		switch ($tag)
		{
			case "input":
				$subtag = "checkbox";
				if (isset($f['sub-tag']))
					$subtag = $f['sub-tag'];

				switch ($subtag)
				{
					case "checkbox";
						if ($formclassesdiv && isset($formclassesdiv['checkbox']))
							echo " class='{$formclassesdiv['checkbox']}'";
						break;
				}
				break;
			default:
				break;
		}
		echo ">";

		switch ($tag)
		{
			case "input":
				echo "<input ";
				$subtag = "checkbox";
				if (isset($f['sub-tag']))
					$subtag = $f['sub-tag'];
				if (isset($f['error']) && $f['error'])
				{
					echo "class='err' ";
				}
				echo "type='{$subtag}' id='{$fid}' name='{$fname}' ";
				if (isset ($f['value']) && $f['value'])
				{
					echo "checked ";
				}
				else
				{
					if (isset($f['form'] ['default']) && strlen($f['form'] ['default']) > 0)
					{
						$f['value'] = true;
						echo "checked ";
					}
				}



				if (isset($f['readonly']) && $f['readonly'])
					echo "readonly ";
				if (isset($f['form'] ['onchange']))
					echo "onchange='{$f['form'] ['onchange']}' ";
				echo " />";
				if (isset($f ['form'] ['formlabel']))
					echo "<span>{$f ['form'] ['formlabel']}</span>";
				break;
		}
		echo "</div>";
	}

	private function buildIntegerField($n,$f,$data=null)
	{
		$fid = $n . "_id";
		$divid = $n . "_divid";
		$fname = $n ."_f";
		$tag = 'input';

		if (isset($this->config['form']))
		{
			$form = $this->config['form'];
			if (isset($form['classes']))
			{
				$formclasses = $form['classes'];
				if (isset($formclasses['div']))
					$formclassesdiv = $formclasses['div'];
			}
		}

		if (isset($f['tag']))
			$tag = $f['tag'];

		echo "<div id='{$divid}'";
		if ($formclassesdiv && isset($formclassesdiv['inputtext']))
			echo " class='{$formclassesdiv['inputtext']}'";
		echo ">";

		$prefix = "";
		if (isset($f ['form'] ['required']) && $f ['form'] ['required'])
			$prefix="* ";
		if (isset($f ['form'] ['formlabel']))
			echo "<label for='{$fid}'>{$prefix}{$f ['form'] ['formlabel']}</label>";

		//Default values
		if (! isset ($f['value']))
		{
			if (isset($f['form'] ['default']))
			{
				if ($this->isVariable($f['form'] ['default']) )
				{
					$f['value'] = $this->getVariable($data,$f['form'] ['default']);
				}
				else
					$f['value'] = $f['form'] ['default'];
			}
		}

		$subtag = "text";
		if (isset($f['sub-tag']))
			$subtag = $f['sub-tag'];
		echo "<input class='integer";
		if (isset($f['error']) && $f['error'])
			echo " err'";
		else
			echo "'";
		echo "type='{$subtag}' id='{$fid}' name='{$fname}'";
		if (isset ($f['value']))
		{
			$v = intval($f['value']);
			echo "value='{$v}' ";
		}
		if (isset($f['size']))
			echo " size='{$f['size']}' ";
		if (isset ($f['form'] ['title']) && strlen($f['form'] ['title'] ) > 0)
			echo "title='{$f['form'] ['title']}' ";
		if (isset($f['readonly']) && $f['readonly'])
			echo "readonly ";
		if (isset($f['form'] ['onchange']))
			echo "onchange='{$f['form'] ['onchange']}' ";
		echo " />";


		//Check for post text
		if ( isset ($f['form'] ['posttext']) && strlen($f['form'] ['posttext']) > 0)
		{
			$v = $f['form'] ['posttext'];
			if ($data && $this->isVariable($v))
			{
				$v = $this->getVariable($data,$v);
			}
			echo "<span>{$v}</span>";
		}

		echo "</div>";
	}

	private function buildDecimalField($n,$f,$data=null)
	{
		$fid = $n . "_id";
		$divid = $n . "_divid";
		$fname = $n ."_f";
		$tag = 'input';

		if (isset($this->config['form']))
		{
			$form = $this->config['form'];
			if (isset($form['classes']))
			{
				$formclasses = $form['classes'];
				if (isset($formclasses['div']))
					$formclassesdiv = $formclasses['div'];
			}
		}

		if (isset($f['tag']))
			$tag = $f['tag'];

		echo "<div id='{$divid}'";
		if ($formclassesdiv && isset($formclassesdiv['inputtext']))
			echo " class='{$formclassesdiv['inputtext']}'";
		echo ">";

		$prefix = "";
		if (isset($f ['form'] ['required']) && $f ['form'] ['required'])
			$prefix="* ";
		if (isset($f ['form'] ['formlabel']))
			echo "<label for='{$fid}'>{$prefix}{$f ['form'] ['formlabel']}</label>";

		//Default values
		if (! isset ($f['value']))
		{
			if (isset($f['form'] ['default']))
			{
				if ($this->isVariable($f['form'] ['default']) )
				{
					$f['value'] = $this->getVariable($data,$f['form'] ['default']);
				}
				else
					$f['value'] = $f['form'] ['default'];
			}
		}

		$subtag = "text";
		if (isset($f['sub-tag']))
			$subtag = $f['sub-tag'];
		echo "<input class='decimal";
		if (isset($f['error']) && $f['error'])
			echo " err'";
		else
			echo "'";
		echo "type='{$subtag}' id='{$fid}' name='{$fname}'";
		if (isset ($f['value']))
		{
			$v = floatval($f['value']);
			$places = 2;
			if (isset($f['decimalplaces']))
				$places = intval($f['decimalplaces']);
			$v = number_format($v,$places);
			echo "value='{$v}' ";
		}
		if (isset($f['size']))
			echo " size='{$f['size']}' ";
		if (isset ($f['form'] ['title']) && strlen($f['form'] ['title'] ) > 0)
			echo "title='{$f['form'] ['title']}' ";
		if (isset($f['readonly']) && $f['readonly'])
			echo "readonly ";
		if (isset($f['form'] ['onchange']))
			echo "onchange='{$f['form'] ['onchange']}' ";
		echo " />";


		//Check for post text
		if ( isset ($f['form'] ['posttext']) && strlen($f['form'] ['posttext']) > 0)
		{
			$v = $f['form'] ['posttext'];
			if ($data && $this->isVariable($v))
			{
				$v = $this->getVariable($data,$v);
			}
			echo "<span>{$v}</span>";
		}

		echo "</div>";
	}

	private function buildPercentField($n,$f,$data=null)
	{
		$fid = $n . "_id";
		$divid = $n . "_divid";
		$fname = $n ."_f";
		$tag = 'input';

		if (isset($this->config['form']))
		{
			$form = $this->config['form'];
			if (isset($form['classes']))
			{
				$formclasses = $form['classes'];
				if (isset($formclasses['div']))
					$formclassesdiv = $formclasses['div'];
			}
		}

		if (isset($f['tag']))
			$tag = $f['tag'];

		echo "<div id='{$divid}'";
		if ($formclassesdiv && isset($formclassesdiv['inputtext']))
			echo " class='{$formclassesdiv['inputtext']}'";
		echo ">";

		$prefix = "";
		if (isset($f ['form'] ['required']) && $f ['form'] ['required'])
			$prefix="* ";
		if (isset($f ['form'] ['formlabel']))
			echo "<label for='{$fid}'>{$prefix}{$f ['form'] ['formlabel']}</label>";

		//Default values
		if (! isset ($f['value']))
		{
			if (isset($f['form'] ['default']))
			{
				if ($this->isVariable($f['form'] ['default']) )
				{
					$f['value'] = $this->getVariable($data,$f['form'] ['default']);
				}
				else
					$f['value'] = $f['form'] ['default'];
			}
		}

		$subtag = "text";
		if (isset($f['sub-tag']))
			$subtag = $f['sub-tag'];
		echo "<input class='decimal";
		if (isset($f['error']) && $f['error'])
			echo " err'";
		else
			echo "'";
		echo "type='{$subtag}' id='{$fid}' name='{$fname}'";
		if (isset ($f['value']))
		{
			$v = floatval($f['value']) * 100.0;
			$places = 2;
			if (isset($f['decimalplaces']))
				$places = intval($f['decimalplaces']);
			$v = number_format($v,$places);
			echo "value='{$v}%' ";
		}
		if (isset($f['size']))
			echo " size='{$f['size']}' ";
		if (isset ($f['form'] ['title']) && strlen($f['form'] ['title'] ) > 0)
			echo "title='{$f['form'] ['title']}' ";
		if (isset($f['readonly']) && $f['readonly'])
			echo "readonly ";
		if (isset($f['form'] ['onchange']))
			echo "onchange='{$f['form'] ['onchange']}' ";
		echo " />";


		//Check for post text
		if ( isset ($f['form'] ['posttext']) && strlen($f['form'] ['posttext']) > 0)
		{
			$v = $f['form'] ['posttext'];
			if ($data && $this->isVariable($v))
			{
				$v = $this->getVariable($data,$v);
			}
			echo "<span>{$v}</span>";
		}

		echo "</div>";
	}

	private function buildCurrencyField($n,$f,$data=null)
	{
		$fid = $n . "_id";
		$divid = $n . "_divid";
		$fname = $n ."_f";
		$tag = 'input';

		if (isset($this->config['form']))
		{
			$form = $this->config['form'];
			if (isset($form['classes']))
			{
				$formclasses = $form['classes'];
				if (isset($formclasses['div']))
					$formclassesdiv = $formclasses['div'];
			}
		}

		if (isset($f['tag']))
			$tag = $f['tag'];

		echo "<div id='{$divid}'";
		if ($formclassesdiv && isset($formclassesdiv['inputtext']))
			echo " class='{$formclassesdiv['inputtext']}'";
		echo ">";

		$prefix = "";
		if (isset($f ['form'] ['required']) && $f ['form'] ['required'])
			$prefix="* ";
		if (isset($f ['form'] ['formlabel']))
			echo "<label for='{$fid}'>{$prefix}{$f ['form'] ['formlabel']}</label>";

		//Default values
		if (! isset ($f['value']))
		{
			if (isset($f['form'] ['default']))
			{
				if ($this->isVariable($f['form'] ['default']) )
				{
					$f['value'] = $this->getVariable($data,$f['form'] ['default']);
				}
				else
					$f['value'] = $f['form'] ['default'];
			}
		}

		$subtag = "text";
		if (isset($f['sub-tag']))
			$subtag = $f['sub-tag'];
		echo "<input class='currency";
		if (isset($f['error']) && $f['error'])
			echo " err'";
		else
			echo "'";
		echo "type='{$subtag}' id='{$fid}' name='{$fname}'";
		if (isset ($f['value']))
		{
			$v = floatval($f['value']);
			$places = 2;
			if (isset($f['decimalplaces']))
				$places = intval($f['decimalplaces']);
			$v = number_format($v,$places);
			$currencySymbol = "$";
			if (isset($f['currency_symbol']))
				$currencySymbol = $f['currency_symbol'];
			echo "value='{$currencySymbol}{$v}' ";
		}
		if (isset($f['size']))
			echo " size='{$f['size']}' ";
		if (isset ($f['form'] ['title']) && strlen($f['form'] ['title'] ) > 0)
			echo "title='{$f['form'] ['title']}' ";
		if (isset($f['readonly']) && $f['readonly'])
			echo "readonly ";
		if (isset($f['form'] ['onchange']))
			echo "onchange='{$f['form'] ['onchange']}' ";
		echo " />";


		//Check for post text
		if ( isset ($f['form'] ['posttext']) && strlen($f['form'] ['posttext']) > 0)
		{
			$v = $f['form'] ['posttext'];
			if ($data && $this->isVariable($v))
			{
				$v = $this->getVariable($data,$v);
			}
			echo "<span>{$v}</span>";
		}

		echo "</div>";
	}

	private function buildDateField($n,$f,$data=null)
	{
		$fid = $n . "_id";
		$divid = $n . "_divid";
		$fname = $n ."_f";
		$tag = 'input';

		if (isset($this->config['form']))
		{
			$form = $this->config['form'];
			if (isset($form['classes']))
			{
				$formclasses = $form['classes'];
				if (isset($formclasses['div']))
					$formclassesdiv = $formclasses['div'];
			}
		}

		if (isset($f['tag']))
			$tag = $f['tag'];

		echo "<div id='{$divid}'";
		if ($formclassesdiv && isset($formclassesdiv['inputtext']))
			echo " class='{$formclassesdiv['inputtext']}'";
		echo ">";

		$prefix = "";
		if (isset($f ['form'] ['required']) && $f ['form'] ['required'])
			$prefix="* ";
		if (isset($f ['form'] ['formlabel']))
			echo "<label for='{$fid}'>{$prefix}{$f ['form'] ['formlabel']}</label>";

		//Default values
		if (! isset ($f['value']))
		{
			if (isset($f['form'] ['default']))
			{
				if ($this->isVariable($f['form'] ['default']) )
				{
					$f['value'] = $this->getVariable($data,$f['form'] ['default']);
				}
				else
					$f['value'] = $f['form'] ['default'];
			}
		}

		$subtag = "date";
		echo "<input class='date";
		if (isset($f['error']) && $f['error'])
			echo " err'";
		else
			echo "'";
		echo "type='{$subtag}' id='{$fid}' name='{$fname}'";
		if (isset ($f['value']))
		{
			$tz = 'UTC';
			//This relies on the $_SESSION Variable tz or timezone
			if (isset($_SESSION['tz']))
				$tz = $_SESSION['tz'];
			elseif (isset($_SESSION['timezone']))
				$tz = $_SESSION['timezone'];

			$v = classTimeHelpers::timeFormat($f['value'],'Y-m-d',$tz);

			echo "value='{$v}' ";
		}
		if (isset($f['size']))
			echo " size='{$f['size']}' ";
		if (isset ($f['form'] ['title']) && strlen($f['form'] ['title'] ) > 0)
			echo "title='{$f['form'] ['title']}' ";
		if (isset($f['readonly']) && $f['readonly'])
			echo "readonly ";
		if (isset($f['form'] ['onchange']))
			echo "onchange='{$f['form'] ['onchange']}' ";
		echo " />";


		//Check for post text
		if ( isset ($f['form'] ['posttext']) && strlen($f['form'] ['posttext']) > 0)
		{
			$v = $f['form'] ['posttext'];
			if ($data && $this->isVariable($v))
			{
				$v = $this->getVariable($data,$v);
			}
			echo "<span>{$v}</span>";
		}

		echo "</div>";
	}

	private function buildDateTimeField($n,$f,$data=null)
	{
		$fid = $n . "_id";
		$divid = $n . "_divid";
		$fname = $n ."_f";
		$tag = 'input';

		if (isset($this->config['form']))
		{
			$form = $this->config['form'];
			if (isset($form['classes']))
			{
				$formclasses = $form['classes'];
				if (isset($formclasses['div']))
					$formclassesdiv = $formclasses['div'];
			}
		}

		if (isset($f['tag']))
			$tag = $f['tag'];

		echo "<div id='{$divid}'";
		if ($formclassesdiv && isset($formclassesdiv['inputtext']))
			echo " class='{$formclassesdiv['inputtext']}'";
		echo ">";

		$prefix = "";
		if (isset($f ['form'] ['required']) && $f ['form'] ['required'])
			$prefix="* ";
		if (isset($f ['form'] ['formlabel']))
			echo "<label for='{$fid}'>{$prefix}{$f ['form'] ['formlabel']}</label>";

		//Default values
		if (! isset ($f['value']))
		{
			if (isset($f['form'] ['default']))
			{
				if ($this->isVariable($f['form'] ['default']) )
				{
					$f['value'] = $this->getVariable($data,$f['form'] ['default']);
				}
				else
					$f['value'] = $f['form'] ['default'];
			}
		}

		$subtag = "datetime-local";
		echo "<input class='date";
		if (isset($f['error']) && $f['error'])
			echo " err'";
		else
			echo "'";
		echo "type='{$subtag}' id='{$fid}' name='{$fname}'";
		if (isset ($f['value']))
		{
			$tz = 'UTC';
			//This relies on the $_SESSION Variable tz or timezone
			if (isset($_SESSION['tz']))
				$tz = $_SESSION['tz'];
			elseif (isset($_SESSION['timezone']))
				$tz = $_SESSION['timezone'];

			$v = classTimeHelpers::timeFormatDateTimeLocal($f['value'],$tz);

			echo "value='{$v}' ";
		}
		if (isset($f['size']))
			echo " size='{$f['size']}' ";
		if (isset ($f['form'] ['title']) && strlen($f['form'] ['title'] ) > 0)
			echo "title='{$f['form'] ['title']}' ";
		if (isset($f['readonly']) && $f['readonly'])
			echo "readonly ";
		if (isset($f['form'] ['onchange']))
			echo "onchange='{$f['form'] ['onchange']}' ";
		echo " />";


		//Check for post text
		if ( isset ($f['form'] ['posttext']) && strlen($f['form'] ['posttext']) > 0)
		{
			$v = $f['form'] ['posttext'];
			if ($data && $this->isVariable($v))
			{
				$v = $this->getVariable($data,$v);
			}
			echo "<span>{$v}</span>";
		}

		echo "</div>";
	}
	private function buildChoiceField($n,$f,$data=null)
	{
		$fid = $n . "_id";
		$divid = $n . "_divid";
		$classid = $n ."_class";
		$fname = $n ."_f";
		$tag = 'radio';

		if (isset($this->config['form']))
		{
			$form = $this->config['form'];
			if (isset($form['classes']))
			{
				$formclasses = $form['classes'];
				if (isset($formclasses['div']))
					$formclassesdiv = $formclasses['div'];
			}
		}

		if (isset($f['tag']))
			$tag = $f['tag'];

		echo "<div id='{$divid}'";
		switch ($tag)
		{
			case "input":
				$subtag = "radio";

				switch ($subtag)
				{
					case "radio";
						if ($formclassesdiv && isset($formclassesdiv['choice']))
							echo " class='{$formclassesdiv['choice']}'";
						break;
				}
				break;
			default:
				break;
		}
		echo ">";

		if (isset($f['form']))
		{

				$form = $f['form'];
				if (isset($form['display']) && $form['display'] && isset($form['choice']))
				{
					if (isset($form['formlabel']))
					{
						$strLabel = htmlspecialchars($form['formlabel']);
						if ($form['required'])
							$strLabel = "* " . $strLabel;
						echo "<label for='{$fid}'>{$strLabel}</label>";
					}

				   $choice = $form['choice'];
				   $cnt = 0;
				   foreach ($choice as $radio)
				   {
					   echo "<div>";
					   echo "<input id='{$fid}_{$cnt}' class='{$classid}' type='radio' name='{$fname}' value='{$radio['value']}'";


					   if (isset($radio['selected']) && strlen($radio['selected']) > 0)
					   {

						   echo " onchange='{$radio['selected']}'";
					   }
					   if (! isset($f['value']) && $cnt == 0)
						   echo " checked";
					   if (isset ($f['value']) && $f['value'] == $radio['value'])
					   {
						   echo " checked";
					   }
					   echo " />";
					   echo "<span>{$radio['text']}</span><br />";
					echo "</div>";
					   $cnt++;
				   }
				}
		}
		echo "</div>";
	}

	private function buildDropdownField($n,$f,$data=null)
	{
		$fid = $n . "_id";
		$divid = $n . "_divid";
		$classid = $n ."_class";
		$fname = $n ."_f";

		if (isset($this->config['form']))
		{
			$form = $this->config['form'];
			if (isset($form['classes']))
			{
				$formclasses = $form['classes'];
				if (isset($formclasses['div']))
					$formclassesdiv = $formclasses['div'];
			}
		}


		echo "<div id='{$divid}'";
		if ($formclassesdiv && isset($formclassesdiv['dropdown']))
			echo " class='{$formclassesdiv['dropdown']}'";
		echo " >";

		if (isset($f['form']))
		{

			$form = $f['form'];
			if (isset($form['display']) && $form['display'])
			{
				if (isset($form['formlabel']))
				{
					$strLabel = htmlspecialchars($form['formlabel']);
					if ($form['required'])
						$strLabel = "* " . $strLabel;
					echo "<label for='{$fid}'>{$strLabel}</label>";
				}

				echo "<select id='{$fid}' class='{$classid}' name='{$fname}'>";
				if (isset($f['dropdownvalues']))
				{
					$drop_values = $f['dropdownvalues'];
					$ignoreDefault = false;
					if (isset($f['value']) )
						$ignoreDefault = true;
					foreach ($drop_values as $dropv)
					{
						$selected="";
						if ($ignoreDefault && $f['value'] == $dropv['value'])
							$selected="selected";
						if (! $ignoreDefault && isset($dropv['default']) && $dropv['default'])
							$selected="selected";
						echo "<option value='{$dropv['value']}' {$selected}>{$dropv['text']}</option>";
					}
				}
				echo "</select>";
			}
		}
		echo "</div>";

	}

	public function buildFKField($n,$f,$data,$DB)
	{
		$fid = $n . "_id";
		$divid = $n . "_divid";
		$classid = $n ."_class";
		$fname = $n ."_f";

		if (isset($this->config['form']))
		{
			$form = $this->config['form'];
			if (isset($form['classes']))
			{
				$formclasses = $form['classes'];
				if (isset($formclasses['div']))
					$formclassesdiv = $formclasses['div'];
			}
		}


		echo "<div id='{$divid}'";
		if ($formclassesdiv && isset($formclassesdiv['fk']))
			echo " class='{$formclassesdiv['fk']}'";
		echo " >";

		if (isset($f['form']))
		{

			$form = $f['form'];
			if (isset($form['display']) && $form['display'])
			{
				if (isset($form['formlabel']))
				{
					$strLabel = htmlspecialchars($form['formlabel']);
					if ($form['required'])
						$strLabel = "* " . $strLabel;
					echo "<label for='{$fid}'>{$strLabel}</label>";
				}

				if (isset($f['readonly']) && $f['readonly'])
				{
					$where = '';
					$order = '';
					if (isset($f['fk_where']))
						$where = trim($where);
					if (isset($f['fk_order']))
						$order = trim($order);
					if ($DB)
					{
						$d = $DB->every($f['fk_table'],$where,$order);
						foreach ($d as $a)
						{
							if (isset($f['value']) && $f['value'] == $a[$f['fk_index']])
							{
								$strV = htmlspecialchars($a[$f['fk_display']]);
								echo "<input id='{$fid}' type='text' name='invalid_{$fname}' value='{$strV}' readonly disbaled / >";
								echo "<input type='hidden' name='{$fname}' value='{$a[$f['fk_index']]}' />";
							}
						}
					}
				}
				else
				{

					echo "<select id='{$fid}' class='{$classid}' name='{$fname}'>";
					if (isset($f['fk_table']) && isset($f['fk_index']) && isset($f['fk_display']))
					{
						$where = '';
						$order = '';
						if (isset($f['fk_where']))
							$where = trim($where);
						if (isset($f['fk_order']))
							$order = trim($order);
						if ( ! isset($form['required']) ||  ! $form['required'])
							echo "<option value='0'></option>";
						if ($DB)
						{
							$where = '';
							$order = '';
							if (isset($f['fk_where']) && strlen($f['fk_where']) > 0)
								$where = $f['fk_where'];
							if (isset($f['fk_order']) && strlen($f['fk_order']) > 0)
								$order = $f['fk_order'];
							$d = $DB->every($f['fk_table'],$where,$order);
							foreach ($d as $a)
							{
								$selected = '';
								if (isset($f['value']) && $f['value'] == $a[$f['fk_index']])
									$selected = 'selected';
								$strV = htmlspecialchars($a[$f['fk_display']]);
								echo "<option value='{$a[$f['fk_index']]}' {$selected}>{$strV}</option>";
							}
						}
					}
					echo "</select>";
				}
			}
		}
		echo "</div>";
	}

	private function buildHiddenField($n,$f,$data=null)
	{
		$fid = $n . "_id";
		$divid = $n . "_divid";
		$fname = $n ."_f";
		$tag = 'input';

		if (isset($this->config['form']))
		{
			$form = $this->config['form'];
			if (isset($form['classes']))
			{
				$formclasses = $form['classes'];
				if (isset($formclasses['div']))
					$formclassesdiv = $formclasses['div'];
			}
		}

		//Default values
		if (isset($f['form'] ['default']))
		{
			//For hidden fields we encrypt
			$defValue =null;
			if ($this->isVariable($f['form'] ['default']) )
			{
				$defValue = $this->getVariable($data,$f['form'] ['default']);
			}
			else
			{
				$defValue = $f['form'] ['default'];
			}
			$defValue = "hidden={$defValue}";
			$f['value'] = FormList::encryptParam($defValue);
		}

		$subtag = "hidden";
		echo "<input ";
		echo "type='{$subtag}' id='{$fid}' name='{$fname}'";
		if (isset ($f['value']))
		{
			$v = $f['value'];
			echo "value='{$v}' ";
		}
		echo " />";



		//Check for post text
		if ( isset ($f['form'] ['posttext']) && strlen($f['form'] ['posttext']) > 0)
		{
			$v = $f['form'] ['posttext'];
			if ($data && $this->isVariable($v))
			{
				$v = $this->getVariable($data,$v);
			}
			echo "<span>{$v}</span>";
		}

	}

	public function getTableData($DB,$recid)
	{
		if (! $this->config)
			throw new Exception(__FILE__ . "[" . __LINE__ ."] FormList has not been constructed with parameters" );

		if (! isset ($this->config['fields']) )
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No fields are sepcified in parameters" );

		$global = $this->config['global'];
		$table = $global['table'];
		$pk = null;
		if (isset($global['primary_key']))
			$pk = $global['primary_key'];
		$rec = $DB->getFromTable($table,$pk,$recid);

		$fields = $this->config['fields'];
		foreach($fields as $name => $field)
		{
			if (isset($rec[$name]))
			{
				$this->config['fields'] [$name] ['value'] = $rec[$name];
			}
		}
	}

	public function buildFormFields($data=null,$DB=null)
	{
		$form = null;
		$formclasses = null;
		$formclassesdiv = null;


		if (! $this->config)
			throw new Exception(__FILE__ . "[" . __LINE__ ."] FormList has not been constructed with parameters" );

		if (! isset ($this->config['fields']) )
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No fields are sepcified in parameters" );

		if (isset($this->config['form']))
			$form = $this->config['form'];
		if (isset($form['classes']))
			$formclasses = $form['classes'];
		if (isset($formclasses['div']))
			$formclassesdiv = $formclasses['div'];

		//Build the form heading
		if ($form)
		{
			if (isset($form['heading']))
			{
				echo "<div id='formheading'>";
				echo "<h1>".htmlspecialchars($form['heading'])."</h1>";
				echo "</div>";
			}

			if (isset($form['introduction1']) && strlen($form['introduction1']) > 0)
			{
				echo "<div id='formheadingintroduction'>";
				echo "<p>".htmlspecialchars($form['introduction1'])."</p>";
				if (isset($form['introduction2']) && strlen($form['introduction2']) > 0)
				{
					echo "<p>".htmlspecialchars($form['introduction2'])."</p>";
					if (isset($form['introduction3']) && strlen($form['introduction3']) > 0)
					{
						echo "<p>".htmlspecialchars($form['introduction3'])."</p>";
					}
				}
				echo "</div>";
			}
		}


		$lastgroup = '';
		$fields = $this->config['fields'];
		foreach($fields as $name => $field)
		{
			if (isset($field['form']) && isset($field['form'] ['display']) && $field['form'] ['display'] )
			{
				if (! isset($field['type']) )
					throw new Exception(__FILE__ . "[" . __LINE__ ."] Field type not set for field {$name}" );

				//Is this a new group
				if (isset($field['form'] ['group']) && $form && isset($form['groups'] [$field['form'] ['group']] ))
				{
					$groupname = $field['form'] ['group'];
					$group = $form ['groups'] [$groupname];
					if ($groupname != $lastgroup)
					{
						if (strlen($lastgroup) > 0)
							echo "</div>";
						echo "<div class='formgroup'>";

						if ( isset($group['heading']) && strlen($group['heading']) > 0 )
							echo "<p class='formgroupname'>{$group['heading']}</p>";
						if ( isset ($group['introduction']) && strlen($group['introduction']) > 0 )
						{
							$introtext = htmlspecialchars($group['introduction']);
							echo "<p class='formgroupintro'>{$introtext}</p>";
						}


						$lastgroup = $groupname;
					}
				}



				switch ($field['type'])
				{
					case "boolean":
						$this->buildBoolField($name,$field);
						break;
					case "integer":
						$this->buildIntegerField($name,$field);
						break;
					case "decimal":
						$this->buildDecimalField($name,$field);
						break;
					case "currency":
						$this->buildCurrencyField($name,$field);
						break;
					case "date":
						$this->buildDateField($name,$field);
						break;
					case "datetime":
						$this->buildDateTimeField($name,$field);
						break;
					case "percent":
						$this->buildPercentField($name,$field);
						break;
					case "dropdown":
						$this->buildDropdownField($name,$field);
						break;
					case "choice":
						$this->buildChoiceField($name,$field,$data);
						break;
					case "text":
						$this->buildTextField($name,$field,$data);
						break;
					case "fk":
						$this->buildFKField($name,$field,$data,$DB);
						break;
					case "hidden":
						$this->buildHiddenField($name,$field,$data,$DB);
						break;
					default:
						break;
				}
			}
		}

		if (strlen($lastgroup) > 0)
			echo "</div>";


		//Build the form security token
		echo "<input type='hidden' name='formtoken' value='{$_SESSION['csrf_key']}'>";
	}

	private function buildRecordSelector($list,$n,$table)
	{
		if ($list["record_selector"])
		{
			echo "<div class='record_selector'>";
			$start = intval($_SESSION["liststate"]["start"]) + 1;
			$end = min($n, $_SESSION["liststate"]["start"] + $_SESSION["liststate"]["view_length"]);
			$selfff = trim($_SERVER["PHP_SELF"], "/");
			$v2 = FormList::encryptParam("table={$table}&action=rec_select");
			echo "<span>RECORDS <input type='text' class='record_selector' name='rec_from' value='{$start}' size='3' onchange='record_selector(this,\"{$v2}\",\"{$selfff}\",\"{$_SESSION['csrf_key']}\")' /> TO {$end} OF {$n}</span>";
			$prev_disabled = $start <= 1 ? "disabled" : "";
			$next_disabled = $end >= $n ? "disabled" : "";


			echo "<button class='record_selector' onclick='record_selector_prev(this,\"{$v2}\",\"{$selfff}\",\"{$_SESSION['csrf_key']}\")' {$prev_disabled}>PREV</button>";
			echo "<button class='record_selector' onclick='record_selector_next(this,\"{$v2}\",\"{$selfff}\",\"{$_SESSION['csrf_key']}\")' {$next_disabled} >NEXT</button>";
			echo "</div>";
		}
	}

	public function buildList($DB,$data=null,$where=null,$complexparams=null,$DBfunction=null,$DBparameters=null)
	{
		if (! $this->config)
			throw new Exception(__FILE__ . "[" . __LINE__ ."] FormList has not been constructed with list parameters" );

		if (! isset ($this->config['fields']) )
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No fields are sepcified in parameters" );

		if (!isset($this->config['global']))
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No globals sepcified in parameters" );

		if (!isset($this->config['global'] ['table'] ))
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No table sepcified in global section of parameters" );

		if (!isset($this->config['list']))
			throw new Exception(__FILE__ . "[" . __LINE__ ."] No list section sepcified in parameters" );


		$global = $this->config['global'];
		$list = $this->config['list'];
		$table = $this->config['global'] ['table'];
		$fields = $this->config['fields'];
		$selff = trim($_SERVER["PHP_SELF"],"/");

		//Check number in records in table
		$order = '';
		if ($this->haveParameterText($list,'default_order') )
			$order = $list['default_order'];

		if ($where == null)
		{
			$where = '';
			if ($this->haveParameterText($list,'default_where') )
			{
				$where = $list['default_where'];
				if ($data)
					$where = $this->parseVariable($where,$data);
			}
		}

		$n = 0;
		if (null != $complexparams)
		{
			$countFuncion = $complexparams["functions"] ["count"];
			$params = $complexparams["params"];
			$n = $DB->$countFuncion($params);
		}
		else
			$n = $DB->rows_in_table($table,$where);

		//Check session list variables
		if (! isset($_SESSION["liststate"]))
		{
			//Set up the defaults
			$_SESSION["liststate"] = array();
			$_SESSION["liststate"] ["start"] = 0;
			$_SESSION["liststate"] ["view_length"] = 50;
		}

		if ($this->haveParameterText($list, 'items_per_page'))
		{
			$ipp = max(1, intval($list['items_per_page']));
			$_SESSION["liststate"]["view_length"] = $ipp;
		}


		if ($_SESSION["liststate"] ["start"] < 0)
			$_SESSION["liststate"] ["start"] = 0;

		if ($_SESSION["liststate"] ["start"] > ($n-1))
			$_SESSION["liststate"] ["start"] = max(0,$n-1);

		$limit = "limit {$_SESSION["liststate"] ["start"]}, {$_SESSION["liststate"] ["view_length"]}";

		//Start the export of the html
		echo "<div class='list'>";




		//Build a heading
		if ($this->haveParameterText($list,'heading') )
		{
			echo "<div class='_listHead'>";
			$strText = htmlspecialchars($list['heading']);
			echo "<h1>{$strText}</h1>";
			echo "</div>";
		}

		//We need to build a menu of actions
		echo "<div class='listactions'>";
		$v = FormList::encryptParam("table={$table}&action=create");
		echo "<form method='GET' action='{$selff}'><input type='hidden' name='v' value='{$v}'/><button>CREATE</button>";
		echo "<button id='del{$table}' class='listDelete' form='_list1form' disabled>DELETE</button>";
		echo "</form>";
		//Any additional actions
		if (isset($list['additional_actions']))
		{
			foreach($list["additional_actions"] as $action)
			{
				if (isset($action["title"]) && isset($action["href"]) )
					echo "<button type='button' onclick='window.location=\"{$action["href"]}\"'>{$action["title"]}</button>";
			}
		}

		echo "</div>";

		$this->buildRecordSelector($list,$n,$table);

		//Record selector
		//if ($list["record_selector"])
		//{
		//    echo "<div id='list_record_selector' class='record_selector'>";
		//    $start = intval($_SESSION["liststate"] ["start"]) + 1;
		//    $end = min($n,$_SESSION["liststate"] ["start"] + $_SESSION["liststate"] ["view_length"]);
		//    $selfff = trim($_SERVER["PHP_SELF"],"/");
		//    $v2 = FormList::encryptParam("table={$table}&action=rec_select");
		//    echo "<span>RECORDS <input type='text' class='record_selector' name='rec_from' value='{$start}' size='3' onchange='record_selector(this,\"{$v2}\",\"{$selfff}\",\"{$_SESSION['csrf_key']}\")' /> TO {$end} OF {$n}</span>";
		//    $prev_disabled = $start <= 1 ? "disabled" : "";
		//    $next_disabled = $end >= $n ? "disabled" : "";


		//    echo "<button class='record_selector' onclick='record_selector_prev(this,\"{$v2}\",\"{$selfff}\",\"{$_SESSION['csrf_key']}\")' {$prev_disabled}>PREV</button>";
		//    echo "<button class='record_selector' onclick='record_selector_next(this,\"{$v2}\",\"{$selfff}\",\"{$_SESSION['csrf_key']}\")' {$next_disabled} >NEXT</button>";
		//    echo "</div>";
		//}

		echo "<div class='_list1'>";

		$r = null;
		if (null != $complexparams) {
			$listFuncion = $complexparams["functions"]["list"];
			$params = $complexparams["params"];
			$params[] = $order;
			$params[] = $limit;

			$r = $DB->$listFuncion($params);
		}
		else
		{

			$r = $DB->allFromTable($table, $where, $order, $limit);
		}
		if ($n == 0)
		{
			echo "<p class='norecord'>NO RECORDS</p>";
		}
		else
		{
			echo "<form id='_list1form' method='POST' action='{$selff}'>";

			echo "<table>";

			//Create the tabel headings
			echo "<tr>";
			if ($this->haveParameterText($list,'type') && $list['type'] == "checkbox")
				echo "<th></th>";

			foreach($fields as $name => $field)
			{
				$list_attr = $field['list'];
				if ($list_attr['display'])
				{
					echo "<th>";
					$strData ='';
					if ($this->haveParameterText($list_attr,'heading') )
						$strData = htmlspecialchars($list_attr['heading']);
					echo $strData;
					echo "</th>";
				}
			}
			if (isset($list['actions']) )
			{
				foreach($list['actions'] as $name => $action)
				{
					echo "<th></th>";
				}
			}

			//Add any additional fields as seen fit
			if (function_exists("listAdditionalHeadings"))
			{
				listAdditionalHeadings($table);
			}

			echo "</tr>";
			$display_count = $r->num_rows;
			while ($d = $r->fetch_array(MYSQLI_ASSOC))
			{
				$recid="";
				if ($this->haveParameterBoolean($global,"single_record") )
				{
					$recid = FormList::encryptParam("table={$table}&onerec=1,action=edit");
				}
				else
				{

					if (isset($d[$global['primary_key']]))
					{
						$recid = FormList::encryptParam("table={$table}&id={$d[$global['primary_key']]}&action=edit");
					}
				}
				echo "<tr>";
				if ($this->haveParameterText($list,'type') && $list['type'] == "checkbox")
					echo "<td><input type='checkbox' class='listcheck{$table}' name='li[]' value='{$recid}' onchange='deleteButtonChange(\"{$table}\")'/></td>";

				foreach($fields as $name => $field)
				{

					$tdClass='';
					switch ($field['type'])
					{
						case 'decimal':
						case 'integer':
						case 'currency':
						case 'percent':
							$tdClass = "r";
							break;
						case 'boolean':
							$tdClass = "c";
							break;
						default:
							break;
					}


					$list_attr = $field['list'];
					if ($list_attr['display'])
					{
						echo "<td";
						if (strlen($tdClass) > 0)
							echo " class='{$tdClass}'";
						echo">";
						if ($this->haveParameterBoolean($list_attr,'anchor'))
						{
							$vrec = urlencode($recid);
							$url = "{$selff}?v={$vrec}";
							echo "<a href='{$url}'>";
						}

						$strData = '';

						if (isset($d[$name]))
						{
							switch ($field['type'])
							{
								case 'text':
									$strData = htmlspecialchars($d[$name]);
									break;
								case 'boolean':
									switch ($list_attr["displayoption"])
									{
										case "tick":
											if ($d[$name])
												$strData = "&check;";
											else
												$strData = "";
											break;
										default:
											$strData = htmlspecialchars($d[$name]);
											break;
									}
									break;
								case 'integer':
									$strData = htmlspecialchars(intval($d[$name]));
									break;
								case 'decimal':
									$v = floatval($d[$name]);
									$decimals = 2;
									if (isset($field['decimalplaces']))
										$decimals = intval($field['decimalplaces']);
									$strData = number_format($v,$decimals);
									break;
								case 'currency':
									$v = floatval($d[$name]);
									$decimals = 2;
									if (isset($field['decimalplaces']))
										$decimals = intval($field['decimalplaces']);
									$currency_char = "$";
									if (isset($field['currency_symbol']))
										$currency_char = $field['currency_symbol'];
									$strData = $currency_char . number_format($v,$decimals);
									break;
								case 'percent':
									$v = floatval($d[$name]) * 100.0;
									$decimals = 2;
									if (isset($field['decimalplaces']))
										$decimals = intval($field['decimalplaces']);
									$strData = number_format($v,$decimals) . "%";
									break;
								case 'date':
									$tz = 'UTC';
									if (isset($_SESSION['tz']))
										$tz = $_SESSION['tz'];
									elseif (isset($_SESSION['timezone']))
										$tz = $_SESSION['timezone'];

									$strData = classTimeHelpers::timeFormatnthDate($d[$name],$tz);
									break;
								case 'datetime':
									$tz = 'UTC';
									if (isset($_SESSION['tz']))
										$tz = $_SESSION['tz'];
									elseif (isset($_SESSION['timezone']))
										$tz = $_SESSION['timezone'];
									$strData = classTimeHelpers::timeFormatnthDateTime1($d[$name],$tz);
									break;
								case 'timemilli':
									$tz = 'UTC';
									if (isset($_SESSION['tz']))
										$tz = $_SESSION['tz'];
									elseif (isset($_SESSION['timezone']))
										$tz = $_SESSION['timezone'];
									$strData = classTimeHelpers::timeFormatMilliToHHMM($$d[$name],$tz);
									break;
								case 'fk':
									$v = intval($d[$name]);
									$d2 = $DB->getFromTable($field['fk_table'],$field['fk_index'],$v);
									if ($d2 && isset($d2[$field['fk_display']]))
									{
										$strData = htmlspecialchars($d2[$field['fk_display']]);
									}
									break;
								default:
									$strData = htmlspecialchars($d[$name]);
									break;

							}

						}

						if (isset($list_attr['translation']))
						{
							switch ($list_attr['translation'])
							{
								case "upper":
									$strData = strtoupper($strData);
									break;
								case "upper":
									$strData = strtolower($strData);
									break;
								case "firstupper":
									$strData = strtoupper(substr($strData,0,1)) . strtolower(substr($strData,1));
									break;
								default:
									break;
							}
						}

						if (strlen($strData) == 0 && $this->haveParameterBoolean($list_attr, 'anchor'))
							$strData = "[BLANK]";

						echo $strData;

						if ($this->haveParameterBoolean($list_attr,'anchor'))
						{
							echo "</a>";
						}



						echo "</td>";
					}
				}

				if (isset($list['actions']) )
				{
					foreach($list['actions'] as $name => $action)
					{
						$actionvalue = urlencode(FormList::encryptParam("table={$table}&id={$d[$global['primary_key']]}&action=actionit&call={$action['action']}"));
						echo "<td><button type='button' value='{$actionvalue}' onclick='actionStations(this)'>{$action['display']}</button></td>";
					}
				}

				//Add any additional fields as seen fit
				if (function_exists("listAdditionalFields"))
				{
					listAdditionalFields($table,$d);
				}
				echo "</tr>";
			}
			echo "</table>";
			$v = FormList::encryptParam("table={$table}&action=delete");
			echo "<input type='hidden' name='v' value='{$v}'/>";
			echo "<input type='hidden' name='formtoken' value='{$_SESSION['csrf_key']}'>";
			echo "</form>";
		}

		echo "</div>";

		if ($display_count > 10)
			$this->buildRecordSelector($list,$n,$table);


		echo "</div>";

	}

	static function getPageTitle($formdata,$tablename)
	{
		if (isset($formdata[$tablename] ['global'] ['page_title'] ) )
			return htmlspecialchars($formdata[$tablename] ['global'] ['page_title']);
		return "";
	}

	static function buildSelectEntry($tablename,$formdata)
	{
		$strText = '';
		if (isset($formdata[$tablename] ['global'] ['selector_text'] ))
		{
			$strText = htmlspecialchars($formdata[$tablename] ['global'] ['selector_text']);
		}
		if (strlen($strText) == 0)
			$strText = $tablename;

		echo "<li id='sel{$tablename}' class='liselector' onclick='selectRight(this,\"{$tablename}\")'>{$strText}</li>";
	}

	static function buildAllSelectEntries($FormTables,$formdata)
	{
		foreach ($FormTables as $t)
		{
			FormList::buildSelectEntry($t,$formdata);
		}
	}

	static function buildAdditionalSelectEntry($entry)
	{
		$strText = '';
		$strID = "";
		if (isset($entry['name']) )
		{
			$strText = htmlspecialchars($entry['name']);
			$strID = strtolower($entry['name']);
		}

		echo "<li id='seladd{$strID}' class='liselector' onclick='selectRight(this,\"{$strID}\")'>{$strText}</li>";
	}

	static function buildAllAdditionalSelectEntries($entries)
	{
		foreach ($entries as $t)
		{
			FormList::buildAdditionalSelectEntry($t);
		}

	}

	static public function buildPanel($DB,$data,$tablename,$formdata,$first=false)
	{
		echo "<div id='{$tablename}' class='rtEntity";
		if ($first)
			echo " first";
		echo "'>";
		echo "<div id='list{$tablename}'>";
		(new FormList($formdata[$tablename]))->buildList($DB,$data);
		echo "</div>";
		echo "</div>";
	}


	static public function buildAllPanels($DB,$data,$FormTables,$formdata)
	{
		$first = true;
		foreach ($FormTables as $t)
		{
			FormList::buildPanel($DB,$data,$t,$formdata,$first);
			$first = false;
		}
	}

	static public function buildForm($DB,$data,$tablename,$formdata,$pageData)
	{
		echo "<div id='{$tablename}form' class='detailEntity'>";
			echo "<div class='form'>";
			echo "<form method='POST' action='" . htmlspecialchars($_SERVER["PHP_SELF"]) . "'>";
				if ($pageData ['select'] == $tablename)
				{
					$FL = new FormList($formdata[$tablename]);
					if ($pageData ['form'] ['mode'] == "edit")
						$FL->getTableData($DB,$pageData ['form'] ['recid']);
					$FL->buildFormFields($data,$DB);
					echo "<div class='submit'>";
					if ($pageData ['form'] ['mode'] == "edit")
					{
						$v = FormList::encryptParam("table={$tablename}&action=change&recid={$pageData ['form'] ['recid']}");
						echo "<input type='hidden' name='v' value='{$v}' />";
						echo "<input type='submit' name='_server_change' value='CONFIRM CHANGE' />";
					}
					else
					{
						$v = FormList::encryptParam("table={$tablename}&action=create");
						echo "<input type='hidden' name='v' value='{$v}' />";
						echo "<input type='submit' name='_server_new' value='CREATE NEW' />";
					}
					echo "</div>";
				}
			echo "</form>";
			echo "</div>";
		echo "</div>";
	}

	static public function buildAllForms($DB,$data,$FormTables,$formdata,$pageData)
	{
		foreach ($FormTables as $t)
		{
			FormList::buildForm($DB,$data,$t,$formdata,$pageData);
		}

	}

	static public function handleGet(&$pageData)
	{
		if (isset($_GET['v']))
		{
			$a = self::decryptParamRaw($_GET['v']);

			if (isset($a['action']))
			{
				switch ($a['action'])
				{
					case "create":
						$pageData ['select'] = $a['table'];
						$pageData ['form'] ['display'] = true;
						$pageData ['form'] ['mode'] = "create";
						break;
					case "edit":
						$pageData ['select'] = $a['table'];
						$pageData ['form'] ['display'] = true;
						$pageData ['form'] ['mode'] = "edit";
						if (isset($a['onerec']))
							$pageData ['form'] ['recid'] = -99;
						else
							$pageData ['form'] ['recid'] = $a['id'];
						break;
					case "actionit":
						if (function_exists($a['call']) )
						{
							$callwho = $a['call'];
							$callwho($a['table'],$a['id']);
						}
						break;
				}
			}
		}
	}

	static public function handlePost($DB,$formdata,&$pageData,$security=null)
	{

		if (isset($_POST['v']))
		{
			$a = FormList::decryptParamRaw($_POST['v']);

			if (isset($a['table']) && isset($a['action']))
			{
				if ($a['action'] == 'delete')
				{

					$FL = new FormList($formdata[$a['table']]);
					$form =$FL->getForm();

					if ($form)
					{
						//Check security
						$can_delete = 1;
						if ($security !== null && isset($form["security"]) && isset($form["security"] ["delete"]) && $form["security"] ["delete"] > 0)
						{
							$can_delete = intval($security) & intval($form["security"] ["delete"]);
						}

						if ($can_delete)
						{
							if (isset($_POST["li"]))
							{
								foreach($_POST["li"] as $f)
								{
									$b = FormList::decryptParamRaw($f);
									$rslt = $FL->DeleteRecord($DB,$b["id"]);
								}
							}
						}
					}
				}
				elseif ($a['action'] == 'rec_select')
				{

					$FL = new FormList($formdata[$a['table']]);
					$list = $formdata[$a['table']] ["list"];

					if (!isset($_SESSION["liststate"]))
						 $_SESSION["liststate"] = array();

					if (isset($_POST["next"]) )
					{
						$_SESSION["liststate"] ["start"] = $_SESSION["liststate"] ["start"] + $_SESSION["liststate"] ["view_length"];
					}
					elseif (isset($_POST["prev"]) )
					{
						$_SESSION["liststate"] ["start"] = $_SESSION["liststate"] ["start"] - $_SESSION["liststate"] ["view_length"];
					}
					else
					{
						$_SESSION["liststate"] ["start"] = intval($_POST["rec"]) - 1 ;
						$_SESSION["liststate"] ["view_length"] = 50;
						if (isset($list['items_per_page']) )
						{
							$ipp = max(1,intval($list['items_per_page']));
							$_SESSION["liststate"] ["view_length"] = $ipp;
						}
					}
				}
				else
				{
					$FL = new FormList($formdata[$a['table']]);
					$valid = $FL->getFormInputFields();
					if (!$valid)
					{
						$e = $FL->fieldsWithError();
						error_log("Form entry had errors dump of error array follows:");
						FormList::var_error_log($e,"Form errors");
					}
					if ($valid && $a['action'] == 'change')
					{
						$FL->ModifyRecord($DB,$a['recid']);
						$pageData ['form'] ['display'] = false;
					}
					if ($valid && $a['action'] == 'create')
					{
						$FL->AddRecord($DB);
						$pageData ['form'] ['display'] = false;
					}
					$pageData ['select'] = $a['table'];
				}
			}
		}
	}

	static public function encryptParam($v)
	{
		// Remove the base64 encoding from our key
		if (isset($_SESSION['session_key']))
		{
			$flag = "FFFF";
			$data = $flag . (string) $v;
			$encryption_key = base64_decode($_SESSION['session_key']);

			// Generate an initialization vector
			$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
			// Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
			$encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
			// The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
			$result = base64_encode($encrypted . '::' . $iv);
			return $result;
		}
		return null;
	}

	static public function decryptParamRaw($data)
	{
		$params = array();
		$d = null;

		if (isset($_SESSION['session_key']))
		{
			// Remove the base64 encoding from our key
			$encryption_key = base64_decode($_SESSION['session_key']);
			// To decrypt, split the encrypted data from our IV - our unique separator used was "::"
			list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
			$d =  openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
		}

		if ($d)
		{
			if (strlen($d) >= 4)
			{
				if (substr($d,0,4) == 'FFFF')
				{
					if (strlen($d) >= 4)
					{
						$param = substr($d,4,strlen($d)-4);
						parse_str(strtr($param, ":,", "=&"), $params);
					}
				}
			}
		}
		return $params;
	}
}
?>
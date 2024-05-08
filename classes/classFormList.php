<?php
class FormList
{
	private $config;

	function __construct($params)
	{
		$this->config = $params;
	}

	private function var_error_log( $object=null )
	{
		ob_start();                    // start buffer capture
		var_dump( $object );           // dump the values
		$contents = ob_get_contents(); // put the buffer into a variable
		ob_end_clean();                // end capture
		error_log( $contents );        // log contents of the result of var_dump( $object )
	}

	public function encryptParam($v)
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
		else
			return null;
	}

	private function decryptParamPart($data)
	{
		if (isset($_SESSION['session_key']))
		{

			// Remove the base64 encoding from our key
			$encryption_key = base64_decode($_SESSION['session_key']);
			// To decrypt, split the encrypted data from our IV - our unique separator used was "::"
			list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
			return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
		}
		else
			return null;
	}

	public function decryptParam($data)
	{
		$ret = array();
		$ret['command'] = '';
		$ret['id'] = 0;
		if ($d = $this->decryptParamPart($data))
		{
			if (strlen($d) >= 4)
			{
				if (substr($d,0,4) == 'FFFF')
				{
					if (strlen($d) >= 7)
						$ret['command'] = substr($d,4,3);
					if (strlen($d) > 7)
						$ret['id'] = intval(substr($d,7,strlen($d)-7));
				}
			}
		}
		return $ret;
	}

	public function decryptParamRaw($data)
	{
		$params = array();
		if ($d = $this->decryptParamPart($data))
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

	static public function getCheckBoxField($f)
	{
		if (isset($_POST[$f]) && strtoupper($_POST[$f]) == "ON")
			return true;
		return false;
	}

	static public function getPercentageField($f)
	{
		$v = 0.0;
		if (isset($_POST[$f]) )
		{
			$v = FormList::getField($f);
			if (strpos($v,"%") !== false)
				$v = intval($v) / 100.0;
			else
				$v = floatval($v);
		}
		return $v;
	}

	private function displayText($v)
	{
		if (!is_null($v) && strlen($v) > 0)
		{
			$z = str_replace("\x92", "'", $v);
			return htmlspecialchars(htmlspecialchars_decode($z),ENT_QUOTES  | ENT_HTML401);
		}
		else
			return '';
	}

	private function displayBoolean($v)
	{
		if ($v)
			return '&#x2713';
		else
			return '';
	}

	private function displayNumber($v)
	{
	   if (!is_null($v))
			return htmlspecialchars($v,ENT_QUOTES  | ENT_HTML401);
	   else
			return '';
	}

	private function displayDouble($v)
	{
	   if ($v && strlen($v) > 0)
			return htmlspecialchars($v,ENT_QUOTES  | ENT_HTML401);
	   else
			return '';
	}

	private function displayPercentage($v)
	{
	   if ($v && strlen($v) > 0)
			return htmlspecialchars(sprintf('%4.2f%%',($v*100)),ENT_QUOTES  | ENT_HTML401);
	   else
			return '';
	}

	private function displayMoney($v)
	{
	   if ($v && strlen($v) > 0)
			return ("$" . number_format($v,2));
	   else
			return '';
	}

	private function displayDate($v,$format='Y-m-d')
	{
	   if ($v && strlen($v) > 0)
	   {
			$dt = new DateTime($v);
			return $dt->format($format);
	   }
	   else
			return '';
	}

	private function displayDatetime($v,$format='Y-m-d H:i:s')
	{
	   if ($v && strlen($v) > 0)
	   {
			$dt = new DateTime($v);
			return $dt->format($format);
	   }
	   else
			return '';
	}

	public function displayCustField($r,$f)
	{
		if (null != $r)
		{
			if (isset($r[$f]))
				return htmlspecialchars($r[$f],ENT_QUOTES  | ENT_HTML401);
		}
		return '';
	}

	public function displayPercField($r,$f)
	{
		if (null != $r)
		{
			if (isset($r[$f]))
				  return htmlspecialchars(sprintf('%4.2f%%',($r[$f]*100)),ENT_QUOTES  | ENT_HTML401);
		}
		return '';
	}

	 public function displayMoneyField($r,$f)
	 {
		if (null != $r)
		{
			if (isset($r[$f]))
			  return number_format($r[$f],2);
		}
		return '';
	 }

	public function displayDateField($r,$f,$format='Y-m-d')
	{
		if (null != $r)
		{
			if (isset($r[$f]))
			{
				$dt = new DateTime($r[$f]);
				return $dt->format($format);
			}
		}
		return '';
	}

	public function displayDatetimeField($r,$f,$format='Y-m-d H:i:s')
	{
		if (null != $r)
		{
			if (isset($r[$f]))
			{
				$dt = new DateTime($r[$f]);
				return $dt->format($format);
			}
		}
		return '';
	}

	public function displayListField($item,$row)
	{
		$v = null;
		if (isset($row[$item]))
			$v = $row[$item];
		$i = $this->config['fields'] [$item];

		switch (strtoupper($i['type']))
		{
		case 'COLOR':
			return $this->displayText($v);
			break;
		case 'TEXT':
			return $this->displayText($v);
			break;
		case 'TEXTAREA':
			return $this->displayText($v);
			break;
		case 'NUMBER':
			return $this->displayNumber($v);
			break;
		case 'DOUBLE':
			return $this->displayDouble($v);
			break;
		case 'PERCENTAGE':
			return $this->displayPercentage($v);
			break;
		case 'MONEY':
			return $this->displayMoney($v);
			break;
		case 'DATE':
			return $this->displayDate($v);
			break;
		case 'DATETIME':
			return $this->displayDatetime($v);
			break;
		case 'CHECKBOX':
			return $this->displayBoolean($v);
			break;
		case 'FK':
			if (isset($row[$i['fkdisp']]))
				$v = $row[$i['fkdisp']];
			return $this->displayText($v);
			break;
		default:
			return $this->displayText($v);
			break;
		}

	}

	public function dispFormField($item,$data,$db)
	{

		$i = $this->config['fields'] [$item];
		if (isset($i['form_display']) && !$i['form_display'])
			return;
		//Readonly fields
		if (isset($i['readonly']) && $i['readonly'])
		{
			switch (strtoupper($i['type']))
			{
				case 'TEXT':
				if (isset($i['formvalue']))
				{
					$v = $i['formvalue'];
					if (strlen($v) > 0)
						echo "<p class='p5'>".$this->displayText($v)."</p>";
				}
				else
				if (null != $data)
				{
					if (is_array($data))
						$v = $data[$item];
					else
						$v = $data;
					if (strlen($v) > 0)
						echo "<p class='p5'>".$this->displayText($v)."</p>";
				}
				break;
			}
		}
		else
		{
			switch (strtoupper($i['type']))
			{
			case 'TEXT':
			case 'COLOR':
					//input and class
					echo "<input ";
					if (isset($i['err']) && $i['err'])
						echo "class='err' ";
					else
					if ($i['req'])
						echo "class='req' ";
					//type and name
					echo "type='".$i['type']."' name = '".$item."_f'";
					//size
					if (isset($i['size']))
						echo " size='".$i['size']."'";
					if (isset($i['maxlength']))
						echo " maxlength='".$i['maxlength'] ."'";
					//title
					if (isset($i['title']))
						echo " title='".$i['title'] ."'";
					//onchange
					if (isset($i['onchange']))
						echo " onchange='{$i['onchange']}'";
					if (isset($i['formvalue']))
					{
						$v = $i['formvalue'];
						if (strlen($v) > 0)
							echo " value='".$this->displayText($v)."'";

					}
					else
					if (null != $data)
					{
						if (is_array($data))
							$v = $data[$item];
						else
							$v = $data;
						if (strlen($v) > 0)
							echo " value='".$this->displayText($v)."'";
					}
					echo " />";
			   break;
		   case 'TEXTAREA':
				echo "<textarea ";
				if (isset($i['err']) && $i['err'])
						echo "class='err' ";
				else
				if ($i['req'])
				   echo "class='req' ";
									//name
				echo "name = '".$item."_f'";
				//rows and cols
				if (isset($i['rows']))
					echo " rows='".$i['rows']."'";
				if (isset($i['cols']))
					echo " cols='".$i['cols']."'";
				//title
				if (isset($i['title']))
						echo " title='".$i['title'] ."'";
				if (isset($i['spellcheck']))
					echo " spellcheck='true'";
				echo ">";
				if (isset($i['formvalue']))
				{
					$v = $i['formvalue'];
					if (strlen($v) > 0)
						echo $this->displayText($v);
				}
				else
				if (null != $data)
				{
					if (is_array($data))
						$v = $data[$item];
					else
						$v = $data;
					if (strlen($v) > 0)
						echo $this->displayText($v);
				}
				echo "</textarea>";
				break;
		   case 'NUMBER':
					//input and class
					echo "<input ";
					if (isset($i['err']) && $i['err'])
						echo "class='err' ";
					else
					if ($i['req'])
						echo "class='req' ";
					//type and name
					echo "type='number' name = '".$item."_f'";
					//size
					echo " size='".$i['size']."'";
					//title
					if (isset($i['title']))
						echo " title='".$i['title'] ."'";
					if (isset($i['formvalue']))
					{
						$v = $i['formvalue'];
						if (strlen($v) > 0)
							echo " value='".$this->displayNumber($v)."'";

					}
					else
					if (null != $data)
					{
						if (is_array($data))
							$v = $data[$item];
						else
							$v = $data;
						if (strlen($v) > 0)
							echo " value='".$this->displayNumber($v)."'";
					}
					echo " />";
			   break;
			case 'DOUBLE':
					//input and class
					echo "<input ";
					if (isset($i['err']) && $i['err'])
						echo "class='err' ";
					else
					if ($i['req'])
						echo "class='req' ";
					//type and name
					echo "type='text' name = '".$item."_f'";
					//size
					echo " size='".$i['size']."'";
					//title
					if (isset($i['title']))
						echo " title='".$i['title'] ."'";
					if (isset($i['formvalue']))
					{
						$v = $i['formvalue'];
						if (strlen($v) > 0)
							echo " value='".$this->displayDouble($v)."'";
					}
					else
					if (null != $data)
					{
						if (is_array($data))
							$v = $data[$item];
						else
							$v = $data;
						if (strlen($v) > 0)
							echo " value='".$this->displayDouble($v)."'";
					}
					echo " />";
			   break;
			case 'PERCENTAGE':
					//input and class
					$class = "r";
					echo "<input ";
					if (isset($i['err']) && $i['err'])
						$class .= " err";
					else
					if ($i['req'])
							$class .= " req";
					echo "class='$class' ";
					//type and name
					echo "type='text' name = '".$item."_f'";
					//size
					echo " size='".$i['size']."'";
					//title
					if (isset($i['title']))
						echo " title='".$i['title'] ."'";
					if (isset($i['formvalue']))
					{
						$v = $i['formvalue'];
						if (strlen($v) > 0)
							echo " value='".$this->displayPercentage($v)."'";

					}
					else
					if (null != $data)
					{
						if (is_array($data))
							$v = $data[$item];
						else
							$v = $data;
						if (strlen($v) > 0)
							echo " value='".$this->displayPercentage($v)."'";
					}
					echo " />";
			   break;
			case 'MONEY':
					//input and class
					echo "<input ";
					if (isset($i['err']) && $i['err'])
						echo "class='err right' ";
					else
					if ($i['req'])
						echo "class='req right' ";
					//type and name
					echo "type='text' name = '".$item."_f'";
					//size
					echo " size='".$i['size']."'";
					//title
					if (isset($i['title']))
						echo " title='".$i['title'] ."'";
					if (isset($i['formvalue']))
					{
						$v = $i['formvalue'];
						if (strlen($v) > 0)
							echo " value='".$this->displayMoney($v)."'";

					}
					else
					if (null != $data)
					{
						if (is_array($data))
							$v = $data[$item];
						else
							$v = $data;
						if (strlen($v) > 0)
							echo " value='".$this->displayMoney($v)."'";
					}
					echo " />";
			   break;
			case 'DATE':
					//input and class
					echo "<input ";
					if (isset($i['err']) && $i['err'])
						echo "class='err' ";
					else
					if ($i['req'])
						echo "class='req' ";
					//type and name
					echo "type='".$i['type']."' name = '".$item."_f'";
					//size
					echo " size='".$i['size']."'";
					//title
					if (isset($i['title']))
						echo " title='".$i['title'] ."'";
					if (isset($i['formvalue']))
					{
						$v = $i['formvalue'];
						if (strlen($v) > 0)
							echo " value='".$this->displayDate($v) ."'";

					}
					else
					if (null != $data)
					{
						if (is_array($data))
							$v = $data[$item];
						else
							$v = $data;
						if (strlen($v) > 0)
							echo " value='".$this->displayDate($v)."'";
					}
					echo " />";
			   break;
			case 'DATETIME':
					//input and class
					echo "<input ";
					if (isset($i['err']) && $i['err'])
						echo "class='err' ";
					else
					if ($i['req'])
						echo "class='req' ";
					//type and name
					echo "type='datetime-local' name = '".$item."_f'";
					//size
					if (isset($i['size']))
						echo " size='".$i['size']."'";
					//title
					if (isset($i['title']))
						echo " title='".$i['title'] ."'";
					if (isset($i['formvalue']))
					{
						$v = $i['formvalue'];
						if (strlen($v) > 0)
							echo " value='".$this->displayDatetime($v,'Y-m-d\TH:i:s') ."'";

					}
					else
					if (null != $data)
					{
						if (is_array($data))
							$v = $data[$item];
						else
							$v = $data;
						if (strlen($v) > 0)
							echo " value='".$this->displayDatetime($v,'Y-m-d\TH:i:s')."'";
					}
					echo " />";
			   break;
			case 'CHECKBOX':
					//input and class
					echo "<input ";
					if (isset($i['err']) && $i['err'])
						echo "class='err' ";
					else
					if ($i['req'])
						echo "class='req' ";
					//type and name
					echo "type='".$i['type']."' name = '".$item."_f'";
					//title
					if (isset($i['title']))
						echo " title='".$i['title'] ."'";
					if (isset($i['formvalue']))
					{
						if ($i['formvalue'])
							echo " checked";
					}
					else
					if (null != $data)
					{
						if (is_array($data))
						{
							if(isset($data[$item]) )
								$v = $data[$item];
						}
						else
							$v = $data;
						if (intval($v) > 0)
						   echo " checked";
					}

				   echo " />";

				   break;
			case "ENUM":
				   echo "<select name='".$item."_f'";
				   echo ">";
				   $vals = $i['values'];
				   $valsDisp = $i['valuesDisp'];
				   for ($idx = 0;$idx < count($vals); $idx++)
				   {
					  echo "<option value='".$vals[$idx]."'";
					  if (isset($i['formvalue']))
					  {
						  if ($i['formvalue'] == $vals[$idx])
							   echo " selected";
					  }
					  else
					  {
						 if (is_array($data))
						 {
							if (isset($data[$item]))
							{
								if ($data[$item] == $vals[$idx])
									echo " selected";
							}
						 }
						 else
						 if ($data == $vals[$idx])
							echo " selected";
					 }
					 echo ">" . $valsDisp[$idx] . "</option>";
				   }
				   echo "</select>";
				   break;
			case 'FK':
					echo "<select name='".$item."_f'";
					if (isset($i['err']) && $i['err'])
						echo " class='err' ";
					else
					if ($i['req'])
						echo " class='req' ";
					echo ">";

					if (!$i['req'])
					{
						echo "<option></option>";
					}

					if (isset($i['fkcond']))
					{
						$cond = $i['fkcond'];
					}
					else
					{
						$cond = '';
					}

					if($r = $db->allFromTable($i['fktable'],$cond,"order by " . $i['fkdisp']) )
					{
						while ($d = $r->fetch_array())
						{
							echo "<option value='".$d[$i['fkkey']]."'";

							if (isset($i['formvalue']))
							{
								if ($i['formvalue'] == $d[$i['fkkey']])
									echo " selected";
							}
							else
							if (isset($data[$item]))
							{
								if ($data[$item] == $d[$i['fkkey']])
									echo " selected";
							}
							echo ">";
							echo htmlspecialchars($d[$i['fkdisp']],ENT_QUOTES  | ENT_HTML401);
							echo "</option>";
						}
					}
					echo "</select>";
					break;
			}
		}
	}

	public function GetInputFields()
	{
		$fields = $this->config['fields'];
		$keys = array_keys($fields);
		for ($idx = 0; $idx < count($keys); $idx++)
		{
			$this->config['fields'] [$keys[$idx]] ['formvalue'] = $this->getField($keys[$idx] ."_f");
		}
	}


	public function CheckRequired()
	{
		$valid = true;
		$fields = $this->config['fields'];
		$keys = array_keys($fields);
		for ($idx = 0; $idx < count($keys); $idx++)
		{
			$item = $fields [$keys[$idx]];
			if ($item['req'])
			{
				if (strlen($this->getField($keys[$idx] ."_f") ) == 0)
				{
					$valid = false;
					$this->config['fields'] [$keys[$idx]] ['err'] = true;
				}
				else
					$this->config['fields'] [$keys[$idx]] ['err'] = false;
			}
		}
		return $valid;
	}

	public function DBValue($f)
	{
		 $v = $this->getField($f ."_f");
		 if (strtoupper($this->config['fields'] [$f] ['type']) == 'CHECKBOX')
		 {
			 if (null != $v && strtoupper($v) == "ON")
				return true;
			 else
				return false;
		 }
		 else
		 if (strtoupper($this->config['fields'] [$f] ['type']) == 'FK')
		 {
			if (null == $v)
				return null;
			else
				return $v;
		 }
		 else
		 if (strtoupper($this->config['fields'] [$f] ['type']) == 'DATE')
		 {
			if (null == $v)
				return null;
			else
			if (strlen($v) > 0)
				return $v;
			else
				return null;
		 }
		 else
		 if (strtoupper($this->config['fields'] [$f] ['type']) == 'PERCENTAGE')
		 {
			if (null == $v)
				return null;
			else
			if (strlen($v) > 0)
			{
				$v = trim($v,'%');
				return ($v /100.0);
			}
			else
				return null;
		 }
		 else
		 {
			return htmlspecialchars_decode($v);
		 }
	 }


	public function BuildArrayRowFromForm()
	{
		$row = array();
		$fs = $this->config['fields'];
		$keys = array_keys($fs);
		for ($idx = 0; $idx < count($keys); $idx++)
		{
			$doit = true;
			if (isset($fs[$keys[$idx]] ['readonly']) && $fs[$keys[$idx]] ['readonly'] )
				$doit = false;
			if ($doit)
				$row[$keys[$idx]] = $this->DBValue($keys[$idx]);
		}
		return $row;
	}
}
?>
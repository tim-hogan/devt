<?php
//devt.Version = 1.0
require("../SendGrid/sendgrid-php.php");
function mail_var_error_log( $object=null )
{
    ob_start();                    // start buffer capture
    var_dump( $object );           // dump the values
    $contents = ob_get_contents(); // put the buffer into a variable
    ob_end_clean();                // end capture
    error_log( $contents );        // log contents of the result of var_dump( $object )
}

class classEmailAddress
{
    public $address;
    public $name;
    function __construct($a,$n=null)
    {
        $this->address = $a;
        $this->name = null;
        if ($n)
            $this->name = $n;
    }

}

class classMail
{
    private $_from;
    private $_template;
    private $_api_key;

    function __construct($apikey='')
    {
        if (strlen($apikey) > 0)
            $this->_api_key = $apikey;
        else
        {
            $v = getenv('SENDGRID_API_KEY');
            if ($v && strlen($v) > 0)
                $this->_api_key = $v;
        }
    }

    public function SendMail($to,$from,$subject,$message,$msseage_mime = null)
    {
        $mime = "text/plain";
        $email = new \SendGrid\Mail\Mail();
        if ($msseage_mime)
            $mime = $msseage_mime;

        $email->addTo($to->address, $to->name);
        $email->setFrom($from->address, $from->name);
        $email->setSubject($subject);
        $email->addContent($mime,$message);

        //$this->_api_key = getenv('SENDGRID_API_KEY');

        $sendgrid = new \SendGrid($this->_api_key);
        try {
                $response = $sendgrid->send($email);
                if ($response->statusCode() == 202)
                    return true;
                else
                {
                    error_log("Failed email send: Status: {$response->statusCode()} To: <{$to->address}> {$to->name} From: <{$from->address}> {$from->name}");
                    mail_var_error_log($response);
                }
            }
        catch (Exception $e)
            {
                error_log("classMail.php SendGrid Error: {$e}");
            }
        return false;
    }

    public function SendMailFromTemplate($to,$from,$subject)
    {
        return $this->SendMail($to,$from,$subject,$this->_template,'text/html');
    }

    public function loadTemplateFromString($template)
    {
        $this->_template = $template;
    }

    public function loadTemplateFromFile($strFileName)
    {
        if ($f = fopen($strFileName, "r") )
        {
            $this->_template = fread($f,filesize($strFileName));
            fclose($f);
        }
        else
            throw new Exception("classMail::loadTemplateFromFile Cannot load file {$strFileName}");
    }

    public function updateTemplateText($search,$newtext)
    {
        $this->_template = str_replace($search,$newtext,$this->_template);
    }
}
?>
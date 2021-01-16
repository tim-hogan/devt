<?php session_start(); ?>
<?php
/**
 * @Version = 1.0
 * Session variables passed in the $_SESSION array
 *      security_error          What error to display form the pre-defined global list called $errormsg
 *      security_error_message  Any additional text that could be displayed
 */
function var_error_log( $object=null ,$text='')
{
    ob_start();
    var_dump( $object );
    $contents = ob_get_contents();
    ob_end_clean();
    error_log( "{$text} {$contents}" );
}

$selff = htmlspecialchars($_SERVER["PHP_SELF"]);
$_SESSION['returnto'] = $selff;
$additionalmsg = '';
$errortype = "default";

$errormsg = [
    'default'=> [
            '1' => "You have arrived here as you do not have sufficient permissions to do what you were trying to do.",
            '2' => "You can try <a href='Signin.php'>Signing</a> in again or see the site administrator.",
        ],
     //edit add additonal error messages here
     //'apisecurity'=> [
     //       '1' => "Error message line 1.",
     //       '2' => "Error message line 2.",
     //   ]
];

if (isset($_SESSION['security_error']))
{

    $action = $_SESSION['security_error'];
    $action = trim($action);
    $action = stripslashes($action);
    $action = strip_tags(htmlspecialchars_decode($action));

    switch ($action)
    {
        //case "apisecurity":
        //    $_SESSION['security_error'] = "apisecurity";
        //    break;
        default:
            $errortype = "default";
            break;
    }
    unset($_SESSION['security_error']);
}

if (isset($_SESSION['security_error_message']))
{
    $additionalmsg = trim($_SESSION['security_error_message']);
    $additionalmsg = stripslashes($additionalmsg);
    $additionalmsg = strip_tags(htmlspecialchars_decode($additionalmsg));
    $additionalmsg = htmlspecialchars($additionalmsg);
    unset($_SESSION['security_error_message']);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width" />
    <meta name="viewport" content="initial-scale=1.0" />
    <title>Security Error</title>
    <link href="css/Base.css" rel="stylesheet">
    <style>
    #mainconatiner {max-width: 1000px; margin: auto;}
    #error {font-family: 'Roboto'; font-size: 12pt;}
    #error img {height: 200px;}
    #error h1 {margin-left: 40px; color: #666;}
    #error p {margin-left: 60px;}
    </style>
</head>
<body>
  <div class="container">
    <div id='heading'>
    </div>
    <div id='mainconatiner'>
        <div id='main'>
            <div id='error'>
                <img src='/images/SecurityShield.png'/>
                <h1>SECURITY ERROR</h1>
                <?php
                error_log($errortype);
                $err1 = $errormsg[$errortype] ['1'];
                $err2 = $errormsg[$errortype] ['2'];
                echo "<p>{$err1}</p>";
                echo "<p>{$err2}</p>";
                if (strlen($additionalmsg) > 0)
                    echo "<p>{$additionalmsg}</p>";
                ?>
            </div>
        </div>
    </div>
  </div>
</body>
</html>

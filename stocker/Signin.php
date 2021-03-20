<?php 
/**
 * @abstract Template code for the Signin page
 * @author Tim Hogan
 * @version 1.0
 * @requires classSecure classEnvironment securityParams classVault classRolling
 * 
 * @todo Search for the word "edit" and edit as appopriate 
 */
session_start(); 
?>
<?php
require_once dirname(__FILE__) . "/includes/classSecure.php";
require_once dirname(__FILE__) . "/includes/classTime.php";
require_once dirname(__FILE__) . "/includes/classRolling.php";
require_once dirname(__FILE__) . "/includes/classStockerDB.php";
$DB = new stockerDB($devt_environment->getDatabaseParameters());


/**
 * Summary of resetSession
 */
function resetSession()
{
    if (isset($_SESSION['userid']))
        unset($_SESSION['userid']);
    if (isset($_SESSION['tz']))
        unset($_SESSION['tz']);
    if (isset($_SESSION['csrf_key_signin']))
        unset($_SESSION['csrf_key_signin']);
    if (isset($_SESSION['signin_attempt']))
        unset($_SESSION['signin_attempt']);
}

function parseAndCheck($f)
{
    if (isset($_POST[$f]) )
    {
        $v = trim($_POST[$f]);
        $v = stripslashes($v);
        $v = strip_tags(htmlspecialchars_decode($v));
        if (strpos($v," ") === false)
            return $v;
    }
    return null;
}

/**
 * Start
*/
$glb = $DB->getGlobal();
$SEC = new Secure();
$dtNow = new DateTime('now');
$selff = $_SERVER["PHP_SELF"];
$maxattempts = intval(MAX_USERNAME_ATTEMPS);
$err = false;

/*
 * Checks
*/
if (!Secure::isHTTPS())
    exit();

if (Rolling::checkRate($DB,"SignIn"))
{
    resetSession();
    header("Location: SecurityError.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    if (isset($_COOKIE['_devt22']))
    {
        //Check the timestamp
        if ($dtNow->getTimestamp() > (intval($_COOKIE['_devt22']) + 3600) )
        {
            //The cookie should have expired.
            setcookie("_devt22",0, time() - 3600,"","",true,true);
        }
        else
        {
            resetSession();
            $DB->createAudit("security","{$selff} [" .__LINE__. "] User Sign-In request attempt with invalid username too many times");
            header("Location: SecurityError.php");
            exit();
        }
    }

    //Check and parse the username and password and formtoken
    if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['formtoken']))
    {
        resetSession();
        $DB->createAudit("security","{$selff} [" .__LINE__. "] Missing fields in post, POST request has not orginated from this site");
        header("Location: SecurityError.php");
        exit();
    }

    //Check the csrf
    if (! Secure::checkCSRF("csrf_key_signin","formtoken") )
    {
        resetSession();
        $DB->createAudit("security","{$selff} [" .__LINE__. "] CSRF Formtoken error");
        header("Location: SecurityError.php");
        exit();
    }

    //Set a generic error
    $err = true;
    $errormessage = "INVALID USERNAME AND/OR PASSWORD";


    //Parse the parameters
    $username = parseAndCheck("username");
    $password = parseAndCheck("password");

    if ($username && $password)
    {
        $user = $DB->getUserByUserName($username);
        if ($user)
        {
            //Check is user is either deleted or disabled
            if ($user['user_deleted'] == 0 && $user['user_disabled'] == 0)
            {
                if ($SEC->checkPassword($password,$user['user_hash'],$user['user_salt']) )
                {
                    // Set up the session variables
                    $_SESSION['csrf_key'] = base64_encode(openssl_random_pseudo_bytes(32));
                    $_SESSION['reset_userid'] = $user['iduser'];

                    //Reest the failed sign-in
                    $DB->resetFailCounter($user['iduser']);

                    $resetRequired = false;
                    if ($user['user_forcereset'])
                        $resetRequired = true;
                    if ($glb['global_password_renew_days'] > 0)
                    {
                        $dtRenew = new DateTime($user['user_pw_renew_date']);
                        if ($dtNow->getTimestamp() > $dtRenew->getTimestamp())
                            $resetRequired = true;
                    }

                    //Now wwe need to check if we have a force reset
                    if ($resetRequired)
                    {
                        header("Location: ChangePassword.php");
                        exit();
                    }

                    //Successful sign in
                    $err = false;
                    $_SESSION['userid'] = $user['iduser'];
                    $DB->updateUserLastSiginIn($user['iduser']);
                    $DB->createAudit("signin","Sign-in",$user['iduser']);


                    if ($user['user_default_page'] && strlen($user['user_default_page']) > 0)
                        header("Location: {$user['user_default_page']}");
                    elseif ($glb['global_default_homepage'] && strlen($glb['global_default_homepage']) > 0 )
                        header("Location: {$glb['global_default_homepage']}");
                    else
                        header("Location: /");
                    exit();
                }
                else
                {
                    $DB->createAudit("signin","Invalid password",$user['iduser']);
                    //Update the failed counter
                    if ($glb['global_password_maxattempts'] <= $DB->updateFailCounter($user['iduser']) )
                    {
                        //Disable the account as there have been too mnay failed sign-in attempts
                        $DB->disableUser($user['iduser']);
                    }
                }
            }
            else
            {
                if ($user['user_disbaled'])
                {
                    $errormessage = "Your user account has been disabled or locked";
                }
            }
        }
        else
        {
            $DB->createAudit("signin","Invalid username {$username}",null);
        }
    }

    if ($err)
    {
        if (!$username && !$password)
            $DB->createAudit("signin","No username and password supplied");
        if (!$username)
            $DB->createAudit("signin","No username supplied");
        if (!$password)
            $DB->createAudit("signin","No password supplied");

        if (isset($_SESSION['signin_attempt']))
            $_SESSION['signin_attempt'] = intval($_SESSION['signin_attempt']) + 1;
        else
            $_SESSION['signin_attempt'] = 1;
        if (intval($_SESSION['signin_attempt']) > $maxattempts)
        {
            $attempts = intval($_SESSION['signin_attempt']);
            $DB->createAudit("security","Sign in attempts exceed system limit");
            setcookie("_devt22", $dtNow->getTimestamp(), time()+ (3600),"","",true,true);
        }
    }
}

$_SESSION['csrf_key_signin']=base64_encode(openssl_random_pseudo_bytes(32));

?>
<!DOCTYPE HTML>
<html>
<head>
<meta name="viewport" content="width=device-width" />
<meta name="viewport" content="initial-scale=1.0" />
<title>STOCKER</title>
<link rel='stylesheet' type='text/css' href='css/base.css' /> 
<link rel='stylesheet' type='text/css' href='css/heading.css' />
<link rel='stylesheet' type='text/css' href='css/main.css' />
<link rel='stylesheet' type='text/css' href='css/Signin.css' />
</head>
<body>
    <div id="container">
        <div id="heading">
            <p>STOCKER</p>
        </div>
        <div id="main">
            <div id="form">
                <form method = "POST" autocomplete="off" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <table>
                        <tr><td>USERNAME</td><td><input name="username" maxlength="100" autocomplete="off" autofocus /></td></tr>
                        <tr><td>PASSWORD</td><td><input id="p1" type="password" name="password" autocomplete="off" ></td></tr>
                    </table>
                    <p class="errMsg"><?php if ($err) echo $errormessage;?></p>
                    <?php echo "<input type='hidden' name='formtoken' value='{$_SESSION['csrf_key_signin']}'>"; ?>
                    <input type="submit" name="Signin" value="Sign In" />
                </form>
            </div>
        </div>
    </div>
</body>
</html>

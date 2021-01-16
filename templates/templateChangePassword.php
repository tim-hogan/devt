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
require './includes/classSecure.php';
require_once "./includes/classRolling.php";


/*
edit
require './includes/classDATABASE.php";
$DB = new classDATABASE($devt_environment->getDatabaseParameters());
 */


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


$SEC = new Secure();
$selff = $_SERVER["PHP_SELF"];

if (!Secure::isHTTPS()) exit();
$strErr='';


$u = 0;
$glb = $DB->getGlobal();
$dtNow = new DateTime('now');

if (isset($_SESSION['reset_userid']))
    $u=$_SESSION['reset_userid'];
if ($u==0)
{
    $DB->createAudit("security","{$selff} [" .__LINE__. "] ChangePassword entered with no userid");
    header("Location: SecurityError.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    //Check and parse the username and password and formtoken
    if (!isset($_POST['oldpw']) || !isset($_POST['newpw']) || !isset($_POST['newpw2']) || !isset($_POST['formtoken']))
    {
        resetSession();
        $DB->createAudit("security","{$selff} [" .__LINE__. "] Missing fields in post, POST request has not orginated from this site");
        header("Location: SecurityError.php");
        exit();
    }

    if (!Secure::checkCSRF())
    {
        $DB->createAudit("security","{$selff} [" .__LINE__. "] ChangePassword csrf failed");
        header("Location: SecurityError.php");
        exit();
    }

    $bValid = true;

    $opw = parseAndCheck('oldpw');
    $npw = parseAndCheck('newpw');
    $npw2 = parseAndCheck('newpw2');

    if ($user = $DB->getUser($u))
    {
        //Are we able to change if last change was less than n hours earlier.
        if ($user['user_pw_change_date'] && $glb['global_password_no_renew_within_hours'] > 0)
        {
            $minhours = intval($glb['global_password_no_renew_within_hours']);
            $dtLastChange = new DateTime($user['user_pw_change_date']);
            if ($dtNow->getTimestamp() < ($dtLastChange->getTimestamp() + ($minhours*3600)))
            {
                $DB->createAudit("security","{$selff} [" .__LINE__. "] ChangePassword attempt to change password again within {$minhours} hours User ID: {$user['iduser']}");
                $strErr = "You cannot change a password within {$minhours} hours of your last change.";
                $bValid = false;
            }
        }

        if ($bValid)
        {
            //First check the old password
            if ($SEC->checkPassword($opw,$user['user_hash'],$user['user_salt']))
            {
                //We have a valid old password
                if ($npw == $npw2)
                {
                    //Check the strength of the password based on the organisational rules
                    $msg = Secure::strongPassword($npw,$glb['global_password_min_length'],$glb['global_password_min_upper'],$glb['global_password_min_lower'],$glb['global_password_min_num'],$glb['global_password_min_special']);
                    if (strlen($msg) == 0)
                    {

                        if ($glb['global_password_maxattempts'] > 0)
                        {
                            $back = min(intval($glb['global_password_maxattempts']),25);
                            //We have a password that matches now check that its not the same password used the last 10 times
                            $oldpasswords = $user['user_prev_hash'];
                            $oldsalts = $user['user_prev_salt'];

                            for ($oidx = 0; $oidx < $back; $oidx++)
                            {
                                $oldhash = substr($oldpasswords,$oidx*64,64);
                                $oldsalt = substr($oldsalts,$oidx*64,64);

                                if (strlen($oldhash) == 64)
                                {
                                    if ($SEC->checkPassword($npw,$oldhash,$oldsalt))
                                    {
                                        $bValid = false;
                                        $DB->createAudit("security","{$selff} [" .__LINE__. "] Attempted to change password to one used before userid {$u}",$u);
                                        $strErr = "You cannot use a password that you have used before.";
                                    }
                                }
                            }
                        }

                        if ($bValid)
                        {
                            $salt = Secure::createSalt();
                            $hash = $SEC->passwordHash($npw,$salt);
                            if ($DB->updatePassword($u,$hash,$salt,false,$glb['global_password_renew_days']) )
                            {
                                $DB->createAudit("password","{$selff} [" .__LINE__. "] User changed password",$u);
                                header('Location: Signin.php');
                                exit();
                            }
                            else
                            {
                                $DB->createAudit("password","{$selff} [" .__LINE__. "] ystem error attempting to change password",$u);
                                $strErr = "System failed to change your password";
                            }
                        }
                    }
                    else
                        $strErr = $msg;
                }
                else
                    $strErr = "New passwords not identicle.";
            }
            else
            {
                //Old password is not valid
                $strErr = "Old Password is not valid";
            }
        }
    }
    else
    {
        $DB->createAudit("security","{$selff} [" .__LINE__. "] ChangePassword for an invalid user");
        header("Location: SecurityError.php");
        exit();
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
<meta name="viewport" content="width=device-width" />
<meta name="viewport" content="initial-scale=1.0" />
<title>CHANGE PASSWORD</title>
<link rel='stylesheet' type='text/css' href='css/base.css' />
<link rel='stylesheet' type='text/css' href='css/heading.css' />
<link rel='stylesheet' type='text/css' href='css/main.css' />
<link rel='stylesheet' type='text/css' href='css/ChangePassword.css' />
<style>
</style>
</head>
<body>
<div id='container'>
    <div id='heading'>
    </div>
    <div id="main">
        <div id='form'>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <table>
                    <tr><td class='td2'>OLD PASSWORD</td><td><input type='password' name = 'oldpw'></td></tr>
                    <tr><td class='td2'>NEW PASSWORD</td><td><input id="p1" type='password' name = 'newpw'><img class ="i1" src="/images/Eye.png" what="p1" onclick="t(this)" /></td></tr>
                    <tr><td class='td2'>REPEAT PASSWORD</td><td><input id="p2" type='password' name = 'newpw2'><img class="i2" src="/images/Eye.png" what="p2" onclick="t(this)" /></td></tr>
                    <tr><td><input type='submit' value='Change' /></td><td></td></tr>
                    </table>
                    <?php if (strlen($strErr) > 0) echo "<p class='errMsg'>{$strErr}</p>";?>
                    <?php
                    echo "<input type='hidden' name='formtoken' value='{$_SESSION['csrf_key']}'>";
                    ?>
            </form>
        </div>
    </div>
</div>
</body>
</html>
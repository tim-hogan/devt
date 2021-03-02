<?php
/**
 * @abstract Template Code for the a MultiPart Form List page
 * @author Tim Hogan
 * @version 1.0
 * @requires classSecure (Which includes clasEnvironment, clasVault and secruityParams ), classFormList2.php
 * Search for **EDIT** for places to edit
 */
session_start();
function var_error_log( $object=null,$text='')
{
    ob_start();
    var_dump( $object );
    $contents = ob_get_contents();
    ob_end_clean();
    error_log( "{$text} {$contents}" );
}

require_once "./includes/classSecure.php";
require_once "./includes/classRolling.php";
require "./includes/classFormList2.php";
$formdata = require("./forms/**EDIT**.php");

/**
 * Database
 */
require "./includes/class**EDIT**.php";
$DB = new **EDIT**($devt_environment->getDatabaseParameters());


$selff = trim($_SERVER["PHP_SELF"],"/");
$user = null;
if (isset($_SESSION['userid']))
    $user = $DB->getUser($_SESSION['userid']);
Secure::CheckPage2($user,SECURITY_ADMIN);

$pageData = array();
$pageData ['select'] = '**EDIT** FIRST database table';
$pageData ['form'] = array();
$pageData ['form'] ['display'] = false;
$pageData ['form'] ['mode'] = "";
$pageData ['form'] ['recid'] = "";


if (isset($_GET['v']))
{
    $a = FormList::decryptParamRaw($_GET['v']);
    if (isset($a['action']))
    {
        switch ($a['action'])
        {
            case "create":
                $table = $a['table'];
                $pageData ['select'] = $table;
                $pageData ['form'] ['display'] = true;
                $pageData ['form'] ['mode'] = "create";
                break;
            case "edit":
                $table = $a['table'];
                $pageData ['select'] = $table;
                $pageData ['form'] ['display'] = true;
                $pageData ['form'] ['mode'] = "edit";
                if (isset($a['onerec']))
                    $pageData ['form'] ['recid'] = -99;
                else
                    $pageData ['form'] ['recid'] = $a['id'];
                break;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    //Check the CSRF
    if (!Secure::checkCSRF() )
    {
        $DB->createAudit("security","{$selff} [" .__LINE__. "] Admin csrf failed");
        header("Location: SecurityError.php");
        exit();
    }

    if (isset($_POST['v']))
    {
        $a = FormList::decryptParamRaw($_POST['v']);
        if (isset($a['table']) && isset($a['action']))
        {
            $FL = new FormList($formdata[$a['table']]);
            $valid = $FL->getFormInputFields();
            if ($valid && $a['action'] == 'change')
                $FL->ModifyRecord($DB,$a['recid']);
            if ($valid && $a['action'] == 'create')
                $FL->AddRecord($DB);
            $pageData ['select'] = $a['table'];
        }
    }
}

$g_data = array();
//**EDIT**  Enter any gloabl data to pass to the formlist class here
//Example $g_data['max_wait_time'] = 400; 



//Declare all tables to be managed
$g_FormTables = ['**EDIT** TABLE NAME','**EDIT** TABLE NAME'];

?>

<!DOCTYPE HTML>
<html>
<head>
    <meta name="viewport" content="width=device-width" />
    <meta name="viewport" content="initial-scale=1.0" />
    <title>**EDIT**</title>
    <link rel='stylesheet' type='text/css' href='css/base.css' />
    <link rel='stylesheet' type='text/css' href='css/heading.css' />
    <link rel='stylesheet' type='text/css' href='css/menu.css' />
    <link rel='stylesheet' type='text/css' href='css/main.css' />
    <link rel='stylesheet' type='text/css' href='css/form.css' />
    <link rel='stylesheet' type='text/css' href='css/list.css' />
    <link rel='stylesheet' type='text/css' href='css/**EDIT**.css' />
    <script>
        var g_pageState = JSON.parse('<?php echo json_encode($pageData);?>');
    </script>
    **EDIT**<script src="/js/MultiForm.js"></script>
</head>
<body onload="start()">
    <div id="container">
        <div id="heading">
            <h1>**EDIT**HEADING</h1>
        </div>
        <div id="menu">
            <p>**EDIT**MENU</p>
        </div>
        <div id="main">
            <div id="flexcontainer">
                <div id="left">
                    <div class="minimiser" expanded="1" minsize="20" onclick="minmaxwinddow(this)" title="Minimise"><<</div>
                    <div class="panel">
                        <p class="lefttitle">**EDIT**</p>
                        <ul>
                            <?php FormList::buildAllSelectEntries($g_FormTables,$formdata); ?>
                        </ul>
                    </div>
                </div>
                <div id="right">
                    <div class="minimiser" expanded="1" minsize="20" onclick="minmaxwinddow(this)"><<</div>
                    <div class="panel">
                        <?php FormList::buildAllPanels($DB,$g_data,$g_FormTables,$formdata); ?>
                    </div>
                </div>
                <div id="formdetails">
                    <div class="hider" expanded="1" minsize="20" onclick="hidewinddow(this)">X</div>
                    <div class="panel">
                        <div id="**EDIT** table name + form  e.g. userform" class="detailEntity first">
                            <div class="form">
                            <form method="POST" autocomplete="off" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                                <?php
                                if ($pageData ['select'] == 'global')
                                {
                                    $FL = new FormList($formdata['global']);
                                    if ($pageData ['form'] ['mode'] == "edit")
                                        $FL->getTableData($DB,$pageData ['form'] ['recid']);
                                    $FL->buildFormFields(null,$DB);
                                    echo "<div class='submit'>";
                                    if ($pageData ['form'] ['mode'] == "edit")
                                    {
                                        $v = FormList::encryptParam("table=globalr&action=change&recid={$pageData ['form'] ['recid']}");
                                        echo "<input type='hidden' name='v' value='{$v}' />";
                                        echo "<input type='submit' name='_server_change' value='CONFIRM CHANGE' />";
                                    }
                                    else
                                    {
                                        $v = FormList::encryptParam("table=global&action=create");
                                        echo "<input type='hidden' name='v' value='{$v}' />";
                                        echo "<input type='submit' name='_server_new' value='CREATE NEW' />";
                                    }
                                    echo "</div>";
                                }
                                ?>
                            </form>
                            </div>
                        </div>
                        <div id="**EDIT** table name + form  e.g. userform" class="detailEntity">
                            <div class="form">
                            <form method="POST" autocomplete="off" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                                <?php
                                if ($pageData ['select'] == 'server')
                                {
                                    $FL = new FormList($formdata['server']);
                                    if ($pageData ['form'] ['mode'] == "edit")
                                        $FL->getTableData($DB,$pageData ['form'] ['recid']);
                                    $FL->buildFormFields(null,$DB);
                                    echo "<div class='submit'>";
                                    if ($pageData ['form'] ['mode'] == "edit")
                                    {
                                        $v = FormList::encryptParam("table=server&action=change&recid={$pageData ['form'] ['recid']}");
                                        echo "<input type='hidden' name='v' value='{$v}' />";
                                        echo "<input type='submit' name='_server_change' value='CONFIRM CHANGE' />";
                                    }
                                    else
                                    {
                                        $v = FormList::encryptParam("table=server&action=create");
                                        echo "<input type='hidden' name='v' value='{$v}' />";
                                        echo "<input type='submit' name='_server_new' value='CREATE NEW' />";
                                    }
                                    echo "</div>";
                                }
                                ?>
                            </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
session_start();
require_once dirname(__FILE__) . "/includes/classSecure.php";
require_once dirname(__FILE__) . "/includes/classTime.php";
require_once dirname(__FILE__) . "/includes/classStockerDB.php";
$DB = new stockerDB($devt_environment->getDatabaseParameters());

$user = null;
if (isset($_SESSION['userid']))
{
    $user = $DB->getUser($_SESSION['userid']);
    Secure::CheckPage2($user,SECURITY_ADMIN);
}
else
{
    header("Location: Signin.php");
}


function getPercentage($v)
{
    if (strpos($v,"%") !== false)
       return floatval(trim(str_replace("%","",$v))) / 100.0;
    else
        return floatval(trim($v));
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $stock = $_POST['stock'];
    $below = floatval($_POST['below']);
    $above = floatval($_POST['above']);
    $rti = getPercentage($_POST['return']);
    $DB->createWatch($user['iduser'],$stock,$below,$above,$rti);
}

?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>ALERTS</title>
        <style>
            body {font-family: Arial, Helvetica, sans-serif;font-size: 10pt;margin: 0;padding: 0;}
            #main {padding: 20px;}
            #list h1 {font-size: 14pt; color: #ddbf19;}
            #listtable {margin: 10px; padding: 10px; border: solid 1px #888;}
            #listtable table {border-collapse: collapse;}
            #listtable tr.gre {background-color: #eee;}
            #listtable tr.pay {background-color: #eefbe4;}
            #listtable th {padding-top: 8px; padding-bottom: 8px;padding-right: 16px;border-top: solid 1px #aaa;border-bottom: solid 1px #aaa;color: #888;}
            #listtable td {padding-top: 8px; padding-bottom: 8px;padding-right: 16px;border-top: solid 1px #aaa;border-bottom: solid 1px #aaa;}
            #listtable input {text-align:right;}
            #form {margin: 10px; padding: 10px; border: solid 1px #888;}
            #form label {margin-top: 14px; font-size: 8pt; color: #666;display: block;}
            #form input[type='submit'] {margin-top: 14px; display: block;}
            #form h2 {font-size: 12pt; color: #5aad29;}
            .r {text-align: right;}
        </style>
        <script type="text/javascript" src="/js/apiClass.js"></script>
        <script>
        var api = new apiJSON("stocker.devt.nz", "stockerApi.php?r=", "", true);
            api.parseReply = function (d) {
                console.log("Reply from API");
        }
        function enableAlert(n) {
            var v = false;
            if (n.checked)
                v = true;
            var p = { watch: n.value, value: v };
            api.queueReq("POST", "enablewatch",p);
            }
            function setWatchBelow(n) {
                var w = n.getAttribute('watchid');
                var v = n.value;
                var p = { watch: w, value: v };
                api.queueReq("POST", "setwatchbelow",p);
            }
            function setWatchAbove(n) {
                var w = n.getAttribute('watchid');
                var v = n.value;
                var p = { watch: w, value: v };
                api.queueReq("POST", "setwatchabove",p);
            }
        </script>
        </head>
    <body>
        <div id="container">
            <div id="main">
                <div id="list">
                    <h1>ALERTS</h1>
                    <div id="listtable">
                        <table>
                            <tr><th>ENABLED</th><th>STOCK</th><th class='r'>BELOW</th><th class='r'>ABOVE</th><th class='r'>RTI</th><th></th></tr>
                            <?php
                            $r = $DB->allWatchesForUser($user['iduser']);
                            while ($watch = $r->fetch_array(MYSQLI_ASSOC))
                            {
                                $strrti = number_format($watch['watch_rti'] * 100.0,1) . "%";
                                $enabled = '';
                                if (! $watch['watch_done'])
                                    $enabled = " checked";
                                echo "<tr><td><input type='checkbox' value='{$watch['idwatch']}' onchange='enableAlert(this)' {$enabled} /></td><td>{$watch['stock_code']}</td><td class='r'><input type='text' watchid='{$watch['idwatch']}' size='10' value='{$watch['watch_below']}' onchange='setWatchBelow(this)' /></td><td class='r'><input type='text' watchid='{$watch['idwatch']}' size='10' value='{$watch['watch_above']}' onchange='setWatchAbove(this)'/></td><td class='r'>{$strrti}</td><td><button type=button>DELETE</button></td></tr>";
                            }
                            ?>
                        </table>
                    </div>
                    <div id="form">
                        <h2>CREATE NEW</h2>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                            <label for="stock">CHOOSE STOCK</label>
                            <select id="stock" name="stock">
                                <?php
                                $r = $DB->allStock();
                                while ($stock = $r->fetch_array(MYSQLI_ASSOC))
                                {
                                    echo "<option value='{$stock['idstock']}'>{$stock['stock_code']}</option>";
                                }
                                ?>
                            </select>
                            <label for="below">BELOW</label>
                            <input id="below" type="text" name="below" size="10" />
                            <label for="above">ABOVE</label>
                            <input id="above" type="text" name="above" size="10" />
                            <label for="above">RETURN</label>
                            <input id="return" type="text" name="return" size="10" />
                            <input type="submit" name="create" value="CREATE" />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

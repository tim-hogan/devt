<?php
$strRsltMatch = "";
$regx="";
$strtest="";
$strrepl="";
$strreplace="";

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $regx = trim($_POST['regex']);
    $strtest = $_POST['teststr'];
    $strrepl = $_POST['replstr'];
    if (preg_match($regx,$strtest) )
        $strRsltMatch = "True";
    else
        $strRsltMatch = "False";
    $strreplace = preg_replace($regx,$strrepl,$strtest);
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta name="viewport" content="width=device-width" />
    <meta name="viewport" content="initial-scale=1.0" />
    <title>Regular Expression Tester</title>
</head>
<body>
    <div id="container">
        <div id="form">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <div class="f">
                    <p>TEST STRING</p>
                    <input type="text" name="teststr" size="40" value="<?php echo $strtest;?>" />
                </div>
                <div class="f">
                    <p>REPLACE STRING</p>
                    <input type="text" name="replstr" size="40" value="<?php echo $strrepl;?>" />
                </div>
                <div class="f">
                    <p>REG EXPRESSION</p>
                    <input type="text" name="regex" size="10" value="<?php echo $regx;?>" />
                </div>
                <button>TEST</button>
            </form>
            <table>
                <tr><td>MATCH</td><td><?php echo $strRsltMatch;?></td></tr>
                <tr><td>REPLACE</td><td><?php echo $strreplace;?></td></tr>
            </table>
        </div>
    </div>
</body>
</html>
<?php
$minidata = "";

function buildRandomMatrix()
{
    $pos = array();
    $data = array();
    $letter_idx = array();
    for ($i = 0; $i < 62;$i++)
    {
        $r = rand(0,61);
        while (isset($pos[$r]))
            $r = rand(0,61);
        $pos[$r] = 1;

        if ($i < 26)
        {
            $data[$r] = chr(97+$i);
            $letter_idx[ord($data[$r])] = $r;
        }
        elseif ($i < 52)
        {
            $data[$r] = chr(39+$i);
            $letter_idx[ord($data[$r])] = $r;
        }
        else
        {
            $data[$r] = chr($i-4);
            $letter_idx[ord($data[$r])] = $r;
        }
    }

    $ret = array();
    $str = "";
    for ($i = 0; $i < 62;$i++)
        $str .= $data[$i];
    $ret['str'] = $str;
    $ret['index'] = $letter_idx;
    return $ret;
}

$bDoneRandomise = false;
$keys = array();
$keystring = null;
$words = null;



if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if (isset($_POST['minime']))
    {
        $rawdata = $_POST['raw'];
        $type = $_POST['type'];
        mkdir("/tmp/minify");
        file_put_contents("/tmp/minify/input.{$type}",$rawdata);
        exec("yui-compressor /tmp/minify/input.{$type} > /tmp/minify/output.txt");
        $minidata = file_get_contents("/tmp/minify/output.txt");
    }

    if (isset($_POST['random']))
    {
        $ran = buildRandomMatrix();
        $keystring = $ran['str'];
        $ltrs = $ran['index'];
        $idx = 0;
        $words = explode(",",$_POST['words']);
        foreach ($words as $word)
        {
            $t = "";
            $l = strlen($word);
            for ($j = 0;$j < $l;$j++)
            {
                $off = $ltrs[ord(substr($word,$j,1))];
                $t .= sprintf("%02d",$off);
            }
            $keys[$idx++] = $t;
        }
        $bDoneRandomise = true;
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width" />
    <meta name="viewport" content="initial-scale=1.0" />
    <title>MiniFy</title>
    <style>
        body {color: #555;font-family: Arial, Helvetica, sans-serif; font-size: 10pt; margin: 0;}
        #container {padding: 20px;margin: auto;width: 1200px;}
        #minimiser {margin: 10px; padding: 10px; border: solid 1px #888;background-color: #eee;}
        #randomiser {margin: 10px;padding: 10px; border: solid 1px #888;background-color: #eee;}
        #randomiser p {margin: 0;}
        h1 {font-size: 14pt; color: #555}
    </style>
    <script>
        //(function zydrt() {
        //    for (var i = 0; i < g_rk.length; i++) {
        //        var t = '';
        //        var w = g_rk[i];
        //       for (var j = 0; j < (w.length / 2); j += 2) {
        //            t += g_k[parseInt(w.substr(j * 2, 2))];
        //       }
        //        g_w[i] = t;
        //   }
        //})();
    </script>
</head>
<body>
    <div id="container">
        <div id="minimiser">
            <h1>THE MINIMSER</h1>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            
                <p>INPUT</p>
                <input type="radio" name="type" value="js" checked /><span>JAVA SCRIPT</span><br/>
                <input type="radio" name="type" value="css" /><span>CASCADE STYLE SHEET</span><br/>
                <textarea name="raw" cols="150" rows="20"></textarea>
                <p>OUTPUT</p>
                <textarea name="min" cols="150" rows="20"><?php echo $minidata;?></textarea>
                <input type="submit" name="minime" value="Minify" />
            </form>
        </div>
        <div id="randomiser">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <input type="text" name="words" size="75" />
                <input type="submit" name="random" value="Random Creator" />
            </form> 
            <?php
            if ($bDoneRandomise)
            {
                echo "<p>//Keys decode</p>";
                $k = 0;
                foreach ($words as $word)
                {
                    echo "<p>//g_w[{$k}]='{$word}'</p>";
                    $k++;
                }


                echo "<p>";
                echo "var g_k='";
                echo $keystring;
                echo "';";
                echo "</p>";


                echo "<p> var g_rk=[";
                foreach($keys as $key)
                {
                    echo "'{$key}',";
                }
                echo "];</p>";

                echo "<p>var g_w=[];</p>";
                $s = htmlspecialchars("(function(){for(var d=0;d<g_rk.length;d++){var c='';var a=g_rk[d];for(var b=0;b<(a.length/2);b++){c+=g_k.substr(parseInt(a.substr(b*2,2)),1)}g_w[d]=c}})();");
                echo "<p>{$s}</p>";

            }
            ?>
        </div>
    </div>
</body>
</html>
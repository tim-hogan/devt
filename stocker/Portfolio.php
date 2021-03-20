<?php
session_start();
require_once dirname(__FILE__) . "/includes/classSecure.php";
require_once dirname(__FILE__) . "/includes/classTime.php";
require_once dirname(__FILE__) . "/includes/classStockerDB.php";
$DB = new stockerDB($devt_environment->getDatabaseParameters());

function var_error_log( $object=null,$text='')
{
    ob_start();
    var_dump( $object );
    $contents = ob_get_contents();
    ob_end_clean();
    error_log( "{$text} {$contents}" );
}

function getTextField($f)
{
    $v = null;
    if (isset($_POST[$f]))
    {
        $v = trim($_POST[$f]);
    }
    return $v;
}

function getIntegerField($f)
{
    $v = null;
    if (isset($_POST[$f]))
    {
        $v = trim($_POST[$f]);
        $v = str_replace("$","",$v);
        $v = str_replace(",","",$v);
        return intval($v);
    }
    return $v;
}


function getDecimalField($f)
{
    $v = null;
    if (isset($_POST[$f]))
    {
        $v = trim($_POST[$f]);
        $v = str_replace("$","",$v);
        $v = str_replace(",","",$v);
        return floatval($v);
    }
    return $v;
}

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


if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $stock = getIntegerField('stockcode');
    $buysell = getTextField('buysell');
    $price = getDecimalField('price');
    $qty = getDecimalField('qty');
    
    $DB->createPortfolioEntry($stock,$buysell,$price,$qty);
}

?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>STOCKER PORTFOLIO</title>
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
            #listtable td.tdgrey1 {color: #aaa;}
            #form {margin: 10px; padding: 10px; border: solid 1px #888;}
            #form input[type='text'] {margin-bottom: 10px;}
            #form input[type='submit'] {margin-top: 10px;display: block;}
            #form select {margin-bottom: 10px;}
            #form label {display: block;font-size: 8pt;margin-left: 3px;color: #555;}
            .r {text-align: right;}
            .red {color: red;}
            .green {color: green;}
            .b {font-weight: bold;}
            .small {font-size: 8pt;}
        </style>
        <script type="text/javascript" src="/js/apiClass.js"></script>
        <script>
            var api = new apiJSON("stocker.devt.nz", "stockerApi.php?r=", "", true);
                api.parseReply = function (d) {
                    console.log("Reply from API");
            }
            function archive(n) {
                var w = n.getAttribute('portid');
                var p = { tranid: w, value: n.checked };
                api.queueReq("POST", "archivetran",p);
            }

            function tick() {
                window.location = "Portfolio.php";
            }
            function start() {
                setInterval(tick, 300000);
            }
        </script>
    </head>
    <body onload="start()">
        <div id="container">
            <div id="main">
                <div id="list">
                    <h1>PORTFOLIO</h1>
                    <div id="listtable">
                        <table>
                            <tr><th>TIMESTAMP</th><th>STOCK</th><th></th><th class="'r'">QTY</th><th class="r">UNIT PRICE</th><th class="r">VALUE</th><th class="r">CURRENT VALUE</th><th class="r">GAIN/LOSS</th><th class="r">CHANGE</th><th class="r">RTI<sup>1</sup></th><th>ARCHIVE</th></tr>
                            <?php
                            $exch = $DB->getLastRecord('NZD');
                            $toNZ = $exch['record_value'];

                            $cashSum = 0.0;
                            $currentSum = 0.0;
                            $r = $DB->allPortFolio($user['iduser']);
                            while ($port = $r->fetch_array(MYSQLI_ASSOC))
                            {
                                $last = $DB->getLastRecord($port['stock_code']);

                                $strtime = classTimeHelpers::timeFormatnthDateTime1($port['portfolio_timestamp'],"Pacific/Auckland");
                                
                                $class='';
                                $tdclass='';

                                if ($port['portfolio_archive'])
                                    $tdclass = 'tdgrey1';
                                if ($port['portfolio_buysell'] == "sell")
                                    $class='gre';
                                if ($port['portfolio_buysell'] == "div")
                                    $class='pay';
                                
                                echo "<tr class='{$class}'>";

                                echo "<td class='{$tdclass}'><a href='Analysis.php?p={$port['idportfolio']}'>{$strtime}</a></td>";
                                echo "<td class='{$tdclass}'>{$port['stock_code']}</td>";
                                
                                switch ($port['portfolio_buysell'])
                                {
                                    case 'buy':
                                        echo "<td class='{$tdclass}'>BUY</td>";
                                        $qty = $port['portfolio_qty'];
                                        break;
                                    case 'sell':
                                        echo "<td class='{$tdclass}'>SOLD</td>";
                                        $qty = -($port['portfolio_qty']);
                                        break;
                                    case 'div':
                                        echo "<td class='{$tdclass}'>DIV</td>";
                                        $qty = -$port['portfolio_qty'];
                                        break;
                                }
                                
                                if ($port['portfolio_buysell'] != 'div')
                                    echo "<td class='r {$tdclass}'>{$qty}</td>";
                                else
                                    echo "<td></td>";

                                //NZD
                                $nzdUnitPrice = number_format($port['portfolio_price'],2);
                                if ($port['portfolio_buysell'] != 'div')
                                    echo "<td class='r {$tdclass}'>{$nzdUnitPrice}</td>";
                                else
                                    echo "<td></td>";

                                $NZDPurchaseValue = $port['portfolio_price'] * $qty;
                                $cashSum += $NZDPurchaseValue;
                                $strNZDPurchaseValue = number_format($NZDPurchaseValue,2);
                                echo "<td class='r {$tdclass}'>{$strNZDPurchaseValue}</td>";

                                if ($last['record_currency'] != 'NZD')
                                    $currentPrice = $last['record_value'] * $toNZ;
                                else
                                    $currentPrice = $last['record_value'];

                                $class='green';
                                if ($currentPrice < $port['portfolio_price'])
                                    $class='red';
                                $NZDCurrentValue = $currentPrice * $qty;
                                
                                if ($port['portfolio_archive'])
                                    $class = 'tdgrey1';



                                if ($port['portfolio_buysell'] != 'div')
                                    $currentSum += $NZDCurrentValue;
                                $strNZDCurrentValue = number_format($NZDCurrentValue,2);
                                
                                
                                if ($port['portfolio_buysell'] == "buy" && ! $port['portfolio_archive'] )
                                    echo "<td class='r {$class}'>{$strNZDCurrentValue}</td>";
                                else
                                    echo "<td></td>";

                                $delta = $NZDCurrentValue - $NZDPurchaseValue;
                                $strdelta = number_format($delta,2);

                                $class='green';
                                if ($delta < 0.0)
                                    $class='red';

                                if ($port['portfolio_archive'])
                                    $class = 'tdgrey1';
                                
                                if ($port['portfolio_buysell'] == "buy" && ! $port['portfolio_archive'] )
                                    echo "<td class='r {$class}'>{$strdelta}</td>";
                                else
                                    echo "<td></td>";

                                $change1 = (($NZDCurrentValue / $NZDPurchaseValue) - 1.0)*100.0;
                                $strchange1 = number_format($change1,2) . "%";
                                if ($port['portfolio_buysell'] == "buy" && ! $port['portfolio_archive'] )
                                    echo "<td class='r {$class}'>{$strchange1}</td>";
                                else
                                    echo "<td></td>";

                                $dtBuy = new DateTime($port['portfolio_timestamp']);
                                $dtNow = new DateTime();

                                $years = floatval($dtNow->getTimestamp() - $dtBuy->getTimestamp()) / (3600.0*24.0*365.0);
                                //Take 2% off current value
                                $NZDModifiedvalue = $NZDCurrentValue * (1.0 - $port['stock_margin'] );


                                try {
                                    
                                    $change2 = (pow($NZDModifiedvalue / $NZDPurchaseValue, (1.0/$years)) - 1.0) * 100.0;
                                }
                                catch (Exception $e) {
                                    error_log("Fatal error is calc NZDPurchasecvalue = {$NZDPurchaseValue} Error: {$e->getMessage()}");
                                    var_error_log($port,"portfolio");
                                }

                                $strchange2 = number_format($change2,2) . "%";
                                if ($port['portfolio_buysell'] == "buy" && ! $port['portfolio_archive'] )
                                    echo "<td class='r {$class}'>{$strchange2}</td>";
                                else
                                    echo "<td></td>";

                                echo "<td><input type='checkbox' portid='{$port['idportfolio']}' onchange='archive(this)'";
                                if ($port['portfolio_archive'])
                                    echo " checked";
                                echo "/></td>";
                                echo "</tr>";


                            }

                            //Cash sum
                            $strCashSum = "$". number_format($cashSum,2);
                            $strCurrentSum = "$". number_format($currentSum,2);

                            echo "<tr><td>TOTAL CASH</td><td colspan='4'></td><td class='r'>{$strCashSum}</td><td class='r'>{$strCurrentSum}</td><td colspan='3'></td></tr>";
                            //Calculate portfolio worth
                            $vsum1 = 0.0;
                            $vsum2 = 0.0;
                            $today = (new DateTime())->getTimestamp();

                            $portfolio = array();
                            $r = $DB->allPortfolioBuyForUser($user['iduser']);
                            while ($port = $r->fetch_array(MYSQLI_ASSOC))
                            {
                                if (! isset($portfolio[$port['portfolio_stock']]))
                                {
                                    $portfolio[$port['portfolio_stock']] = array(); 
                                }
                                array_push($portfolio[$port['portfolio_stock']],$port);
                            }
                            
                            $havesold = false;
                            $r = $DB->allPortfolioSellForUser($user['iduser']);
                            while ($port = $r->fetch_array(MYSQLI_ASSOC))
                            {
                                //Now calcualte returns on sold stock.
                                if (!$havesold)
                                {
                                    echo "<tr><td colspan='10'></td></tr>";
                                    echo "<tr>";
                                    echo "<td class='b' colspan='9'>RETURNS ON SOLD STOCK</td>";
                                    echo"</tr>";
                                    $havesold = true;
                                }
                                $stockid = $port['portfolio_stock'];
                                $qty = $port['portfolio_qty'];
                                $soldtime = (new DateTime($port['portfolio_timestamp']))->getTimestamp();
                                //Each $port record is a sell record
                                //$portfolio['stockid'] are all the buy records
                                
                                //First we search all vbuy records to see if we have one of the exact qty sold.
                                $exactfound = false;
                                $cnt = 0;
                                
                                foreach($portfolio[$stockid] as $buy)
                                {
                                    if (! $exactfound)
                                    {
                                        if ($buy['portfolio_qty'] == $qty)
                                        {
                                            error_log("Found buy price qty {$qty} price {$buy['portfolio_price']}  buyqty = {$buy['portfolio_qty']}");
                                            $buyprice = $buy['portfolio_price'] * $qty;
                                            $sellprice = $port['portfolio_price'] *$qty;
                                            $t = ($soldtime - (new DateTime($buy['portfolio_timestamp']))->getTimestamp() ) / (3600*24*365);
                                            $f = pow($sellprice/$buyprice,(1/$t)) * $buyprice;
                                            $vsum1 += $buyprice;
                                            $vsum2 += $f;
                                            
                                            $strtime = classTimeHelpers::timeFormatnthDateTime1($port['portfolio_timestamp'],"Pacific/Auckland");
                                            $strBuy = "$" . number_format($buyprice,2);
                                            $strSell = "$" . number_format($sellprice,2);
                                            $strg1 = "$" . number_format($sellprice-$buyprice,2);
                                            $strch = number_format((($sellprice/$buyprice)-1.0)*100.0,2) . "%";
                                            $Gain = (pow($sellprice/$buyprice,(1/$t)) - 1) * 100.0;
                                            $strGain = number_format($Gain,2) . "%";
                                            echo "<tr><td>{$strtime}</td><td>{$port['stock_code']}</td><td></td><td>{$qty}</td><td></td><td class='r'>{$strBuy}</td><td class='r'>{$strSell}</td><td class='r'>{$strg1}</td><td class='r'>{$strch}</td><td class='r'>{$strGain}</td></tr>";
                                            
                                            $portfolio[$stockid] [$cnt] ['portfolio_qty'] -= $qty;
                                            $buy['portfolio_qty'] -= $qty;
                                            $exactfound = true;
                                            error_log("Found exact");
                                        }
                                        $cnt++;
                                    }
                                }
                                
                                if (! $exactfound)
                                {
                                    $cnt = 0;
                                    foreach($portfolio[$stockid] as $buy)
                                    {
                                        if ($qty > 0)
                                        {
                                            
                                            $v = min($qty,$buy['portfolio_qty']);
                                            $buyprice = $buy['portfolio_price'] * $v;
                                            $sellprice = $port['portfolio_price'] * $v;
                                            $t = ($soldtime - (new DateTime($buy['portfolio_timestamp']))->getTimestamp() ) / (3600*24*365);
                                            $f = pow($sellprice/$buyprice,(1/$t)) * $buyprice;
                                            $vsum1 += $buyprice;
                                            $vsum2 += $f;
                                            
                                            $strtime = classTimeHelpers::timeFormatnthDateTime1($port['portfolio_timestamp'],"Pacific/Auckland");
                                            $strBuy = "$" . number_format($buyprice,2);
                                            $strSell = "$" . number_format($sellprice,2);
                                            $strg1 = "$" . number_format($sellprice-$buyprice,2);
                                            $strch = number_format((($sellprice/$buyprice)-1.0)*100.0,2) . "%";
                                            $Gain = (pow($sellprice/$buyprice,(1/$t)) - 1) * 100.0;
                                            $strGain = number_format($Gain,2) . "%";
                                            echo "<tr><td>{$strtime}</td><td>{$port['stock_code']}</td><td></td><td>{$v}</td><td></td><td class='r'>{$strBuy}</td><td class='r'>{$strSell}</td><td class='r'>{$strg1}</td><td class='r'>{$strch}</td><td class='r'>{$strGain}</td></tr>";

                                            $portfolio[$stockid] [$cnt] ['portfolio_qty'] -= $v;
                                            $buy['portfolio_qty'] -= $v;
                                            $qty -= $v;
                                            $cnt++;
                                        }
                                    }
                                }
                                

                            }
                            
                            //Now calcualte returns on each stock.
                            echo "<tr><td colspan='10'></td></tr>";
                            echo "<tr>";
                            echo "<td class='b' colspan='9'>REMAINING STOCK</td>";
                            echo"</tr>";

                            
                            $exch = $DB->getLastRecord('NZD');
                            $toNZ = $exch['record_value'];

                            $totval1 = 0.0;
                            $totval2 = 0.0;
                            foreach ($portfolio as $name => $stock)
                            {
                                foreach($stock as $buy)
                                {
                                    $last = $DB->getLastRecord($buy['stock_code']);
                                    if ($last['record_currency'] != 'NZD')
                                        $currentPrice = $last['record_value'] * $toNZ;
                                    else
                                        $currentPrice = $last['record_value'];

                                    $t = ($today - (new DateTime($buy['portfolio_timestamp']))->getTimestamp() ) / (3600*24*365);
                                    if ($buy['portfolio_qty'] > 0 )
                                    {
                                        $v1 = $buy['portfolio_qty'] * $buy['portfolio_price'];
                                        $vsum1 += $v1;
                                        $v2 = $buy['portfolio_qty'] * $currentPrice;
                                        $f = pow($v2/$v1,(1/$t)) * $v1;
                                        $vsum2 += $f;

                                        $strtime = classTimeHelpers::timeFormatnthDateTime1($buy['portfolio_timestamp'],"Pacific/Auckland");
                                        $strv1 = "$" . number_format($v1,2);
                                        $strv2 = "$" . number_format($v2,2);
                                        $strG = "$" . number_format($v2-$v1,2);
                                        $strP =  number_format((($v2/$v1)-1)*100.0,2) . "%";
                                        $v3=$v2*(1.0 - $buy['stock_margin']);
                                        $f1 = (pow($v3/$v1,(1/$t)) -1.0)*100.0;
                                        $strrti = number_format($f1,2) . "%";

                                        
                                        $totval1 += $v1;
                                        $totval2 += $v2;

                                        echo "<tr><td>{$strtime}</td><td>{$buy['stock_code']}</td><td></td><td class='r'>{$buy['portfolio_qty']}</td><td></td><td class='r'>{$strv1}</td><td class='r'>{$strv2}</td><td class='r'>{$strG}</td><td class='r'>{$strP}</td><td class='r'>{$strrti}</td></tr>";
                                    }
                                }
                            }
                            $strTotv1 = "$" . number_format($totval1,2);
                            $strTotv2 = "$" . number_format($totval2,2);
                            echo "<tr><td>TOTAL</td><td></td><td></td><td></td><td></td><td class='r'>{$strTotv1}</td><td class='r'>{$strTotv2}</td><td></td><td></td><td></td></tr>";

                            $gn = (($vsum2 / $vsum1) - 1.0) * 100.0;
                            //Now we need to add all the dividends
                            $r = $DB->allPortfolioDividendsForUser($user['iduser']);
                            while ($port = $r->fetch_array(MYSQLI_ASSOC))
                            {
                                $vsum1 -= ($port['portfolio_qty'] * $port['portfolio_price']);
                            }
                            $gn = (($vsum2 / $vsum1) - 1.0) * 100.0;
                            $gn = number_format($gn,2) . "%";

                            //Dividend summary
                            echo "<tr><td colspan='10'></td></tr>";
                            echo "<tr>";
                            echo "<td class='b' colspan='9'>DIVIDENDS</td>";
                            echo"</tr>";

                            $r = $DB->allPortfolioDividendsForUser($user['iduser']);
                            while ($port = $r->fetch_array(MYSQLI_ASSOC))
                            {
                                $strtime = classTimeHelpers::timeFormatnthDateTime1($port['portfolio_timestamp'],"Pacific/Auckland");
                                $v = $port['portfolio_qty'] * $port['portfolio_price'];
                                $strDivValue = "$" . number_format($v,2);
                                echo "<tr><td>{$strtime}</td><td>{$port['stock_code']}</td><td>DIV</td><td></td><td></td><td>{$strDivValue}</td></tr>";    
                            }
                            
                            
                            
                            
                            //Summary

                            echo "<tr><td colspan='10'></td></tr>";
                            echo "<tr>";
                            echo "<td class='b' colspan='9'>TOTAL PORTFOLIO RETURN</td>";
                            echo"</tr>";
                            echo "<tr><td colspan='9'></td><td class='b r'>{$gn}</td></tr>";



                            //Notes:
                            echo "<tr><td colspan='10' class='small' ><sup>1</sup> Note: The RTI calculation assumes a 2% cost of sale in commission</td></tr>";

                            ?>
                        </table>
                    </div>
                    <div id="form">
                        <form method="POST" autocomplete="off" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                            <label for='stockcodeid'>STOCK</label>
                            <select id="stockcodeid" name='stockcode'>
                                <?php
                                $r = $DB->allStock();
                                while ($stock = $r->fetch_array(MYSQLI_ASSOC))
                                {
                                    echo "<option value='{$stock['idstock']}'>{$stock['stock_code']}</option>";
                                }
                                ?>
                            </select>
                            <label for='buysellid'>BUY / SELL / DIVIDEND</label>
                            <select id="buysellid" name='buysell'>
                                    <option value="buy">BUY</option>
                                    <option value="sell">SELL</option>
                                    <option value="div">DIVIDEND</option>
                            </select>
                            
                            <label for='priceid'>PRICE</label>
                            <input id="priceid" type="text" name="price" />
                            <label for='qtyid'>QTY</label>
                            <input id="qtyid" type="text" name="qty" />
                            <input type="submit" name="add" value="CREATE" />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
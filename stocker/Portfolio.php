<?php
require_once dirname(__FILE__) . "/includes/classSecure.php";
require_once dirname(__FILE__) . "/includes/classTime.php";
require_once dirname(__FILE__) . "/includes/classStockerDB.php";
$DB = new stockerDB($devt_environment->getDatabaseParameters());

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
            #listtable th {padding-top: 8px; padding-bottom: 8px;padding-right: 16px;border-top: solid 1px #aaa;border-bottom: solid 1px #aaa;color: #888;}
            #listtable td {padding-top: 8px; padding-bottom: 8px;padding-right: 16px;border-top: solid 1px #aaa;border-bottom: solid 1px #aaa;}
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
    </head>
    <body>
        <div id="container">
            <div id="main">
                <div id="list">
                    <h1>PORTFOLIO</h1>
                    <div id="listtable">
                        <table>
                            <tr><th>TIMESTAMP</th><th>STOCK</th><th></th><th class="'r'">QTY</th><th class="r">UNIT PRICE</th><th class="r">VALUE</th><th class="r">CURRENT VALUE</th><th class="r">GAIN/LOSS</th><th class="r">CHANGE</th><th class="r">RTI<sup>1</sup></th></tr>
                            <?php
                            $exch = $DB->getLastRecord('NZD');
                            $toNZ = $exch['record_value'];

                            $cashSum = 0.0;
                            $r = $DB->allPortFolio(1);
                            while ($port = $r->fetch_array(MYSQLI_ASSOC))
                            {
                                $last = $DB->getLastRecord($port['stock_code']);

                                $strtime = classTimeHelpers::timeFormatnthDateTime1($port['portfolio_timestamp'],"Pacific/Auckland");
                                
                                $class='';
                                if ($port['portfolio_buysell'] != "buy")
                                    $class='gre';
                                
                                echo "<tr class='{$class}'>";

                                echo "<td>{$strtime}</td>";
                                echo "<td>{$port['stock_code']}</td>";
                                if ($port['portfolio_buysell'] == "buy")
                                {
                                    echo "<td>BUY</td>";
                                    $qty = $port['portfolio_qty'];
                                }
                                else
                                {
                                    echo "<td>SOLD</td>";
                                    $qty = -($port['portfolio_qty']);
                                }

                                echo "<td class='r'>{$qty}</td>";

                                //NZD
                                $nzdUnitPrice = number_format($port['portfolio_price'],2);
                                echo "<td class='r'>{$nzdUnitPrice}</td>";


                                $NZDPurchaseValue = $port['portfolio_price'] * $qty;
                                $cashSum += $NZDPurchaseValue;
                                $strNZDPurchaseValue = number_format($NZDPurchaseValue,2);
                                echo "<td class='r'>{$strNZDPurchaseValue}</td>";

                                if ($last['record_currency'] != 'NZD')
                                    $currentPrice = $last['record_value'] * $toNZ;
                                else
                                    $currentPrice = $last['record_value'];

                                $class='green';
                                if ($currentPrice < $port['portfolio_price'])
                                    $class='red';
                                $NZDCurrentValue = $currentPrice * $qty;
                                $strNZDCurrentValue = number_format($NZDCurrentValue,2);
                                if ($port['portfolio_buysell'] == "buy")
                                    echo "<td class='r {$class}'>{$strNZDCurrentValue}</td>";
                                else
                                    echo "<td></td>";

                                $delta = $NZDCurrentValue - $NZDPurchaseValue;
                                $strdelta = number_format($delta,2);

                                $class='green';
                                if ($delta < 0.0)
                                    $class='red';
                                if ($port['portfolio_buysell'] == "buy")
                                    echo "<td class='r {$class}'>{$strdelta}</td>";
                                else
                                    echo "<td></td>";

                                $change1 = (($NZDCurrentValue / $NZDPurchaseValue) - 1.0)*100.0;
                                $strchange1 = number_format($change1,2) . "%";
                                if ($port['portfolio_buysell'] == "buy")
                                    echo "<td class='r {$class}'>{$strchange1}</td>";
                                else
                                    echo "<td></td>";

                                $dtBuy = new DateTime($port['portfolio_timestamp']);
                                $dtNow = new DateTime();

                                $years = floatval($dtNow->getTimestamp() - $dtBuy->getTimestamp()) / (3600.0*24.0*365.0);
                                //Take 2% off current value
                                $NZDModifiedvalue = $NZDCurrentValue * (1.0 - $port['stock_margin'] );

                                $change2 = (pow($NZDModifiedvalue / $NZDPurchaseValue, (1.0/$years)) - 1.0) * 100.0;
                                error_log("RTI Modvalue {$NZDModifiedvalue} Puchvalue {$NZDPurchaseValue} Years {$years}");


                                $strchange2 = number_format($change2,2) . "%";
                                if ($port['portfolio_buysell'] == "buy")
                                    echo "<td class='r {$class}'>{$strchange2}</td>";
                                else
                                    echo "<td></td>";


                                echo "</tr>";


                            }

                            //Cash sum
                            $strCashSum = "$". number_format($cashSum,2);
                            echo "<tr><td>TOTAL CASH</td><td colspan='4'></td><td>{$strCashSum}</td><td colspan='4'></td></tr>";
                            //Calculate portfolio worth
                            $vsum1 = 0.0;
                            $vsum2 = 0.0;
                            $today = (new DateTime())->getTimestamp();

                            $portfolio = array();
                            $r = $DB->allPortfolioBuyForUser(1);
                            while ($port = $r->fetch_array(MYSQLI_ASSOC))
                            {
                                if (! isset($portfolio[$port['portfolio_stock']]))
                                {
                                    $portfolio[$port['portfolio_stock']] = array(); 
                                }
                                array_push($portfolio[$port['portfolio_stock']],$port);
                            }
                            
                            $havesold = false;
                            $r = $DB->allPortfolioSellForUser(1);
                            while ($port = $r->fetch_array(MYSQLI_ASSOC))
                            {
                                //Now calcualte returns on sold stock.
                                if (!$havesold)
                                {
                                    echo "<tr><td colspan='10'></td></tr>";
                                    echo "<tr>";
                                    echo "<td class='b' colspan='9'>RETURNS ON SOLD STOCK</td>";
                                    echo"</tr>";
                                }
                                $stockid = $port['portfolio_stock'];
                                $qty = $port['portfolio_qty'];
                                
                                $cnt = 0;
                                foreach($portfolio[$stockid] as $buy)
                                {
                                    if ($qty > 0)
                                    {
                                        
                                        $v = min($qty,$buy['portfolio_qty']);
                                        $buyprice = $buy['portfolio_price'] * $v;
                                        $sellprice = $port['portfolio_price'] * $v;
                                        $t = ($today - (new DateTime($buy['portfolio_timestamp']))->getTimestamp() ) / (3600*24*365);
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
                            
                            //Now calcualte returns on each stock.
                            echo "<tr><td colspan='10'></td></tr>";
                            echo "<tr>";
                            echo "<td class='b' colspan='9'>REMAINING STOCK</td>";
                            echo"</tr>";

                            
                            $exch = $DB->getLastRecord('NZD');
                            $toNZ = $exch['record_value'];

                            
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
                                        echo "<tr><td>{$strtime}</td><td>{$buy['stock_code']}</td><td></td><td class='r'>{$buy['portfolio_qty']}</td><td></td><td class='r'>{$strv1}</td><td class='r'>{$strv2}</td><td class='r'>{$strG}</td><td class='r'>{$strP}</td><td class='r'>{$strrti}</td></tr>";
                                    }
                                }
                            }

                            //Now we need to add all the sales

                            
                            $gn = (($vsum2 / $vsum1) - 1.0) * 100.0;
                            $gn = number_format($gn,2) . "%";

                            //Dividend summary
                            echo "<tr><td colspan='10'></td></tr>";
                            echo "<tr>";
                            echo "<td class='b' colspan='9'>DIVIDENDS</td>";
                            echo"</tr>";

                            $r = $DB->allPortfolioDividendsForUser(1);
                            while ($port = $r->fetch_array(MYSQLI_ASSOC))
                            {
                                $strtime = classTimeHelpers::timeFormatnthDateTime1($port['portfolio_timestamp'],"Pacific/Auckland");
                                echo "<tr><td>{$strtime}</td><td>{$port['stock_code']}</td><td>DIV</td></tr>";    
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
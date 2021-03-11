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
        </style>
    </head>
    <body>
        <div id="container">
            <div id="main">
                <div id="list">
                    <h1>PORTFOLIO</h1>
                    <div id="listtable">
                        <table>
                            <tr><th>TIMESTAMP</th><th>STOCK</th><th></th><th class="'r'">QTY</th><th class="r">UNIT PRICE</th><th class="r">VALUE</th><th class="r">CURRENT VALUE</th><th class="r">GAIN/LOSS</th><th class="r">CHANGE</th><th class="r">RTI</th></tr>
                            <?php
                            $exch = $DB->getLastRecord('NZD');
                            $toNZ = $exch['record_value'];

                            $sum = array();

                            $r = $DB->allPortFolio();
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
                                $strNZDPurchaseValue = number_format($NZDPurchaseValue,2);
                                echo "<td class='r'>{$strNZDPurchaseValue}</td>";

                                $currentPrice = $last['record_value'] * $toNZ;

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
                                $NZDModifiedvalue = $NZDCurrentValue * 0.98;

                                $change2 = (pow($NZDModifiedvalue / $NZDPurchaseValue, (1.0/$years)) - 1.0) * 100.0;
                                error_log("RTI Modvalue {$NZDModifiedvalue} Puchvalue {$NZDPurchaseValue} Years {$years}");


                                $strchange2 = number_format($change2,2) . "%";
                                if ($port['portfolio_buysell'] == "buy")
                                    echo "<td class='r {$class}'>{$strchange2}</td>";
                                else
                                    echo "<td></td>";


                                echo "</tr>";

                                if (!isset($sum[$port['stock_code']]) )
                                {
                                    $sum[$port['stock_code']] = array();
                                    $sum[$port['stock_code']] ['QTY'] = $qty;
                                    if ($port['portfolio_buysell'] == "buy")
                                        $sum[$port['stock_code']] ['PURCHASE_VALUE'] = $NZDPurchaseValue;
                                    else
                                        $sum[$port['stock_code']] ['SOLD_VALUE'] = $NZDPurchaseValue;

                                    $sum[$port['stock_code']] ['CURRENTPRICE'] = $currentPrice;
                                }
                                else
                                {
                                    $sum[$port['stock_code']] ['QTY'] += $qty;
                                    if ($port['portfolio_buysell'] == "buy")
                                        $sum[$port['stock_code']] ['PURCHASE_VALUE'] += $NZDPurchaseValue;
                                    else
                                        $sum[$port['stock_code']] ['SOLD_VALUE'] += $NZDPurchaseValue;

                                }




                            }

                            //Summary

                            echo "<tr><td colspan='10'></td></tr>";
                            echo "<tr>";
                            echo "<td class='b' colspan='9'>SUMMARY</td>";
                            echo"</tr>";

                            foreach($sum as $code => $v)
                            {

                                $NZDCurrentValue = $v['CURRENTPRICE'] * $v['QTY'];
                                $strNZDCurrentValue = "$" . number_format($NZDCurrentValue,2);
                                $NZDPurchaseValue = $v['PURCHASE_VALUE'];
                                $NZDSoldValue  = $v['SOLD_VALUE'];
                                
                                $strNZDPurchaseValue = "$" . number_format($NZDPurchaseValue,2);
                                $strNZDSoldValue = "$" . number_format($NZDSoldValue,2);
                                
                                $gain = $NZDCurrentValue - ($NZDPurchaseValue + $NZDSoldValue);
                                $strgain = "$" . number_format($gain,2);
                                
                                $class='green';
                                if ($gain < 0.0)
                                    $class='red';
                                
                                echo "<tr>";
                                echo "<td></td><td>{$code}</td><td></td><td>{$v['QTY']}</td><td></td><td></td><td class='r'>{$strNZDCurrentValue}</td><td class='r {$class}'>{$strgain}</td><td></td><td></td>";
                                echo "</tr>";
                            }
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
                            <label for='buysellid'>BUY/SELL</label>
                            <select id="buysellid" name='buysell'>
                                    <option value="buy">BUY</option>
                                    <option value="sell">SELL</option>
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
<?php
session_start();
require_once dirname(__FILE__) . "/includes/classSecure.php";
require_once dirname(__FILE__) . "/includes/classTime.php";
require_once dirname(__FILE__) . "/includes/classStockerDB.php";
$DB = new stockerDB($devt_environment->getDatabaseParameters());

if (! isset($_SESSION['currentstock']))
    $_SESSION['currentstock'] = "BTC";

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


?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>STOCKER</title>
        <style>
            body {
                background-color: black;font-family: Arial, Helvetica, sans-serif;font-size: 10pt;margin: 0;padding: 0;
            }
            #curve_chart {width:1600px; height: 800px;}
            #flexme {display: flex}
            #stats1 {padding: 20px;}
            #stats2 td {color: #ffd800;font-size: 16pt;padding-right: 10px;}
            #stats2 td.green {color: #00ff21;}
            #stats2 td.red {color: #ff0000;}
            #stats3 {border: solid 1px #555;padding: 10px;border-radius: 6px;margin-top: 8px;}
            #stats3 th {color: #202040;font-size: 8pt;padding-right: 10px;}
            #stats3 td {color: #202020;font-size: 8pt;padding-right: 10px;}
            #stats3 td.td1 {color: #5661e3;}
            #stats3 button {margin-top: 10px;}
            #porttable {background-color: #ccc;}
            #porttable table {border-collapse:collapse;font-size: 8pt;}
            #porttable td.green {color: green;}
            #porttable td.red {color: red;}
            #select {border: solid 1px #555;padding: 10px;border-radius: 6px;margin-top: 8px;}
            #select span {color: #aaa;}
            p.hd1 {color: #a6a6ff;font-size: 12pt; font-weight: bold; margin-top: 0;}
            .c {text-align: center;}
            .r {text-align: right;}
        </style>
        <script type="text/javascript" src="/js/apiClass.js"></script>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script>
            var api = new apiJSON("stocker.devt.nz", "stockerApi.php?r=", "", true);
            api.parseReply = function (d) {
                console.log("Reply from API"); 
                if (d.meta.status) {
                    switch (d.meta.req) {
                        case 'graphdata':
                            drawChart(d.data);
                            updateCurrent(d.data)
                            break;
                    }
                }
            }
            var g_graphDuration = "1";
            var g_graphStock = '<?php echo $_SESSION['currentstock'];?>';

            function updateCurrent(d) {
                document.getElementById('_current').innerHTML = d.history.strings.LAST;
                document.getElementById('_day').innerHTML = d.history.change.strings.D1;
                if (d.history.change.values.D1 >= 0)
                    document.getElementById('_day').className = 'r green';
                else
                   document.getElementById('_day').className = 'r red';

                document.getElementById('_week').innerHTML = d.history.change.strings.D7;
                if (d.history.change.values.D7 >= 0)
                    document.getElementById('_week').className = 'r green';
                else
                    document.getElementById('_week').className = 'r red';

                document.getElementById('_month').innerHTML = d.history.change.strings.D28;
                if (d.history.change.values.D28 >= 0)
                    document.getElementById('_month').className = 'r green';
                else
                    document.getElementById('_month').className = 'r red';
                            document.getElementById('_month').innerHTML = d.history.change.strings.D28;

                document.getElementById('_hour').innerHTML = d.history.change.strings.H1;
                if (d.history.change.values.H1 >= 0)
                    document.getElementById('_hour').className = 'r green';
                else
                    document.getElementById('_hour').className = 'r red';
            }

            google.charts.load('current', { 'packages': ['corechart'] });
            function drawChart(d) {
                var d2 = d.graphdata;
                for (var i = 0; i < d2.length; i++) {
                    if (i > 0) {
                        d2[i][0] = new Date(d2[i][0]);
                    }
                }
                var data = google.visualization.arrayToDataTable(d2);
                var options = {
                  title: d.title,
                  curveType: 'function',
                  height: 800,
                  width: 1600,
                  //series: ser,
                  backgroundColor: '#000000',
                  titleTextStyle: {color: '#b0b0ff'},
                  //vAxis: {textStyle: {color: '#ffe000'}, ticks: [0,2,4,6,8,10,12,14,16,18,20,22] },
                  vAxis: {textStyle: {color: '#ffe000'}},
                  hAxis: {textStyle: {color: '#ffe000'}},
                  legend: { position: 'bottom',textStyle: {color: '#e0e0e0', fontSize: 12} }
                };

                var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
                chart.draw(data, options);

            }

            function stockChange(n) {
                console.log("Stock change value " + n.value);
                g_graphStock = n.value;
                setSession('currentstock', g_graphStock);
                api.queueReq("GET", "graphdata/" + g_graphStock + "/" + g_graphDuration);
            }

            function selectGraph(n) {
                if (n.checked) {
                    g_graphDuration = String(n.value);
                    api.queueReq("GET", "graphdata/" + g_graphStock + "/" + g_graphDuration);
                }
            }

            function tick() {
                console.log("Tick");
                window.location = "Chart.php";
            }

            function setSession(n, v) {
                var p = { name: n, value: v };
                api.queueReq("POST", "setsession",p);
            }

            function start() {
                setInterval(tick, 300000);
                api.queueReq("GET", "graphdata/" + g_graphStock + "/" + g_graphDuration);
            }
        </script>
    </head>
<body onload="start()">
    <div id="container">
        <div id="flexme">
            <div id="stats1">
                <?php
                    $rec1 = $DB->firstXDaysBach($_SESSION['currentstock'],1);
                    $rec2 = $DB->firstXDaysBach($_SESSION['currentstock'],7);
                    $rec3 = $DB->firstXDaysBach($_SESSION['currentstock'],28);
                    $rec4 = $DB->firstXHoursBach($_SESSION['currentstock'],1);
                    $last = $DB->getLastRecord($_SESSION['currentstock']);
                    $range = $DB->range($_SESSION['currentstock'],28);
                    $exch = $DB->getLastRecord('NZD');
                    if ($rec1 && $last)
                    {
                        $change1 = (($last['record_value'] / $rec1['record_value']) - 1)*100.0;
                        $change2 = (($last['record_value'] / $rec2['record_value']) - 1)*100.0;
                        $change3 = (($last['record_value'] / $rec3['record_value']) - 1)*100.0;
                        $change4 = (($last['record_value'] / $rec4['record_value']) - 1)*100.0;
                        $strchange1 = number_format($change1,2) . "%";
                        $strchange2 = number_format($change2,2) . "%";
                        $strchange3 = number_format($change3,2) . "%";
                        $strchange4 = number_format($change4,2) . "%";
                        $current = number_format($last['record_value'],2);
                        $strRange = "";

                        if ($range && isset($range['min']) && $range['min'] > 0)
                            $strRange = number_format((($range['max'] / $range['min']) - 1.0) * 100.0,2) . "%";
                        $class='g';
                        if ($change1 < 0)
                              $class ='r';

                        echo "<div id='stats2'>";
                        echo "<table>";
                        $strWhen  = classTimeHelpers::timeFormat($last['record_timestamp'],"H:i","Pacific/Auckland");
                        echo "<tr><td>LAST UPDATE</td><td class='r'>{$strWhen}</td></tr>";
                        echo "<tr><td>CURRENT</td><td id='_current' class='{$class}'>{$current}</td></tr>";

                        //Day
                        $class='green';
                        if ($change1 < 0)
                            $class ='red';
                        echo "<tr><td>DAY</td><td id='_day' class='r {$class}'>{$strchange1}</td></tr>";

                        //Week
                        $class='green';
                        if ($change2 < 0)
                            $class ='red';
                        echo "<tr><td>WEEK</td><td id='_week' class='r {$class}'>{$strchange2}</td></tr>";

                        //Month
                        $class='green';
                        if ($change3 < 0)
                            $class ='red';
                        echo "<tr><td>28 DAYS</td><td id='_month' class='r {$class}'>{$strchange3}</td></tr>";

                        //Hour
                        $class='green';
                        if ($change4 < 0)
                            $class ='red';
                        echo "<tr><td>LAST HOUR</td><td id='_hour' class='r {$class}'>{$strchange4}</td></tr>";
                        $class='green';
                        echo "<tr><td>28 DAY CHANGE</td><td id='_change' class='r {$class}'>{$strRange}</td></tr>";

                        if ($exch)
                        {
                            $strexch = number_format(1.0 / $exch['record_value'],4);
                            echo "<tr><td>EXCHANGE RATE</td><td class='r'>{$strexch}</td></tr>";

                        }


                        echo "</table>";
                        echo "</div>";
                        //Portfolio
                        echo "<div id='stats3'>";
                        echo "<p class='hd1'>PORTFOLIO</p>";
                        echo "<div id='porttable'>";
                        echo "<table>";
                        echo "<tr><th>CODE</th><th class='r'>QTY</th><th class='r'>VALUE</th><th class='r'>GAIN</th></tr>";
                        $summary = array();
                        $exch = $DB->getLastRecord('NZD');
                        $toNZ = $exch['record_value'];


                        $r = $DB->allPortFolio($user['iduser']);
                        while ($stock = $r->fetch_array())
                        {

                            $last = $DB->getLastRecord($stock['stock_code']);
                            if ($last['record_currency'] != 'NZD')
                                $currentPrice = $last['record_value'] * $toNZ;
                            else
                                $currentPrice = $last['record_value'];


                            $qty = 0.0;
                            if ($stock['portfolio_buysell'] == 'buy')
                                $qty = $stock['portfolio_qty'];
                            elseif ($stock['portfolio_buysell'] == 'sell')
                                $qty = -($stock['portfolio_qty']);

                            $NZDPurchaseValue = $stock['portfolio_price'] * $qty;

                            if (!isset($summary[$stock['stock_code']]) )
                            {
                                $summary[$stock['stock_code']] = array();
                                $summary[$stock['stock_code']] ['QTY'] = $qty;
                                $summary[$stock['stock_code']] ['CURRENTPRICE'] = $currentPrice;
                                if ($stock['portfolio_buysell'] == 'buy')
                                {
                                    $summary[$stock['stock_code']] ['PURCHASE_VALUE'] = $NZDPurchaseValue;
                                    $summary[$stock['stock_code']] ['SOLD_VALUE'] = 0.0;
                                }
                                elseif ($stock['portfolio_buysell'] == 'sell')
                                {
                                    $summary[$stock['stock_code']] ['PURCHASE_VALUE'] = 0.0;
                                    $summary[$stock['stock_code']] ['SOLD_VALUE'] = $NZDPurchaseValue;
                                }
                            }
                            else
                            {
                                $summary[$stock['stock_code']] ['QTY'] += $qty;
                                if ($stock['portfolio_buysell'] == 'buy')
                                    $summary[$stock['stock_code']] ['PURCHASE_VALUE'] += $NZDPurchaseValue;
                                else
                                    $summary[$stock['stock_code']] ['SOLD_VALUE'] += $NZDPurchaseValue;
                            }


                        }

                        foreach($summary as $name => $v)
                        {
                            echo "<tr>";
                            echo "<td>{$name}</td>";

                            echo "<td class='r'>{$v['QTY']}</td>";

                            $NZDCurrentValue = $v['CURRENTPRICE'] * $v['QTY'];
                            $strNZDCurrentValue = "$" . number_format($NZDCurrentValue,2);
                            echo "<td class='r'>{$strNZDCurrentValue}</td>";

                            $NZDPurchaseValue = $v['PURCHASE_VALUE'];
                            $NZDSoldValue  = $v['SOLD_VALUE'];

                            $strNZDPurchaseValue = "$" . number_format($NZDPurchaseValue,2);
                            $strNZDSoldValue = "$" . number_format($NZDSoldValue,2);

                            $gain = $NZDCurrentValue - ($NZDPurchaseValue + $NZDSoldValue);
                            $strgain = "$" . number_format($gain,2);

                            $class='green';
                            if ($gain < 0.0)
                                $class='red';


                            echo "<td class='r {$class}'>{$strgain}</td>";

                            echo "</tr>";


                        }


                        echo "</table>";
                        echo "</div>";
                        echo "<div id='portoptions'>";
                        echo "<button type'button' onclick='window.location=\"Portfolio.php\"'>EDIT PORTFOLIO</button>";
                        echo "</div>";
                        echo "</div>";
                    }
                ?>
                <div id="select">
                    <p class='hd1'>GRAPH OPTIONS</p>
                    <select onchange="stockChange(this)">
                        <?php
                        $r = $DB->allStock();
                        while ($stock = $r->fetch_array(MYSQLI_ASSOC))
                        {
                            echo "<option value='{$stock['stock_code']}'";
                            if ($_SESSION['currentstock'] == $stock['stock_code'])
                                echo " selected";
                            echo ">{$stock['stock_code']}</option>";
                        }
                        ?>
                    </select>
                    <div><input type="radio" name="graphSpan[]" value="1" checked onchange="selectGraph(this)" /><span>1D</span></div>
                    <div><input type="radio" name="graphSpan[]" value="7" onchange="selectGraph(this)" /><span>7D</span></div>
                    <div><input type="radio" name="graphSpan[]" value="28" onchange="selectGraph(this)" /><span>28D</span></div>
                </div>
            </div>
            <div id="curve_chart"></div>
        </div>
    </div>
</body>
</html>
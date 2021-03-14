<?php
require_once dirname(__FILE__) . "/includes/classSecure.php";
require_once dirname(__FILE__) . "/includes/classTime.php";
require_once dirname(__FILE__) . "/includes/classStockerDB.php";
$DB = new stockerDB($devt_environment->getDatabaseParameters());

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
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script>
            var g_graphDuration = "1";
            var g_graphStock = "BTC";
            var g_data = {
                "BTC": {
                    "1": [
                          [{ "label": "Time", "type": "date" }, "Value"], 
                        //["Time", "Value"],
                    <?php
                    $r = $DB->LastRecordsForStock('BTC',1);
                    while ($record = $r->fetch_array(MYSQLI_ASSOC))
                    {
                        $dt = new DateTime($record['record_timestamp']);
                        $dt->setTimezone(new DateTimeZone('Pacific/Auckland'));
                        $strDate = $dt->format("Y,m,d,H,i,s");
                        echo "[new Date({$strDate}),{$record['record_value']}],";
                    }
                    ?>
                    ],
                    "7": [
                          [{ "label": "Time", "type": "date" }, "Value"], 
                    <?php
                    $r = $DB->LastRecordsForStock('BTC',7);
                    while ($record = $r->fetch_array(MYSQLI_ASSOC))
                    {
                        $dt = new DateTime($record['record_timestamp']);
                        $dt->setTimezone(new DateTimeZone('Pacific/Auckland'));
                        $strDate = $dt->format("Y,m,d,H,i,s");
                        echo "[new Date({$strDate}),{$record['record_value']}],";
                    }
                    ?>
                    ]
                },
                "NZD": {
                    "1": [
                        ["Time", "Value"],
                    <?php
                    $r = $DB->LastRecordsForStock('NZD',1);
                    while ($record = $r->fetch_array(MYSQLI_ASSOC))
                    {
                        $dt = new DateTime($record['record_timestamp']);
                        $x = $dt->getTimestamp() / (3600*24);
                        echo "[{$x},{$record['record_value']}],";
                    }
                    ?>
                    ],
                    "7": [
                        ["Time", "Value"],
                    <?php
                    $r = $DB->LastRecordsForStock('NZD',7);
                    while ($record = $r->fetch_array(MYSQLI_ASSOC))
                    {
                        $dt = new DateTime($record['record_timestamp']);
                        $x = $dt->getTimestamp() / (3600*24);
                        echo "[{$x},{$record['record_value']}],";
                    }
                    ?>
                    ]
                }
            };
            google.charts.load('current', { 'packages': ['corechart'] });
            function drawChart(d) {
                var data = google.visualization.arrayToDataTable(d);
                var options = {
                  title: 'BitCoin $US',
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
                drawChart(g_data[g_graphStock] [g_graphDuration]);
            }

            function selectGraph(n) {
                if (n.checked) {
                    g_graphDuration = String(n.value);
                    drawChart(g_data[g_graphStock] [g_graphDuration]);
                }
            }

            function tick() {
                console.log("Tick");
                window.location = "Chart.php";
            }

            function start() {
                setInterval(tick, 300000);
                drawChart(g_data[g_graphStock] [g_graphDuration]);
            }
        </script>
    </head>
<body onload="start()">
    <div id="container">
        <div id="flexme">
            <div id="stats1">
                <?php
                    $rec1 = $DB->firstXDaysBach('BTC',1);
                    $rec2 = $DB->firstXDaysBach('BTC',7);
                    $rec3 = $DB->firstXDaysBach('BTC',28);
                    $rec4 = $DB->firstXHoursBach('BTC',1);
                    $last = $DB->getLastRecord('BTC');
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
                        $class='g';
                        if ($change1 < 0)
                              $class ='r';

                        echo "<div id='stats2'>";
                        echo "<table>";
                        $strWhen  = classTimeHelpers::timeFormat($last['record_timestamp'],"H:i","Pacific/Auckland");
                        echo "<tr><td>LAST UPDATE</td><td class='r'>{$strWhen}</td></tr>";
                        echo "<tr><td>CURRENT</td><td class='{$class}'>{$current}</td></tr>";

                        //Day
                        $class='green';
                        if ($change1 < 0)
                            $class ='red';
                        echo "<tr><td>DAY</td><td class='r {$class}'>{$strchange1}</td></tr>";

                        //Week
                        $class='green';
                        if ($change2 < 0)
                            $class ='red';
                        echo "<tr><td>WEEK</td><td class='r {$class}'>{$strchange2}</td></tr>";

                        //Month
                        $class='green';
                        if ($change3 < 0)
                            $class ='red';
                        echo "<tr><td>28 DAYS</td><td class='r {$class}'>{$strchange3}</td></tr>";

                        //Hour
                        $class='green';
                        if ($change4 < 0)
                            $class ='red';
                        echo "<tr><td>LAST HOUR</td><td class='r {$class}'>{$strchange4}</td></tr>";
                        
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


                        $r = $DB->allPortFolio();
                        while ($stock = $r->fetch_array())
                        {

                            $last = $DB->getLastRecord($stock['stock_code']);
                            $currentPrice = $last['record_value'] * $toNZ;

                            $qty = 0.0;
                            if ($stock['portfolio_buysell'] == 'buy')
                                $qty = $stock['portfolio_qty'];
                            else
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
                                else
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
                            echo "<option value='{$stock['stock_code']}'>{$stock['stock_code']}</option>";
                        }
                        ?>
                    </select>
                    <div><input type="radio" name="graphSpan[]" value="1" checked onchange="selectGraph(this)" /><span>1D</span></div>
                    <div><input type="radio" name="graphSpan[]" value="7" onchange="selectGraph(this)" /><span>7D</span></div>
                </div>
            </div>
            <div id="curve_chart"></div>
        </div>
    </div>
</body>
</html>
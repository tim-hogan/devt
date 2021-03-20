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

if (isset($_GET['p']))
{
    $portid = $_GET['p'];
    $port = $DB->getPortfolio($portid);
    $stock = $DB->getStockById(intval($port['portfolio_stock']));
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
            #curve_chart {height: 800px;width: 1600px;}
        </style>
        <script type="text/javascript" src="/js/apiClass.js"></script>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script>
            var g_params = {
                stock: <?php echo intval($port['portfolio_stock']);?>,
                backdays: 28,
                fwddays: 28,
                buyprice: <?php echo floatval($port['portfolio_price']);?>,
                buydate: '<?php echo $port['portfolio_timestamp'];?>',
                rti: 1,
                ratecommision: <?php echo floatval($stock['stock_margin']);?>};

            var api = new apiJSON("stocker.devt.nz", "stockerApi.php?r=", "", true);
            api.parseReply = function (d) {
                console.log("Reply from API"); 
                if (d.meta.status) {
                    switch (d.meta.req) {
                        case 'future':
                            drawChart(d.data);
                            break;
                    }
                }
            }

            function getData() {
                api.queueReq("POST", "future",g_params);
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

        </script>
    </head>
    <body onload="getData()">
        <div id="curve_chart"></div>
    </body>
</html>


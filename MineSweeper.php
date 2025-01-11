<?php

?>
<html>
    <head>
        <script type="text/javascript" src="js/MineSweeper.js"></script>
        <script>
            var g_board = null;
            function modeset(v) {
                g_board.setMode(v);
            }
            function hitme(n) {
                if (g_board) {
                    let v = parseInt(ms.ge("setval").value);
                    g_board.hitme(n,v);

                }
            }
            function buildrandon() {
                g_board.createRandom(99);
                g_board.setMode("puz");
                g_board.display();
            }
            function unhide() {
                g_board.unhideAll();
                g_board.display();
            }
            function solveone() {
                if (g_board) {
                    g_board.solveOne();
                }
            }
            function start() {
                g_board = new MineSweeper(30, 16, "board");
            }

        </script>
        <style>
            body {margin: 0;font-family: Arial, Helvetica, sans-serif;font-size: 9pt;}
            #gamespace {margin: 20px;}
            div.cell {display:inline-block; width:16px; height: 16px; padding: 0;border: solid 2px #333;border-top-color: #f8f8f8; border-left-color: #f8f8f8; background-color: #eee;}
            div.s_open {display:inline-block; vertical-align: top; text-align: center; width: 16px;  height: 16px; padding: 0;border: solid 2px #eee;background-color: #eee;font-weight: bold;}
            div.s_mine {display:inline-block; vertical-align: top; text-align: center; width: 16px;  height: 16px; padding: 0;border: solid 2px #333;border-top-color: #f8f8f8; border-left-color: #f8f8f8;background-color: #eee;font-weight: bold;}
            div._1 {color: blue;}
            div._2 {color: green;}
            div._3 {color: red;}
        </style>
    </head>
    <body onload="start()">
        <div id="container">
            <div id="heading">
                <h1>MINESWEEPER</h1>
            </div>
            <div id="main">
                <div id="gamespace">
                    <div id="board">
                    </div>
                </div>
                <div id="actions">
                    <button onclick="modeset('set')">SET</button>
                    <select id="setval">
                        <option value="0">BLANK</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">MINE</option>
                        <option value="-1">HIDDEN</option>"
                    </select>
                    <button onclick="buildrandon()">Build Randon</button>
                    <button onclick="solveone()">SOLVE SINGLE</button>
                    <button onclick="unhide()">UNHDE</button>
                </div>
            </div>
        </div>
    </body>
</html>
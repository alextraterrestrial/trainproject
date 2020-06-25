<!DOCTYPE html>
<html>
<head>
    <title> Tågtidtabellen </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <link href="https://fonts.googleapis.com/css?family=Share+Tech+Mono|Source+Sans+Pro|Monoton" rel="stylesheet">  
    <link rel='stylesheet' type='text/css' href='main.css'>
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css">
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script src="script.js"></script> 

</head>
	  <body onload="checkCookie()">
        <div id="cookie" style="display: none">
            <p>Den här sidan använder sig av cookie. Genom att klicka "ok" så godkänner du
            att cookies sparas på din dator</p>
            <br>
            <input type="button" value="Ok" onclick='closewindow()'>
        </div>
        <div class="main_container">
            <div class="header">
                <h1>Tågtidtabellen</h1>
            </div>
            <?php
            //Kontrollerar ifall en tidigare sökning finns sparad i cookies
            //Och hämtar in sökningen i POST
            if(isset($_COOKIE['tostation'])){
                $_POST['to_station'] = $_COOKIE['tostation'];
                $_POST['from_station'] = $_COOKIE['fromstation'];
            }
            elseif(isset($_COOKIE['fromstation'])){
                $_POST['from_station'] = $_COOKIE['fromstation'];
            }
            
            //Kontrollerar ifall det gjorts en sökning och resultat ska visas
            //Eller ifall sök-innehållet ska visas
            if(isset($_POST['from_station'])){
                include 'result.php';
            }
            else{
                include 'search.php';
            }
            ?>
        </div>
	  </body>
</html>

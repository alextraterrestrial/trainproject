
<div class="result">
    <div class="menu">
        <a href="unsetsearch.php">
            <input type="button" value="Ny sökning">
        </a>
    </div>
    <div class="search_query">
        <?php
        //API-nyckel och include av funktioner i trafikinfo.php
        $key='461d643d710f4c4c84675b53b18dbf7a';
        include 'trafikinfo.php';

        //Kontrollerar om det är en sökning från en station till en annan
        if(isset($_POST['to_station'])){
            $from_station_name = $_POST['from_station'];
            $to_station_name = $_POST['to_station'];

        $from_station_code = station_code($from_station_name);
        $to_station_code = station_code($to_station_name);

        //Set cookie to_station and from_station
        setcookie('tostation', $to_station_name, time() + 60 * 60 * 24 * 7);    
        setcookie('fromstation', $from_station_name, time() + 60 * 60 * 24 * 7);

        //Variabeln som tar emot resultatet    
        $timetable = next_train_between($from_station_code,$to_station_code,-60,600);    

        //Skapar en header div   
        echo "<div id='header'>";
        //Printar ut frånstation och tillstation    
        echo "<h2>Från: $from_station_name till: $to_station_name </h2>";
        echo "</div>";
        
        //Skapar en container div     
        echo "<div class='table' id='journey_table'>";    
        echo "<div class='table_header' id='departure'><p>Avgånstid</p></div>" . "<div class='table_header' id='newtime'><p>Ny tid</p></div>" . "<div  class='table_header' id='track'><p>Spår</p></div>" . "<div class='table_header' id='train'><p>Tåg</p></div>" . "<div class='table_header' id='info'><p>Anmärkning</p></div>";
        
        //Iterator för att loopa igenom 10 avgångar    
        $i = 0;    
        foreach($timetable as $departure){
            $advertised_time = short_time($departure['AdvertisedTimeAtLocation']);
            $estimated_time = short_time($departure['EstimatedTimeAtLocation']);
            $time = strtotime(date('H:i'));
            
            if($departure['TimeStamp'] >= $time and $i < 10){
                if($estimated_time != ""){
                 echo "<p id='crossed'>$advertised_time</p>";
                }
                else{
                    echo "<p>$advertised_time</p>";
                }
                 echo "<p id='estimated_time'>$estimated_time</p>" .    
                 "<p>$departure[TrackAtLocation]</p>" .
                 "<p>$departure[InformationOwner]</p>" .
                 "<p>$departure[Canceled]</p>";
                $i++;
            }
        }   
        echo "</div>";    
        }
        
        
        // Resultatet om alla avgångar från viss station ska visas
        else{
            $from_station_name = $_POST['from_station'];
            $from_station_code = station_code($from_station_name);
            
            //Set cookie from_station
            setcookie('fromstation', $from_station_name, time() + 60 * 60 * 24 * 7);

            //Variabeln som tar emot resultatet alla avångar en timme fram och en timme bak
            $timetable = next_train($from_station_code,-60,600);

            //Printar ut namnet på stationen som tabellen visar
            echo "<div id='header'>" . 
            "<h2>Avgångar från $from_station_name</h2>" .
            "</div>";    
                
            //Skapar en container div
            echo "<div class='table' id='departure_table'>";
            echo "<div class='table_header' id='departure'><p>Avgånstid</p></div>" . "<div class='table_header' id='track'><p>Spår</p></div>" . "<div class='table_header' id='destination'><p>Slutstation</p></div>" .
            "<div class='table_header' id='train'><p>Tåg</p></div>";
            
            
            //Printar ut resultatet från arrayen med en loop. Men endast de avångar som inte är inställda, inte redan avgått
            // och max 10 resultat
            $i = 0;
            foreach($timetable as $departure){
                 $time = strtotime(date('H:i'));
                
                 if($departure['TimeStamp'] >= $time and $departure['Canceled'] == "" and $i < 10){
                  echo "<p>$departure[ActualTime]</p>" .
                  "<p>$departure[TrackAtLocation]</p>" .
                  "<p>$departure[ToLocationName]</p>" .    
                  "<p>$departure[InformationOwner]</p>";
                  $i++;
                 }
             }
        echo "</div>";   
        }
        ?>
    </div>
</div>


<?php
//API-nyckel och include av funktioner i trafikinfo.php
$key='461d643d710f4c4c84675b53b18dbf7a';
include 'trafikinfo.php';
?>
<script>
//    Script som hämtar stationerna och gör autofill
    var Stations = new Array();
        $(document).ready(function () {
            $.support.cors = true; // Enable Cross domain requests
            try {
                $.ajaxSetup({
                    url: "http://api.trafikinfo.trafikverket.se/v1/data.json",
                    error: function (msg) {
                        if (msg.statusText == "abort") return;
                        alert("Request failed: " + msg.statusText + "\n" + msg.responseText);
                    }
                });
            }
            catch (e) { alert("Ett fel uppstod vid initialisering."); }
            // Create an ajax loading indicator
            var loadingTimer;
            $("#loader").hide();
            $(document).ajaxStart(function () {
                loadingTimer = setTimeout(function () {
                    $("#loader").show();
                }, 200);
            }).ajaxStop(function () {
                clearTimeout(loadingTimer);
                $("#loader").hide();
            });
            // Load stations
            PreloadTrainStations();
        });
    
        function PreloadTrainStations() {
            // Request to load all stations
            var xmlRequest = "<REQUEST>" +
                                // Use your valid authenticationkey
                                "<LOGIN authenticationkey='461d643d710f4c4c84675b53b18dbf7a' />" +
                                "<QUERY objecttype='TrainStation'>" +
                                    "<FILTER/>" +
                                    "<INCLUDE>Prognosticated</INCLUDE>" +
                                    "<INCLUDE>AdvertisedLocationName</INCLUDE>" +
                                    "<INCLUDE>LocationSignature</INCLUDE>" +
                                "</QUERY>" +
                             "</REQUEST>";
            $.ajax({
                type: "POST",
                contentType: "text/xml",
                dataType: "json",
                data: xmlRequest,
                success: function (response) {
                    if (response == null) return;
                    try {
                        var stationlist = [];
                        $(response.RESPONSE.RESULT[0].TrainStation).each(function (iterator, item)
                        {
                            // Save a key/value list of stations
                            Stations[item.LocationSignature] = item.AdvertisedLocationName;
                            // Create an array to fill the search field autocomplete.
                            if (item.Prognosticated == true)
                                stationlist.push({ label: item.AdvertisedLocationName, value: item.LocationSignature });
                        }); 
                        fillSearchWidget(stationlist);
                    }
                    catch (ex) { }
                }
            });  
        };
        function fillSearchWidget(data) {
            $(".from_field").val("");
            $("#to_field").val("");
            $(".from_field").autocomplete({
                // Make the autocomplete fill with matches that "starts with" only
                source: function (request, response) {
                    var matches = $.map(data, function (tag) {
                        if (tag.label.toUpperCase().indexOf(request.term.toUpperCase()) === 0) {
                            return {
                                label: tag.label,
                                value: tag.value
                            }
                        }
                    });
                    response(matches);
                },
                select: function (event, ui) {
                    var selectedObj = ui.item;
                    $("from_field").val(selectedObj.label);
                    // Save selected stations signature
                    $(".from_field").data("sign", selectedObj.value);
                    return false;
                },
                focus: function (event, ui) {
                    var selectedObj = ui.item;
                    // Show station name in search field
                    $(".from_field").val(selectedObj.label);
                    return false;
                }
            })
            $("#to_field").autocomplete({
                // Make the autocomplete fill with matches that "starts with" only
                source: function (request, response) {
                    var matches = $.map(data, function (tag) {
                        if (tag.label.toUpperCase().indexOf(request.term.toUpperCase()) === 0) {
                            return {
                                label: tag.label,
                                value: tag.value
                            }
                        }
                    });
                    response(matches);
                },
                select: function (event, ui) {
                    var selectedObj = ui.item;
                    $("#to_field").val(selectedObj.label);
                    // Save selected stations signature
                    $("#to_field").data("sign", selectedObj.value);
                    return false;
                },
                focus: function (event, ui) {
                    var selectedObj = ui.item;
                    // Show station name in search field
                    $("#to_field").val(selectedObj.label);
                    return false;
                }
            }                              
            );
        }
</script>
<div class="menu">
    <div id='from_to'>
        <input type="radio" id='show_hide_from_to' checked onchange='show_hide()'  name="from">
        <p>Åk från en viss station till en annan</p>
    </div>
    <div id='from'>
        <input type="radio" onchange='show_hide()' id='show_hide_from' name="from">
        <p>Lämna stan med nästa tåg</p>
    </div>
</div>
<div class="search_form">
    <div id="search_from_to">
        <form name="form_from_to" action="index.php" method="post">
            <h3>Från:</h3>
            <input type="text" class="from_field" name="from_station">
            <h3>Till:</h3>
            <input type="text" id="to_field" name="to_station">
            <input id="submit" type="submit" value="Visa avgångar">
        </form>
    </div>
    <div id="search_from" style="display:none">
        <form name="form_from" action="index.php" method="post">
            <h3>Från:</h3>
            <input type="text" class="from_field" name="from_station">
            <span id="loader" style="margin-left: 10px">Laddar data ...</span>
            <input id="submit" type="submit" value="Visa avgångar">
        </form>
   </div>
</div>




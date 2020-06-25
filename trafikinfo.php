<?php
$trafikinfo_allstations=prefetch();
$trafikinfo_allstations_extended=prefetch_extended();


function prefetch() {
  //sorted array of stations and codes
  global $key;
  $xml_data="<REQUEST>" .
  "<LOGIN authenticationkey='$key' />" .
  "<QUERY objecttype='TrainStation'>" .
  "<FILTER>" .
  //"<EQ name='Prognosticated' value='true' />" .
  //"<WITHIN name='Geometry.SWEREF99TM' shape='center' value='374034 6164458' radius='150000' />" .
  "</FILTER>" .
  "<INCLUDE>AdvertisedLocationName</INCLUDE>" .
  "<INCLUDE>LocationSignature</INCLUDE>" .
  "<INCLUDE>CountryCode</INCLUDE>" .
  "<INCLUDE>Geometry.SWEREF99TM</INCLUDE>" .
  "</QUERY>" .
  "</REQUEST>";
  $json=request($xml_data);
  $php1=json_decode($json,true);
  $php2=$php1['RESPONSE']['RESULT'][0]['TrainStation'];
  $php3=array();
  foreach ($php2 as $station) {
    $php3[$station['LocationSignature']]=$station['AdvertisedLocationName'];
  }
  asort($php3);
  return $php3;
}

function prefetch_extended() {
  //unsorted array of station codes, names and geometry
  global $key;
  $xml_data="<REQUEST>" .
  "<LOGIN authenticationkey='$key' />" .
  "<QUERY objecttype='TrainStation'>" .
  "<FILTER>" .
  //"<EQ name='Prognosticated' value='true' />" .
  //"<WITHIN name='Geometry.SWEREF99TM' shape='center' value='374034 6164458' radius='150000' />" .
  "</FILTER>" .
  "<INCLUDE>AdvertisedLocationName</INCLUDE>" .
  "<INCLUDE>LocationSignature</INCLUDE>" .
  "<INCLUDE>CountryCode</INCLUDE>" .
  "<INCLUDE>Geometry.SWEREF99TM</INCLUDE>" .
  "</QUERY>" .
  "</REQUEST>";
  $json=request($xml_data);
  $php1=json_decode($json,true);
  $php2=$php1['RESPONSE']['RESULT'][0]['TrainStation'];
  $php3=array();
  foreach ($php2 as $station) {
    $php3[$station['LocationSignature']]=$station;
  }
  return $php3;
}

function stations_near($station_code,$radius) {
  global $key;
  global $trafikinfo_allstations;
  global $trafikinfo_allstations_extended;
  $station=$trafikinfo_allstations_extended[$station_code];
  $geometry=substr($station['Geometry']['SWEREF99TM'],7,-1);
  //  $test=substr($test,0,-1);
  $xml_data="<REQUEST>" .
  "<LOGIN authenticationkey='$key' />" .
  "<QUERY objecttype='TrainStation'>" .
  "<FILTER>" .
  //"<EQ name='Prognosticated' value='true' />" .
  "<WITHIN name='Geometry.SWEREF99TM' shape='center' value='$geometry' radius='$radius' />" .
  "</FILTER>" .
  "<INCLUDE>Prognosticated</INCLUDE>" .
  "<INCLUDE>AdvertisedLocationName</INCLUDE>" .
  "<INCLUDE>LocationSignature</INCLUDE>" .
  "<INCLUDE>CountryCode</INCLUDE>" .
  "<INCLUDE>Geometry.SWEREF99TM</INCLUDE>" .
  "</QUERY>" .
  "</REQUEST>";
  $json=request($xml_data);
  $php1=json_decode($json,true);
  $php2=$php1['RESPONSE']['RESULT'][0]['TrainStation'];
  $php3=array();
  foreach ($php2 as $station) {
    $php3[$station['LocationSignature']]=$station['AdvertisedLocationName'];
  }
  asort($php3);
  return $php3;
}

function station_name($code) {
  global $trafikinfo_allstations;
  return $trafikinfo_allstations[$code];
}

function station_code($name) {
  global $trafikinfo_allstations;
  return array_search($name,$trafikinfo_allstations);
}

function next_train_between($from,$to,$start,$end) {
  //start och end i minuter
  global $key;
  global $trafikinfo_allstations;
  global $trafikinfo_allstations_extended;

  $startstring=convert_time($start);
  $endstring=convert_time($end);


  $xml_data="<REQUEST>" .
  "<LOGIN authenticationkey='ec0d7a06fea94d4882f8b67c15b3f31a' />" .
  "<QUERY objecttype='TrainAnnouncement' " .
  "orderby='AdvertisedTimeAtLocation' >" .
  "<FILTER>" .
    "<AND>" .
      "<OR>" .
        "<AND>" .
          "<GT name='AdvertisedTimeAtLocation' " . "value='\$dateadd($startstring)' />" .
          "<LT name='AdvertisedTimeAtLocation' " . "value='\$dateadd($endstring)' />" .
        "</AND>" .
        "<AND>" .
          "<GT name='EstimatedTimeAtLocation' " . "value='\$dateadd($startstring)' />" .
          "<LT name='EstimatedTimeAtLocation' " . "value='\$dateadd($endstring)' />" .
        "</AND>" .
      "</OR>" .
      "<EQ name='LocationSignature' value='" . "$from" . "' />" .
      "<EQ name='ActivityType' value='Avgang' />" .
      "<EQ name='Advertised' value='true' />" .
      "<OR>" .
        "<EQ name='ViaToLocation.LocationName' value='$to' />" .
        "<EQ name='ToLocation.LocationName' value='$to' />" .
      "</OR>" .
    "</AND>" .
  "</FILTER>" .
  // Just include wanted fields to reduce response size.
  "<INCLUDE>EstimatedTimeAtLocation</INCLUDE>" . //beräknad tid om försenat
  "<INCLUDE>AdvertisedTimeAtLocation</INCLUDE>" . //tidtabellstid
  "<INCLUDE>Canceled</INCLUDE>" .
  "<INCLUDE>TrackAtLocation</INCLUDE>" .
  "<INCLUDE>InformationOwner</INCLUDE>" .
  "</QUERY>" .
  "</REQUEST>";
  $json=request($xml_data);
  $php1=json_decode($json,true);
  $php2=$php1['RESPONSE']['RESULT'][0]['TrainAnnouncement'];
  $php3=array();


  foreach ($php2 as $train) {
    $train2=array();
    if (!isset($train['EstimatedTimeAtLocation'])) {
      $train2['EstimatedTimeAtLocation']="";
      $train2['ActualTime']=$train['AdvertisedTimeAtLocation']; 
      $train2['TimeStamp']=strtotime($train['AdvertisedTimeAtLocation']);     
    } else {
      $train2['EstimatedTimeAtLocation']=$train['EstimatedTimeAtLocation'];
      $train2['ActualTime']=$train['EstimatedTimeAtLocation'];
      $train2['TimeStamp']=strtotime($train['EstimatedTimeAtLocation']);    
    }
    if (!isset($train['AdvertisedTimeAtLocation'])) {
      $train2['AdvertisedTimeAtLocation']="";
    } else {
      $train2['AdvertisedTimeAtLocation']=$train['AdvertisedTimeAtLocation'];
    }
    if (!isset($train['Canceled'])) {
      $train2['Canceled']="";
    } else {
      $train2['Canceled']=$train['Canceled'];
    }
    if (!isset($train['TrackAtLocation'])) {
      $train2['TrackAtLocation']="";
    } else {
      $train2['TrackAtLocation']=$train['TrackAtLocation'];
    }
    if (!isset($train['InformationOwner'])) {
      $train2['InformationOwner']="";
    } else {
      $train2['InformationOwner']=$train['InformationOwner'];
    }

    $php3[]=$train2;
  }
  
   //Sort array by 'TimeStamp' in ascending order
    usort($php3, function ($a, $b) {
    return $a['TimeStamp'] - $b['TimeStamp'];
   });    

  return $php3;
}

function next_train($from,$start,$end) {
  //start och end i minuter
  global $key;
  global $trafikinfo_allstations;
  global $trafikinfo_allstations_extended;

  $startstring=convert_time($start);
  $endstring=convert_time($end);

  $xml_data="<REQUEST>" .
  "<LOGIN authenticationkey='ec0d7a06fea94d4882f8b67c15b3f31a' />" .
  "<QUERY objecttype='TrainAnnouncement' " .
  "orderby='AdvertisedTimeAtLocation' >" .
  "<FILTER>" .
    "<AND>" .
      "<OR>" .
        "<AND>" .
          "<GT name='AdvertisedTimeAtLocation' " . "value='\$dateadd($startstring)' />" .
          "<LT name='AdvertisedTimeAtLocation' " . "value='\$dateadd($endstring)' />" .
        "</AND>" .
        "<AND>" .
          "<GT name='EstimatedTimeAtLocation' " . "value='\$dateadd($startstring)' />" .
          "<LT name='EstimatedTimeAtLocation' " . "value='\$dateadd($endstring)' />" .
        "</AND>" .
      "</OR>" .
      "<EQ name='LocationSignature' value='" . "$from" . "' />" .
      "<EQ name='ActivityType' value='Avgang' />" .
      "<EQ name='Advertised' value='true' />" .
    "</AND>" .
  "</FILTER>" .
  // Just include wanted fields to reduce response size.
  "<INCLUDE>EstimatedTimeAtLocation</INCLUDE>" . //beräknad tid om försenat
  "<INCLUDE>AdvertisedTimeAtLocation</INCLUDE>" . //tidtabellstid
  "<INCLUDE>Canceled</INCLUDE>" .
  "<INCLUDE>TrackAtLocation</INCLUDE>" .
  "<INCLUDE>InformationOwner</INCLUDE>" .
  "<INCLUDE>ToLocation</INCLUDE>" .
  "<INCLUDE>ViaToLocation</INCLUDE>" .

  "</QUERY>" .
  "</REQUEST>";
  $json=request($xml_data);
  $php1=json_decode($json,true);
  $php2=$php1['RESPONSE']['RESULT'][0]['TrainAnnouncement'];
  $php3=array();


  foreach ($php2 as $train) {
    $train2=array();
    if (!isset($train['EstimatedTimeAtLocation'])) {
      $train2['EstimatedTimeAtLocation']="";
      $train2['ActualTime']=short_time($train['AdvertisedTimeAtLocation']);
      $train2['TimeStamp']=strtotime($train['AdvertisedTimeAtLocation']);     
    } else {
      $train2['EstimatedTimeAtLocation']=$train['EstimatedTimeAtLocation'];
      $train2['ActualTime']=short_time($train2['EstimatedTimeAtLocation']);
      $train2['TimeStamp']=strtotime($train['EstimatedTimeAtLocation']);     
    }
    if (!isset($train['AdvertisedTimeAtLocation'])) {
      $train2['AdvertisedTimeAtLocation']="";
    } else {
      $train2['AdvertisedTimeAtLocation']=$train['AdvertisedTimeAtLocation'];
    }
    if (!isset($train['Canceled'])) {
      $train2['Canceled']="";
    } else {
      $train2['Canceled']=$train['Canceled'];
    }
    if (!isset($train['TrackAtLocation'])) {
      $train2['TrackAtLocation']="";
    } else {
      $train2['TrackAtLocation']=$train['TrackAtLocation'];
    }
    if (!isset($train['InformationOwner'])) {
      $train2['InformationOwner']="";
    } else {
      $train2['InformationOwner']=$train['InformationOwner'];
    }

    if (!isset($train['ToLocation'])) {
      $train2['ToLocation']="";
    } else {
      $train2['ToLocation']=$train['ToLocation'][0]['LocationName'];
      $train2['ToLocationName']=station_name($train2['ToLocation']);
    }

    if (!isset($train['ViaToLocation'])) {
      $train2['ViaToLocation']="";
    } else {
      $viacode="";
      $vianame="";
      foreach ($train['ViaToLocation'] as $location) {
        $viacode .= $location['LocationName'] . ", ";
        $vianame .= station_name($location['LocationName']) . ", ";
      }
      $viacode = substr($viacode,0,-2);
      $vianame = substr($vianame,0,-2);
      $train2['ViaToLocation']=$viacode;
      $train2['ViaToLocationName']=$vianame;
    }

    $php3[]=$train2;
  }
    
//Sort array by 'TimeStamp' in ascending order
    usort($php3, function ($a, $b) {
    return $a['TimeStamp'] - $b['TimeStamp'];
});
    
//  function sortByOption($a, $b) {
//   return strcmp($a['ActualTime'], $b['ActualTime']);
//   usort($php3, 'sortByOption');
// }
     
  return $php3;
}



function short_time($datetime) {
  return substr($datetime,11,-3);
}
function short_date($datetime) {
  return substr($datetime,0,10);
}


function request($xml_data) {
  $URL = "http://api.trafikinfo.trafikverket.se/v1.3/data.json";
  $ch = curl_init($URL);
  //curl_setopt($ch, CURLOPT_MUTE, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
  curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml_data");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $json = curl_exec($ch);
  curl_close($ch);
  return $json;
}

function convert_time($minutes) {
    //$minutes betwween -14440 and 1440 minutes = 1 day
    if ($minutes<0) {
      $negative=true;
      $minutes=-$minutes;
    } else {
      $negative=false;
    }
    $hours = floor($minutes/60);
    $minutes -= $hours * 60;
    $seconds = 0;
    $temp=lz($hours).":".lz($minutes).":".lz($seconds);
    if ($negative) {
      return "-".$temp;
    } else {
      return $temp;
    }
}

// lz = leading zero
function lz($num)
{
    return substr(("000".$num),-2);
}

?>

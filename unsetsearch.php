<?php
//Unset variablerna som innehåller sökningen
unset($_POST['to_station']);
unset($_POST['from_station']);

//Töm cookies och unset cookie-variabeln
setcookie('fromstation', '', time() - 3600);
unset($_COOKIE['fromstation']);

setcookie('tostation', '', time() - 3600);
unset($_COOKIE['tostation']);

//Skicka tillbaka till index
header('location: ../');

?>
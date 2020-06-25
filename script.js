//Funktion för att växla mellan dom olika sökfälten
function show_hide() {
    if(document.getElementById('show_hide_from_to').checked){
        document.getElementById('search_from_to').style.display = 'block';
        document.getElementById('search_from').style.display = 'none'; 
        return true;
    }
    else if(document.getElementById('show_hide_from').checked){
        document.getElementById('search_from_to').style.display = 'none';
        document.getElementById('search_from').style.display = 'block';
        return true; 
    }
}
//funktion för att stänga cookies-rutan, som skapar en cookie för att inte visa rutan igen
function closewindow(){
    document.getElementById('cookie').style.display = 'none';
    var d = new Date();
    d.setTime(d.getTime() + (365*24*60*60*1000));
    var expires = "expires="+ d.toUTCString();
    document.cookie = "cookies=YesPlease;" + expires;
}

//Hämtar cookies och Kontrollerar om cookies-rutan blivit klickad
function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function checkCookie() {
    var cookie = getCookie("cookies");
    if (cookie != "") {
        document.getElementById('cookie').style.display = 'none';
    } else {
        document.getElementById('cookie').style.display = 'flex';
     
        }
    }

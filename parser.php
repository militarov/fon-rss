<?php 
/**
* Author: Goran Militarov <meeleetarov[@]gmail.com>
* Date: 24.05.2012
* Description: FON RSS parser
*/

require_once('simple_html_dom.php');

$html = file_get_html('http://www.fon.rs/vesti/index.html');

/** Izvlacenje podataka  
*    ------------------- 
*    .newsDate - datum
*    .newsHeadline - naslov
*    .newsContent - sadrzaj
*/

$dates = array();

foreach($html->find('.newsDate') as $element) { 
	$dates[] = strip_tags($element->innertext);       
 
}

$headlines = array();

foreach ($html->find('.newsHeadline') as $element) {
	$headlines[] = trim(strip_tags($element->innertext));
}


$contents = array();
foreach ($html->find('.newsContent') as $element) {

	if ($fixed_element = checkLinks($element)) {
		$contents[] = trim($fixed_element->innertext);
	}
	else {
		$contents[] = $element->innertext;
	}
}


/* Zaglavlje XML fajla */
$rssfeed = '<?xml version="1.0" encoding="utf-8"?>';
$rssfeed .= '<rss version="2.0">';
$rssfeed .= '<channel>';
$rssfeed .= '<title>Fakultet organizacionih nauka - RSS </title>';
$rssfeed .= '<link>http://www.fon.bg.ac.rs</link>';
$rssfeed .= '<description>Studentske vesti sa FON sajta | Developed by FONIS</description>';
$rssfeed .= '<language>sr-rs</language>';
$rssfeed .= '<copyright>Developed by FONIS.</copyright>';
/* kraj zaglavlja XML fajla */

/* ispisivanje elemenata */
foreach ($dates as $id => $date) {

$rssfeed .= '<item>';
$rssfeed .= '<title><![CDATA[' . strip_tags($headlines[$id]) . ']]></title>';
$rssfeed .= '<description><![CDATA[' . strip_tags(trim($contents[$id]), '<a>') . ']]></description>';
$rssfeed .= '<link>http://www.fon.rs/vesti/</link>';
$rssfeed .= '<pubDate>'  . validDate($date) .'</pubDate>';
$rssfeed .= '</item>';

}
/* kraj ispisivanja elemenata */

$rssfeed .= '</channel>';
$rssfeed .= '</rss>';


/* upisivanje u fajl */
$file = 'fon.xml';
file_put_contents($file, $rssfeed);


/* ----------------------------------------------------------------
*  | F U N K C I J E
*  |--------------------------------------------------------------- 
*/

/**
* Na sajtu FON-a ne postoje apsolutni linkovi
* ka dokumentima koji se postavljaju.
* Ova funkcija dodaje validan link.
*/
function checkLinks($element) {

$valid_start = "http";
$fon_uri = "http://www.fon.bg.ac.rs/vesti/";
$valid_mail = "mailto:";


//ukoliko postoje elementi sa linkovima
if ($url = @$element->find('a')) {

	//ukoliko postoje, prolazi se kroz niz
	foreach ($url as $id => $uri) {

		$lpos = strpos($uri, $valid_start);

		if ($lpos === false) {
			//proveravamo da li je to mailto: link

			$mpos = strpos($uri, $valid_mail);

			if ($mpos === false) {
				//ukoliko nije mailto link, znaci da je relativna putanja na FON sajtu,
				// i dodajemo http://www.fon.bg.ac.rs/vesti/ ispred
				$element->find('a', $id)->href = $fon_uri . $uri->href;
			}
		}
	}
	return $element;
}
//ne postoje linkovi
return false;
}


function monthHelper($month) {

switch ($month) {
	    case "januar":
	        return 1;
	        break;
	    case "februar":
	        return 2;
	        break;
	    case "mart":
	        return 3;
	        break;
	    case "april":
	        return 4;
	        break;
	    case "maj":
	        return 5;
	        break;
	    case "jun":
	        return 6;
	        break;
	    case "jul":
	        return 7;
	        break;
	    case "avgust":
	        return 8;
	        break;
	    case "septembar":
	        return 9;
	        break;
	    case "oktobar":
	        return 10;
	        break;
	    case "novembar":
	        return 11;
	        break;
	    case "decembar":
	        return 12;
	        break;
	}
}

/**
* RSS Standard podrazumeva korišćenje RFC-822 standarda
* -
* Prilikom dobijanja podataka sa sajta FON-a,
* datum nije u validnom obliku, stoga se mora konvertovati 
*/
function validDate($invalid_date) {

	//datum koji se prosledjuje je u formatu: 24. maj 2012. 
	$date = explode(" ", trim($invalid_date));

	$dan = substr($date[0], 0, -1); //brisemo tacku sa kraja stringa;
	$mesec = monthHelper($date[1]);
	$godina = substr($date[2], 0, -1); //brisemo tacku sa kraja stringa

	$valid_date = new DateTime($godina . "-" . $mesec . "-" . $dan);
	$valid_date = $valid_date->format(DateTime::RSS);

	return $valid_date;
}


/** End of file **/
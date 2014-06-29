<?php
# Use the Curl extension to query centrosolar PV server and get back a page of results
$url = "http://pv.meema.lan";
$timeout = 5;
$username = "pvserver";
$password = "eTcJ0708=";

$outputCurrentFile = "pvdata.csv";
$outputTotalsFile = "pvdatatotals.csv";

// XPath current power output (state: on)
$xpathCurrentOutput = "/html/body/form/font/table[2]/tr[4]/td[3]";

// XPath daily power output
$xpathDailyOutput = "/html/body/form/font/table[2]/tr[6]/td[6]";

// XPath total power output
$xpathTotalOutput = "/html/body/form/font/table[2]/tr[4]/td[6]";

// XPath PV state
$xPathPvState = "/html/body/form/font/table[2]/tr[8]/td[3]";

$ch = curl_init() or die ("ERROR: Could not initiate curl session!");

curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$html = curl_exec($ch) or die ("ERROR: Could not execute curl session!");

curl_close($ch);

// Create a DOM parser object
$dom = new DOMDocument();

/*
Parse the HTML from PV server.
The @ before the method call suppresses any warnings that
loadHTML might throw because of invalid HTML in the page.
*/
@$dom->loadHTML($html);
//@$dom->loadHTMLFile("pvserver.html");

$xpath = new DOMXpath($dom);

$fh_current = fopen($outputCurrentFile, "a");
if ($fh_current == FALSE)
{
    echo "ERROR: Failed to open file!<br \>";
}

$fh_totals = fopen($outputTotalsFile, "a");
if ($fh_totals == FALSE)
{
    echo "ERROR: Failed to open file!<br \>";
}


// get pv state
$elements = $xpath->query($xPathPvState);

foreach ($elements as $element) {
    $pvState = trim($element->nodeValue);
}
//echo "DEBUG: pvState=" . $pvState . "<br \>";

if ($pvState != "Aus")
{
    //echo "Marker: IN<br \>";
    // get current power output
    $elementsCurrent = $xpath->query($xpathCurrentOutput);
    //echo "DEBUG: elementsCurrent->length=" . $elementsCurrent->length . "<br \>";

    foreach ($elementsCurrent as $elementCurrent) {
        fwrite($fh_current, date("Y-m-d H:i:s") . ";" . trim($elementCurrent->nodeValue). "\r\n");
    }
    
    // get daily power output
    $elementsDaily = $xpath->query($xpathDailyOutput);
    //echo "DEBUG: elementsDaily->length=" . $elementsDaily->length . "<br \>";

    foreach ($elementsDaily as $elementDaily) {
         $dailyOutput = trim($elementDaily->nodeValue);
    }
    
    // get total power output
    $elementsTotal = $xpath->query($xpathTotalOutput);
    //echo "DEBUG: elementsTotal->length=" . $elementsTotal->length . "<br \>";

    foreach ($elementsTotal as $elementTotal) {
         $totalOutput = trim($elementTotal->nodeValue);
    }
    
    //echo "Total Output: " . $totalOutput . " kWh<br \>";
    //echo "Daily Output: " . $dailyOutput . " kWh<br \>";
    
    fwrite($fh_totals, date("Y-m-d H:i:s") . ";" . $totalOutput . ";" . $dailyOutput . "\r\n");

}
else
{
    //echo "IN_ELSE";
    // get current power output
    fwrite($fh_current, date("Y-m-d H:i:s") . ";0\r\n");

}

fclose($fh_current);
fclose($fh_totals);

/*
// loop through all <td> tags and print XPath, Value and Node Type

$tds = $dom->getElementsByTagName('td');

foreach ($tds as $td) {
    echo "Node Value:" . $td->nodeValue . ", Node Type:" . $td->nodeType . ", Node XPath:" . $td->getNodePath() . "<br \>";
}*/
?>
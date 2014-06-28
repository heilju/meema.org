<?php
# Use the Curl extension to query centrosolar PV server and get back a page of results
$url = "http://pv.meema.lan";
$timeout = 5;
$username = "pvserver";
$password = "eTcJ0708=";

$outputFile = "pvdata.csv";

// XPath current power output
$xpathCurrentOutput = "/html/body/form/font/table[2]/tbody/tr[4]/td[3]";

// XPatch total power output
$xpathTotalOutput = "/html/body/form/font/table[2]/tbody/tr[4]/td[6]";


/*
$ch = curl_init();

curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
$html = curl_exec($ch);
curl_close($ch);
*/
# Create a DOM parser object
$dom = new DOMDocument();

# Parse the HTML from PV server.
# The @ before the method call suppresses any warnings that
# loadHTML might throw because of invalid HTML in the page.
//@$dom->loadHTML($html);
@$dom->loadHTMLFile("pvserver.html");

$xpath = new DOMXpath($dom);

$handle = fopen($outputFile, "a");

// get current power output
$elements = $xpath->query($xpathCurrentOutput);

foreach ($elements as $element) {
    fwrite($handle, date("Y-m-d H:i:s") . ";" . trim($element->nodeValue). "\r\n");
}

fclose($handle);

// loop through all <td> tags and print XPath, Value and Node Type
/*
$tds = $dom->getElementsByTagName('td');

foreach ($tds as $td) {
    echo "Node Value:" . $td->nodeValue . ", Node Type:" . $td->nodeType . ", Node XPath:" . $td->getNodePath() . "<br \>";
}
*/

?>
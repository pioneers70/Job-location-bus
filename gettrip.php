<?php
ini_set("soap.wsdl_cache_enabled","0");
$client = new SoapClient("http://192.168.2.82:14051/wsdl/ISoapAppSrv");
$IdTrip = 1626232;
$restrip = $client->krsGetTripCoord(25346, 'Sf$qW5v_', $IdTrip,0);
function showinf($data): void
{
    echo '<pre>' . print_r($data, 1) . '</pre>';
}
showinf($restrip);
$geotrip = array ();
foreach ($restrip as $pointtrip) {
    $geotrip[] = array(
        'longtrip' => $pointtrip->LONG,
        'latitrip' => $pointtrip->LAT
    );
}
header('Content-Type: application/json; charset=utf-8');
$jsondatatrip = json_encode($geotrip);
//echo $jsondatatrip;
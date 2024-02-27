<?php
ini_set("soap.wsdl_cache_enabled","0");
$client = new SoapClient("http://80.65.28.9:14051/wsdl/ISoapAppSrv");
$IdTrip = 1626232;
$restrip = $client->krsGetTripCoord(25346, 'Sf$qW5v_', $IdTrip,0);
$geotrip = array ();
foreach ($restrip as $pointtrip) {
    $geotrip[] = array(
        'longtrip' => $pointtrip->LONG,
        'latitrip' => $pointtrip->LAT
    );
}
header('Content-Type: application/json; charset=utf-8');
$jsondatatrip = json_encode($geotrip);
echo $jsondatatrip;
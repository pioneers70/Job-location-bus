<?php
ini_set("soap.wsdl_cache_enabled","0");
$client = new SoapClient("http://80.65.28.9:14051/wsdl/ISoapAppSrv");
/*$client = new SoapClient("http://192.168.2.82:14051/wsdl/ISoapAppSrv");*/
$IdTrip = 1626232;
/** Получение маршрута  */
//$res = $client->krsGetTripRes(25346, 'Sf$qW5v_', $IdTrip);

/** Получение пройденных координат */
$restrip = $client->krsGetTripCoord(25346, 'Sf$qW5v_', $IdTrip,0);


//$res = $client->krsGetTrips(25346, 1, 15,'27.02.2024'); // для получения Id
//var_dump($res);
function showinf($data): void
{
    echo '<pre>' . print_r($data, 1) . '</pre>';
}
//showinf($res);
$geotrip = array ();
foreach ($restrip as $pointtrip)
{
    $geotrip[] = array(
        'longtrip' => $pointtrip->LONG,
        'latitrip' => $pointtrip->LAT
    );
}
showinf($geotrip);
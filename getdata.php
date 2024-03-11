
<?php
ini_set("soap.wsdl_cache_enabled","0");
$client = new SoapClient("http://80.65.28.9:14051/wsdl/ISoapAppSrv"); //80.65.28.9
$IdTrip = 1626232;
//$IdTrip = 1663785;
$res = $client->krsGetTripRes(25346, 'Sf$qW5v_', $IdTrip);
$restrip = $client->krsGetTripCoord(25346, 'Sf$qW5v_', $IdTrip,0);

//$jsonData =json_encode($res);
function showinf($data): void
{
    echo '<pre>' . print_r($data, 1) . '</pre>';
}
$buspos = array ();
    $buspos[] = array(
        'buslongitude' => $res->LONGITUDE,
        'buslatitude' => $res->LATITUDE
    );
$geodata = array ();
foreach ($res->TripPointArr as $point) {
    $geodata[] = array(
        'idstation' => $point->IdStation,
        'stationName' =>$point->StationNm,
        'longitude' => $point->StLONGITUDE,
        'latitude' => $point->StLATITUDE,
        'timearrpl' => $point->DtTmArrivalPl,
        'timearrft' => $point->DtTmArrivalFt,
        'timesendpl' => $point->DtTmSendPl,
        'timesendft' => $point->DtTmSendFt
        //        'timesendft' => (new DateTime($point->DtTmSendFt))->format('Y-m-d H:i:s')
    );
}
$geodatatrip = array();
foreach ($restrip as $pointtrip)
{
    $geodatatrip[] = array(
        'longtrip' => $pointtrip->LONG,
        'latitrip' => $pointtrip->LAT
    );
};
$geodataall = array(
    'datapoint'=>$geodata,
    'pointtrip' => $geodatatrip,
    'busposition' => $buspos
);
//showinf($geodataall);
//showinf($geodata);
header('Content-Type: application/json; charset=utf-8');
$jsondata = json_encode($geodataall);
echo $jsondata;
//echo json_encode($stationName);
//$output=$_POST['$ostanovki'];
// if ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
//             throw new Exception('No ajax');





<?php
ini_set("soap.wsdl_cache_enabled","0");
$client = new SoapClient("http://80.65.28.9:14051/wsdl/ISoapAppSrv");
$IdTrip = 1626232;
$res = $client->krsGetTripRes(25346, 'Sf$qW5v_', $IdTrip);
//var_dump($res);
function showinf($data): void
{
    echo '<pre>' . print_r($data, 1) . '</pre>';
}
showinf($res);
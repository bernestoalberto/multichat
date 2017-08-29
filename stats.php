<?php
/**
 * Created by PhpStorm.
 * User: NEO
 * Date: 07/03/2016
 * Time: 10:27
 */

include_once 'multichat/configuration.php';
include_once 'multichat/functions.php';


$configuracion = new Configuracion();
$god_email =$configuracion->bustiarecarga;
date_default_timezone_set('America/Havana');

$hoy = new DateTime();
$fechahoy = $hoy->format("Y-m-d");

$fechareport = $hoy->format("Y-m-d H:i:s");

$hoy->add(new DateInterval("P1D"));
$fechamanana = $hoy->format("Y-m-d");

$hist = getTodaySMS($fechahoy, $fechamanana);

$saldo = getSaldo($god_email);
$activeusers = array();
$totalnewUsers = getNewUsers($fechahoy, $fechamanana);
$totalusedUsers = getusedUsers();
$totaltransf = getTotalTransf($fechahoy, $fechamanana);
$remanent = getSaldoUsers();

if (count($hist) > 0) {
    foreach ($hist as $smsduty) {
        $ammount = checkCell($smsduty['to']);
        if ($ammount == 25) {
            $totalnac++;
        } else {
            $totalinternac++;
        }
        $activeusers[] = $smsduty['from_id'];
    }
}

$subject = $fechareport;

$body =
    "Su saldo en smsXmail es $ ".$saldo." CUC equivalente a " . $saldo/0.02 ." sms <br>
  <b> Resumen del día </b>  <br>
Total de SMS enviados hoy: " . count($hist) . "<br>
Total gastado por usuarios hoy: $ " . $totalnac *0.02 . "<br>
Saldo remanente en usuarios: $ " . $remanent . "<br>
Total de usuarios activos: " . count(array_unique($activeusers)) . "<br>
Total de usuarios nuevos: " . $totalnewUsers . "<br>
Total de transferencias recibidas Hoy: $ " . $totaltransf['full'] . "<br>";

enviarcorreo($god_email, $body, $subject);

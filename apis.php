<?php
/**
 * Created by PhpStorm.
 * User: Ernesto Bonet
 * Date: 27/06/2016
 * Time: 12:19
 */

include_once 'multichat/functions.php';
include_once 'multichat/configuration.php';
include_once 'multichat/src/Fetch/Server.php';
include_once 'multichat/src/Fetch/Attachment.php';
$configuracion = new Configuracion();

set_time_limit(60);
error_reporting(E_ALL);
date_default_timezone_set('America/Havana');
$date = new DateTime("NOW");
$fecha =$date->format("Y-m-d H:i:s");



$bustia = $configuracion->bustia;
$bustia_recarga = $configuracion->bustiarecarga;
$dominioemail = $configuracion->dominioemail;
$test = $configuracion->test;

$dbhost  = $_SERVER['HTTP_HOST'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$remote_address = $_SERVER['REMOTE_ADDR'];

$email_server = new \Fetch\Server($configuracion->imap_server, $configuracion->mailport);

$email_server->setAuthentication($configuracion->imap_user, $configuracion->imap_pass);

$messages = $email_server->getMessages(10);

if (count($messages) > 0) {
    /** @var $message \Fetch\Message */
    foreach ($messages as $message) {
        /*
         * Cabeceras del correo, asunto, from, cuerpo.
         */
        $from = $message->getFrom()[0]["address"];
        $subject = $message->getSubject();
        $structure = $message->getStructure();
        $cuerpo = $message->getMessageBody();

        $attachment = new \Fetch\Attachment($message,$structure);
       $my_data_file = $attachment->getMyData();

        traza($fecha, $from, $subject, $my_data_file, 'cliente');
        logs($subject, "Entrada", $from, $dbhost, $user_agent, $remote_address);
        $asunto = explode("*", $subject);
        if(isset($asunto[1])){
            if(!is_numeric($asunto[1]) && $asunto[2]==null)
            $asunto[1] = strtoupper($asunto[1]);
        }
        $asunto[0] = strtoupper(trim($asunto[0]));


        //Eliminar este mensaje
        $message->delete();
          $cliente = getCliente($from);
            switch ($asunto[0]) {
                case is_numeric($asunto[0]):
                    if (strlen($my_data_file) <= 160 && estaRegistrado($from) == true) {
                        $comprobacion = descontar($from, $configuracion->getValuesmsNacional());
                        $phone = '53'.$asunto[0];
                        if ($comprobacion == true && checkCellVillas($phone)) {

                            $result = enviarcorreo($bustia, $my_data_file, $phone);
                          
                            historial($cliente['id_cliente'], $asunto[0], $my_data_file, $fecha, $cliente['saldo']);
                            traza($fecha, $from, $asunto[0], $my_data_file, 'sistema');
                            logs('SMS', $result, $from, $dbhost, $user_agent, $remote_address);
                            if($asunto[1]=="NOTI"){
                                confirmacion_entrega($from ,$fecha,$dbhost,$user_agent,$remote_address);
                            }

                        }


                    }

                    break;
                case "SALDO":
                    if (estaRegistrado($from) == true) {
                        $comprobacion = descontar($from, $configuracion->getValuesmsNacional());
                        if ($comprobacion == true) {
                            $saldo = getSaldo($from);
                            $numsms = ($saldo / $configuracion->getValuesmsNacional());
                            $body = " <b>Saldo:</b> $".$saldo." equivalente a ".$numsms." SMS <br>
                                       <b>Actualizado</b>: ".$fecha."                         <br>
                                       Transfiera 2 CUC al 53204125 para obtener 100 SMS <br>
                                       Gracias por utilizar Multich@t
     ";
                            $numero_cliente = getPhonebyEmail($from);
                            $subject = "$numero_cliente";
                            $result = enviarcorreo($bustia, $body, $subject);
                            historial($cliente['id_cliente'], $numero_cliente, $body, $fecha, $cliente['saldo']);
                            traza($fecha, $from, $subject, $body, 'sistema');
                            logs('SALDO', $result, $from, $dbhost, $user_agent, $remote_address);
                        }
                    }
                    break;
                case "AYUDA":
                    if (estaRegistrado($from) == true) {
                        $comprobacion = descontar($from, $configuracion->getValuesmsNacional());
                        if ($comprobacion == true) {
                            $numero_cliente = getPhonebyEmail($from);

                            $body = "   MMS a multichat@smsxmail.com  <br>
                                        <b> Asunto:</b>".$numero_cliente." <br>
                                         <b>Cuerpo:</b>Mensaje <br>
                                       Otros comandos Asunto: SALDO, LINK y ALGONUEVO        <br>
                                       Transfiera 2 CUC al 53204125 para obtener 100 SMS      ";

                            $subject = "  substr($numero_cliente,2,8)";
                            $result = enviarcorreo($bustia, $body, $subject);
                            historial($cliente['id_cliente'], $numero_cliente, $body, $fecha, $cliente['saldo']);
                            traza($fecha, $from, $subject, $body, 'sistema');
                            logs('AYUDA', $result, $from, $dbhost, $user_agent, $remote_address);
                        }
                    }

                    break;
                case "LINK":
                    if (estaRegistrado($from) == true) {
                        $comprobacion = descontar($from, $configuracion->getValuesmsNacional());
                        if ($comprobacion == true) {
                            $numero_cliente = getPhonebyEmail($from);
                            $links = getLinks();
                           for($i=0;$i<count($links);$i++){

                               $body .=  "$links[$i]  \n ";



                           }

                            $subject = "$numero_cliente";
                            $result = enviarcorreo($bustia, $body, $subject);
                            historial($cliente['id_cliente'], $numero_cliente, $body, $fecha, $cliente['saldo']);
                            traza($fecha, $from, $subject, $body, 'sistema');
                            logs('LINK', $result, $from, $dbhost, $user_agent, $remote_address);
                        }
                    }

                    break;
                case "ALGONUEVO":
                    if (estaRegistrado($from) == true) {
                        $content = getContent($from);
                        if ($content != false) {
                            $comprobacion = descontar($from, $configuracion->getValuesmsNacional());
                            if ($comprobacion == true ) {
                                $numero_cliente = getPhonebyEmail($from);
                                $body = "$content";
                                $subject = "$numero_cliente";
                                $result = enviarcorreo($bustia, $body, $subject);
                                historial($cliente['id_cliente'], $numero_cliente, $body, $fecha, $cliente['saldo']);
                                traza($fecha, $from, $subject, $body, 'sistema');
                                logs('ALGONUEVO', $result, $from, $dbhost, $user_agent, $remote_address);
                            }
                        }
                    }
                    break;
                case "RE":
                    $numero_completo = '53'.$asunto[1];
                if ($from == $bustia_recarga && checkCellOnly($numero_completo) && is_numeric($asunto[2])) {
                    $esta = isCellinClienteTable($numero_completo);
                    if ($esta == true)
                    {
                        $comando = 'RE';
                        recargar($from, $numero_completo, $asunto[2], $fecha, 'Droid', $dbhost, $user_agent, $remote_address,$comando);

                    }
                    else
                    {
                        $email= $numero_completo.$dominioemail;
                      $resultado = RegistrarUsuario($from,$numero_completo, $email,$asunto[2],$fecha);
                        if($resultado==true){
                            $subject = "El celular ".$numero_completo." se ha registrado en el sistema";
                            $body = "El celular " . $numero_completo . " fue registrado con $" . $asunto[2]." de saldo";
                            $result = enviarcorreo($bustia_recarga, $body, $subject);

                            traza($fecha, $from, $subject, $body, 'sistema');
                            logs('RE', $result, $from, $dbhost, $user_agent, $remote_address);
                        }
                        else{
                            $subject = "No se pudo registrar al cliente";
                            $body = "                                                               ";
                            $result = enviarcorreo($from, $body, $subject);

                            traza($fecha, $from, $subject, $body, 'sistema');
                            logs('RE', $result, $from, $dbhost, $user_agent, $remote_address);
                        }

                    }

                }
                else {
                    $subject = "Comando no permitido o numero de telefono o saldo incorrecto";
                    $body = "                                                               ";
                    $result = enviarcorreo($from, $body, $subject);

                    traza($fecha, $from, $subject, $body, 'sistema');
                    logs('RE', $result, $from, $dbhost, $user_agent, $remote_address);


                }

                break;
                case "RC":

                    if ($from == $bustia_recarga && isEmailAddress($asunto[1]) && is_numeric($asunto[2])) {

                     if(isEmailinClienteTable($asunto[1])) {
                         $numero_completo = getPhonebyEmail($asunto[1]);
                         $comando = 'RC';
                         recargar($from, $numero_completo, $asunto[2], $fecha, 'Droid', $dbhost, $user_agent, $remote_address, $comando);
                     }

                        else
                        {

                            $numero_completo ='535000000';
                            $resultado = RegistrarUsuario($from,$numero_completo, $asunto[1],$asunto[2],$fecha);
                            if($resultado==true){
                                $subject = "El correo ".$asunto[1]." se ha registrado en el sistema";
                                $body = "El cliente asociado al correo  " . $asunto[1] . " fue registrado con $" . $asunto[2]." de saldo";
                                $result = enviarcorreo($bustia_recarga, $body, $subject);

                                traza($fecha, $from, $subject, $body, 'sistema');
                                logs('RC', $result, $from, $dbhost, $user_agent, $remote_address);
                            }
                            else{
                                $subject = "No se pudo registrar al cliente";
                                $body = "                                                               ";
                                $result = enviarcorreo($from, $body, $subject);

                                traza($fecha, $from, $subject, $body, 'sistema');
                                logs('RC', $result, $from, $dbhost, $user_agent, $remote_address);
                            }

                        }

                    }
                    else {
                        $subject = "Comando no permitido o numero de telefono o saldo incorrecto";
                        $body = "                                                               ";
                        $result = enviarcorreo($from, $body, $subject);

                        traza($fecha, $from, $subject, $body, 'sistema');
                        logs('RC', $result, $from, $dbhost, $user_agent, $remote_address);


                    }


                    break;
                case "REPORTE":
                    if ($from == $bustia_recarga || $from == $test ) {
                        Reporte($from, $fecha, $dbhost, $user_agent, $remote_address);
                    }
                    else {
                        $body = "          ";
                        $subject = 'usted no tiene permiso para ejecutar este comando';
                        $result = enviarcorreo($from, $body, $subject);
                        traza($fecha, $from, $subject, $my_data_file, 'sistema');
                        logs($asunto[0], $result, $from, $dbhost, $user_agent, $remote_address);

                    }

                    break;
                case "ANUEVO":

                    if ($from == $bustia_recarga && strlen($cuerpo) <= 160){
                       $resultado = actualizarAnuevo($cuerpo);
                        $body = "          ";
                        if($resultado==true){
                            $subject = 'Se ha actualizado el contenido del mensaje';
                        }
                        else{
                            $subject = 'No se ha actualizado el contenido del mensaje';
                        }

                        $result = enviarcorreo($from, $body, $subject);
                        traza($fecha, $from, $subject, $body, 'sistema');
                        logs($asunto[0], $result, $from, $dbhost, $user_agent, $remote_address);
                    }
                    else{
                        $body = "          ";
                        $subject = 'usted no tiene permiso para ejecutar este comando o el cuerpo tiene mas caracteres de los permitido';
                        $result = enviarcorreo($from, $body, $subject);
                        traza($fecha, $from, $subject, $my_data_file, 'sistema');
                        logs($asunto[0], $result, $from, $dbhost, $user_agent, $remote_address);
                    }

                    break;
                case "ALINK":

                    if ($from == $bustia_recarga && strlen($cuerpo) <= 160){
                        $resultado = actualizarALink($cuerpo);
                        $body = "          ";
                        if($resultado==true){
                            $subject = 'Se ha actualizado los links';
                        }
                        else{
                            $subject = 'No se ha actualizado los links';
                        }

                        $result = enviarcorreo($from, $body, $subject);
                        traza($fecha, $from, $subject, $my_data_file, 'sistema');
                        logs($asunto[0], $result, $from, $dbhost, $user_agent, $remote_address);
                    }
                    else{
                        $body = "          ";
                        $subject = 'usted no tiene permiso para ejecutar este comando o el cuerpo tiene mas caracteres de los permitido';
                        $result = enviarcorreo($from, $body, $subject);
                        traza($fecha, $from, $subject, $my_data_file, 'sistema');
                        logs($asunto[0], $result, $from, $dbhost, $user_agent, $remote_address);
                    }
                    break;
                default:


                    break;

            }

    }
}



//Clean mailbox!
$email_server->expunge();

exit;



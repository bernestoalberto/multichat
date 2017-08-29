<?php
include_once 'multichat/configuration.php';
$configuracion = new Configuracion();


function puedeRecargar($from){
    $estacliente = getCliente($from);
   $available =  estaRecargadoryestaHabilitado($estacliente['id_cliente']);
    if($estacliente != false && $available == true ){
      return true;
    }
    return false;


}
function preparar_envio_sms($from,$phone){

    // $phone = getCell($from);

    if (substr($phone, 0, 2) == "53" && strlen($phone) == 10) {
        $value = 1;
        $rslt =    descontar($from,$value);
    }
    elseif
    (substr($phone, 0, 2) != "53" && strlen($phone) <= 13 && strlen($phone) >= 10) {
        $value = 4;
        $rslt = descontar($from,$value);


    }
    else{
        $rslt = "Numero incorrecto";
    }
    return $rslt;

}
function descontar($from, $value)
{
       global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $saldo = getSaldo($from);
    if ($saldo !=false && $saldo >= $value) {
        $saldo -= $value;
        $query = "UPDATE cliente  SET saldo = '".$saldo ."' WHERE correo = '" . $from . "'";

        $rs =$mysqli->query($query);
        if($rs == true){
            $respuesta =true;
        }
    }
    else{
        $respuesta =false;
    }
    return $respuesta;
}
function getEmail($cell)
{
       global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $sql = "SELECT correo FROM cliente WHERE celular = '" . $cell . "'";

    $result = $mysqli->query($sql);
    if ($result->num_rows == 1) {
        $r = $result->fetch_assoc();
        return $r['correo'];
    }
    return FALSE;
}
function traza_recarga($cell, $saldo, $fecha, $id_cliente_recargador, $tipo_recarga, $descripcion){
       global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $sql = "INSERT INTO recarga (numero_recargar,monto,fecha_recarga,id_cliente_recargador,tipo_recarga,descripcion_recarga)
            VALUES ('".$cell."','".$saldo."','".$fecha."','".$id_cliente_recargador."','".$tipo_recarga."','".$descripcion."')";

    $result = $mysqli->query($sql);
    if ($result->num_rows == 1) {

        return true;
    }
    return FALSE;
}
function recargar($correo,$cell,$saldo,$fecha,$tipo_recarga,$host,$user_agent,$remote_addr,$comando){

        $pudo = descontar($correo,$saldo);
        if($pudo != false ){
        $saldofinal = asignarSaldoaCliente($cell,$saldo);
        if($saldofinal!= false) {
            traza_recarga($cell, $saldo, $fecha, $cell, $tipo_recarga, '');
            $subject = "La cuenta del " . $cell . " fue recargada ";
            $body = "El cliente asociado a  " . $cell . " fue recargado con $" . $saldo . ". Saldo final: $" . $saldofinal;

            $resultado = enviarcorreo($correo, $body, $subject);

            traza($fecha, $correo, $subject, $body, 'sistema');
            logs($comando, $resultado, $correo, $host,$user_agent,$remote_addr);

        }

        else{

                $subject = "Error en Recarga";
                $body = "Ocurrio un error al intentar recargar a " . $cell . "";
                $resultado =   enviarcorreo($correo, $body, $subject);


            }

    }
    else{

        $subject = "Error en Recarga, Saldo a transferir mayor que saldo actual";
        $body = "Ocurrio un error al intentar recargar a " . $cell . "";
        $resultado =   enviarcorreo($correo, $body, $subject);


    }
    traza($fecha,$correo, $subject,$body,'sistema');
    logs("RE",$resultado,$correo,$host,$user_agent,$remote_addr);
}



function Clientes(){
       global $configuracion;
    $mysqli=  $configuracion->mysqli;
       $query = " SELECT * FROM clientes";
    $r = $mysqli->query($query);
    for ($clientes = array (); $row = $r->fetch_assoc(); $clientes[] = $row);
    return $clientes;
}
function Reporte($correo,$fecha,$host,$user_agent,$remote_addr){

    $saldoDroid =  getSaldo($correo);
    $clientes = Clientes();
    $subject = "Reporte";
    $body='';
    $body.= "<b>Reporte</b> <br><br>";
    $body.= " <b>Su saldo actual en Multich@t es </b>:".$saldoDroid." CUC <br><br>";
    $body1 = '';
       $body3 ='';
    if($clientes==null) {

        $body3 = '<b> Clientes</b><br>';
        $body4 = ' <b> Actualmente no existen clientes </b> <br>';
    }
    else{
        $body3 .= "<b>Clientes</b><br>";
        $body3 .= "<table cellpadding='2' cellspacing='2' width='100%' border='1'>
   <tr>
   <td><b>#</b></td>
   <td><b>Correo</b></td>
   <td><b>Celular</b></td>
   <td><b>Saldo (MN)</b></td></tr>";
        $body4 = '';
    for($j=0;$j<count($clientes);$j++){

        $emailc = $clientes[$j]['correo'];
        $celularc = $clientes[$j]['celular'];
        $saldoc = $clientes[$j]['saldo'];

        $body4 .= "<tr><td>".$j."</td>";
        $body4 .= "<td>" . $emailc. "</td>";
        $body4 .= "<td>" . $celularc. "</td>";
        $body4 .= "<td>" . $saldoc . "</td>";
        $body4 .= "</tr>";
    }
        $body4 .= "</table> <br> <br>";
    }
    $body = $body.$body1.$body3.$body4;
    $result = enviarcorreo($correo, $body, $subject);

    traza($fecha,$correo, $subject,$body,'sistema');
    logs('REPORTE',$result,$correo,$host,$user_agent,$remote_addr);
}
function asignarSaldoaCliente($cell, $saldo){

       global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $saldactual = getSaldo($cell);

        $saldofinal = $saldactual + $saldo;
        $query = "UPDATE cliente
    SET saldo = '" . $saldofinal . "' WHERE celular ='" . $cell . "'";

        $rs = $mysqli->query($query);
        if ($rs == true) {
            $response = $saldofinal;
        }

    else
    {
        $response = false;
    }
    return $response;

}
function REG($cell, $from, $fecha, $bustia, $host, $user_agent, $remote_addr){
    $codigo_verficacion = rand(100000,999999);
    if (checkCell($cell,$from)== true) {
        $result = pregistrarUsuario($cell,$from,$codigo_verficacion);
        if ($result ==true) {

            $subject = "$cell";
            $body = "Su codigo de activacion en smscubano es " . $codigo_verficacion . "";
            historial(0,$cell,$body,$fecha,0);


            $result = enviarcorreo($bustia, $body, $subject);



        }
        else {
            //Ya estaba en BD
            //Se pudo registrar en BD
            $subject = "Registro incorrecto";
            $body = "No se pudo registrar el usuario.<br>
                            El servicio permite asociar cada número de celular solamente a un correo.<br>"
            ;
            $result = enviarcorreo($from, $body, $subject);

        }
    }
    else {
        //El usuario no registro correctamente el numero
        $subject = "Número incorrecto";
        $body = "El número de celular que utilizó para registrarse en SMS CUBANO no es correcto o ya esta registrado";

        $result = enviarcorreo($from, $body, $subject);

    }
    traza($fecha,$from, $subject,$body,'sistema');
    logs('REG',$result,$from,$host,$user_agent,$remote_addr);
}
function historial($id_cliente,$numero_destino,$mensaje,$fecha,$saldo){
       global $configuracion;     $mysqli=  $configuracion->mysqli;
    $query = "INSERT INTO historial(id_cliente,numero_destino,mensaje,fecha_envio,saldo_historial)
             values('$id_cliente','$numero_destino','$mensaje','$fecha','$saldo')";

    $mysqli->query($query);

}
function getCliente($correo){
       global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $addphone = "SELECT * FROM cliente WHERE correo = '" . $correo . "'";
    $r = $mysqli->query($addphone);
    if ($r->num_rows == 1) {
        $cliente =   $r->fetch_assoc();
        return $cliente;
    }
    return FALSE;
}
/*
 * Verificar que el  numero es valido
 */
function checkCell($phone,$from)
{

    if (is_numeric($phone)) {
        if (substr($phone, 0, 3) == "535" && strlen($phone) == 10) {

            $resultado = enviarCodigoRegistro($phone,$from);

            if($resultado == true ){
                return true;
            }
            else
            {
                return false;
            }

        }

        return false;
    }
    return false;
}
function checkCellVillas($phone)
{

    if (is_numeric($phone)) {
        if (substr($phone, 0, 3) == "535" && strlen($phone) == 10) {


            return true;
        }
        return false;
    }
    return false;
}
function checkCellOnly($phone)
{

    if (is_numeric($phone)) {
        if (substr($phone, 0, 3) == "535" && strlen($phone) == 10) {

                return true;
            }

        return false;
    }
    return false;
}

/*
 * Buscar un cell en la BD para verificar que existe
 */
function estaRegistrado($correo){
   $cliente = getCliente($correo);
   $correo =  $cliente['correo'];
        $celular =$cliente['celular'];

    if($correo!="" && $celular!=0){
        return true;

    }
    return false;
}
function estaAsociado($phone,$from){
    global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $addphone = "SELECT id_cliente FROM cliente WHERE celular = '" . $phone . "' AND correo = '".$from."'";

    $r = $mysqli->query($addphone);
    if ($r->num_rows == 1) {
        return TRUE;
    }
    return FALSE;
}
function enviarCodigoRegistro($phone, $from){
  $resultado =  isCellinClienteTable($phone);
    if($resultado ==true){
        $asociado = estaAsociado($phone,$from);
        if($asociado ==true){
            $dio = true;
        }
        else{
            $dio = false;
        }

    }
    else{
        $dio = true;
    }
    return $dio;
}
function isCellinClienteTable($phone)
{
       global $configuracion;
    $mysqli=  $configuracion->mysqli;
       $addphone = "SELECT id_cliente FROM cliente WHERE celular = '" . $phone . "'";

    $r = $mysqli->query($addphone);
    if ($r->num_rows == 1) {
        return TRUE;
    }
    return FALSE;
}
/*$phone
 * Update the cell in DB
 */
function pregistrarUsuario($phone, $email,$codigo_verficacion)
{
       global $configuracion;
    $mysqli=  $configuracion->mysqli;
    //Buscar el numero en la BD
    if(isEmailinClienteTable($email)== false){
        $addphone = "Insert into cliente
(correo,celular,fecha_registro,saldo,codigo_verficacion,numero_temporal)
VALUES ('$email','','','','$codigo_verficacion','$phone')";
    }
    else {
        $addphone = "UPDATE  cliente
        SET
        codigo_verficacion='$codigo_verficacion',
        numero_temporal='$phone'
       WHERE  correo='$email'";
    }

    $rs = $mysqli->query($addphone);
    if ($rs == true) {
        $resultado = true;
    } else {
        $resultado = false;
    }

    return $resultado;

}
function RegistrarUsuario($from,$phone, $email,$saldo,$fecha)
{
    global $configuracion;
    $resultado = false;
    $pudo = descontar($from,$saldo);
    if($pudo != false ) {
        $mysqli = $configuracion->mysqli;
        $addphone = "Insert into cliente (correo,celular,fecha_registro,saldo,codigo_verficacion,numero_temporal)
VALUES ('$email','$phone.','$fecha','$saldo','1234','$phone')";

        $rs = $mysqli->query($addphone);
        if ($rs == true) {
            $resultado = true;
        } else {
            $resultado = false;
        }
    }
    return $resultado;

}

function isEmailinClienteTable($email)
{
       global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $addphone = "SELECT id_cliente FROM cliente WHERE correo = '$email'";

    $r = $mysqli->query($addphone);
    if ($r->num_rows == 1) {
        return TRUE;
    }
    return FALSE;
}
function estaRecargadoryestaHabilitado($id_cliente){
       global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $addphone = "SELECT id_recargador,estado FROM recargador WHERE id_cliente = '" . $id_cliente . "'";

    $r = $mysqli->query($addphone);
    if($r==true){
        $recargador =$r->fetch_assoc();

          if($recargador['id_recargador'] != null && $recargador['estado'] == 1 ){
              return true;
          }
        else{
            return false;
        }

    }
    return false;
}
function isIDinRecargadorTable($id_cliente){

       global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $addphone = "SELECT id_recargador FROM recargador WHERE id_cliente = '" . $id_cliente . "'";

    $r = $mysqli->query($addphone);
    if($r==true){
        $recargador =$r->fetch_assoc();

        return  $recargador['id_recargador'];
    }
    return null;
}
function cambiar_estado_recargador($correo,$estado,$fecha,$host,$user_agent,$remote_address)
{
       global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $id_cliente = getIDCliente($correo);
    $id_recargador = isIDinRecargadorTable($id_cliente);

    if($id_recargador!=null) {

        $query = "UPDATE recargador SET estado= ".$estado." WHERE id_recargador='". $id_recargador . "'";
        $rs = $mysqli->query($query);
        if ($rs == true) {
            $response = true;
            if($estado==1){
           $accion = "habilitado";
            }
            else{
                $accion = "deshabilitado";
            }

            $comentario = "Usted  ha sido ".$accion." como  recargador";

        $body = "                ";
        $resultado = enviarcorreo($correo, $body, $comentario);

            traza($fecha,$correo, $comentario,$body,'sistema');
            logs('ACT',$resultado,$correo,$host,$user_agent,$remote_address);


        }
        else {
            $response = false;
        }
    }
    else {
        $query = "Insert into recargador (id_cliente,estado) values ('" . $id_cliente . "','" . $estado . "')";
        $rs = $mysqli->query($query);
        if ($rs == true) {
            $response = true;

            $comentario = "Usted  ha sido habilitado como  recargador";

            $body = "                ";
            $resultado = enviarcorreo($correo, $body, $comentario);

            traza($fecha,$correo, $comentario,$body,'sistema');
            logs('ACT',$resultado,$correo,$host,$user_agent,$remote_address);


        } else {
            $response = false;
        }
    }
    return $response;
}
/*
 * Obtener los nuevos usuarios que pagan y no estaban en la BD
 */
function getNewUsers($hoy, $manana)
{
    global $configuracion;
    $mysqli = $configuracion->mysqli;
    $final = 0;
    $query = "SELECT * FROM recarga
WHERE fecha_recarga > '" . $hoy . " 00:00:00'
AND fecha_recarga < '" . $manana . " 00:00:00'";
    $result = $mysqli->query($query);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            if (!isInDBAudit($hoy, $row['recharged_number'])) {
                $final++;
            }
        }
        return $final;
    }
    return 0;
}
function getusedUsers()
{
    global $configuracion;
    $mysqli = $configuracion->mysqli;
    $final = array();
    $query = "SELECT id_historial  FROM historial";
    $result = $mysqli->query($query);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $final[] = $row['from_id'];
        }
        return count(array_unique($final));
    }
    return FALSE;
}

function getSaldoUsers()
{
    global $configuracion;
    $mysqli = $configuracion->mysqli;

    $query = "SELECT SUM(saldo) as saldo FROM cliente";
    $result = $mysqli->query($query);
    if ($result->num_rows > 0) {
        $rows = $result->fetch_assoc();
        $row = $rows['saldo'];
    }
    return $row;
}



function getTotalTransf($hoy, $manana)
{
    global $configuracion;
    $mysqli = $configuracion->mysqli;
    $row = array();

    $query = "SELECT SUM(monto) as money
FROM recarga WHERE fecha_recarga > '" . $hoy . " 00:00:00'
AND fecha_recarga < '" . $manana . " 00:00:00'";
    $result = $mysqli->query($query);
    if ($result->num_rows > 0) {
        $rows = $result->fetch_assoc();
        $row['hoy'] = $rows['money'];
    }

    $query = "SELECT SUM(monto) as money2 FROM recarga";
    $result = $mysqli->query($query);
    if ($result->num_rows > 0) {
        $rows = $result->fetch_assoc();
        $row['full'] = $rows['money2'];
    }
    return $row;
}

function getTodaySMS($hoy, $manana)
{
    global $configuracion;
    $mysqli = $configuracion->mysqli;
    $query = "SELECT * FROM historial WHERE fecha_envio > '" . $hoy . " 00:00:00' AND fecha_envio < '" . $manana . " 00:00:00'";
    $result = $mysqli->query($query);
    if ($result->num_rows > 0) {
        $final = array();
        while ($row = $result->fetch_assoc()) {
            $final[] = $row;
        }
        return $final;
    }
    return FALSE;
}



function getIDCliente($email)
{
       global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $sql = "SELECT * FROM cliente  WHERE correo = '" . $email . "'";

    $result = $mysqli->query($sql);
    if ($result->num_rows == 1) {
        $r = $result->fetch_assoc();
        return $r['id_cliente'];
    }
    return FALSE;
}
function getSaldo($valor)
{
       global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $variable = 'correo';
    if (is_numeric($valor))
        $variable = 'celular';

    $query = "SELECT saldo FROM cliente  WHERE " . $variable . " = '" . $valor . "'";

    $r = $mysqli->query($query);
    if ($r->num_rows == 1) {
        $saldo = $r->fetch_assoc();
        return $saldo['saldo'];
    }
    return null;
}
function confirmacion_entrega($from,$fecha,$dbhost,$user_agent,$remote_address){
    global $configuracion;
    $celular = getPhonebyEmail($from);
   $desconto= descontar($from,$configuracion->getValuesmsNacional());
	if($desconto==true){
    enviarcorreo($from, "mensaje entregado", '535'.$celular);
    traza($fecha, $from, $celular, "entregado", 'sistema');
    logs('confirmacion de entrega', "entregado", $from, $dbhost, $user_agent, $remote_address);
	}
}
function enviarcorreo($to, $body, $asunto = "smscubano", $attach = '', $sendas = '')
{

    include_once 'PHPMailer/class.phpmailer.php';

    global $configuracion;
    $credentials = $configuracion->getServerMailCredentials();
    $mail = new PHPMailer();

    $mail->Host =$credentials['mailhost'];
    $mail->FromName = $credentials['mailFromName'];
    $mail->From = $credentials['mailFrom'];
    $mail->Username = $credentials['mailUsername'];
    $mail->Password =$credentials['mailPassword'];



    if (is_array($to)) {
        foreach ($to as $email) {
            $mail->AddCC($email);
        }
    } else {
        $mail->AddAddress($to);
    }
    $mail->WordWrap = 70;
    $mail->IsHTML(true);
    $mail->Subject = utf8_decode($asunto);
    $mail->msgHTML(utf8_decode($body));
    //$mail->AltBody = $body;
    if ($attach != '') {
        $mail->AddAttachment($attach);
    }
    if (!$mail->Send()) {
        return 'Mailer Error: ' . $mail->ErrorInfo;
    }
    return 'Message has been sent';
}
function asignar_credito_activador($correo, $saldo)
{
       global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $id = getRecargadorfromIdMailpool($correo);
    $query = "UPDATE recargador SET credito += '" . $saldo . "' WHERE id_recargador = '" . $id . "' AND credito + '" . $saldo . "' < tope_credito";

    $rs = $mysqli->query($query);
    if ($rs == true) {
        return true;
    }
    return false;
}
function logs($comando, $result, $from, $host, $user_agent, $remote_address)
{

    $report = array();
    $estado = file_get_contents('logs/bitacora.' . date("Y-m-d") . '.txt');
    if ($estado == false) {
        $report['head'] = 'Bitacora de Multichat' . "\n";
        $report['columnas'] = 'Fecha|                   |Correo|                            |Comando|   |Resultado|    |Host|                   |UserAgent|                       |Remote Address' . "\n";
    }

    $report['fecha'] = date("Y-m-d H:i:s") . '       |';
    $report['correo'] = $from . '       |';
    $report['comando'] = $comando . '   |';
    $report['resultado'] = $result . '            |';
    $report['ip'] = $host . '             |';
    $report['agente'] = $user_agent . '                       |';
    $report['remote_address'] = $remote_address . ' ' . "\n";
    $fichero = 'bitacora.' . date("Y-m-d") . '.txt';
    file_put_contents('logs/' . $fichero . '', $report, FILE_APPEND | LOCK_EX);
}
function traza($fecha, $origen, $asunto, $cuerpo,$naturaleza)
{
    global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $query = " INSERT INTO traza(`fecha`,`origen`,`asunto`,`cuerpo`,`naturaleza`)
          VALUES ('" . $fecha . "','" . $origen . "','" . $asunto . "','" . $cuerpo . "','".$naturaleza."')  ";

    $mysqli->query($query);
}
function checkVCO($from,$vco,$fecha,$host,$user_agent,$remote_address){
    if(is_numeric($vco) && strlen($vco)==6) {

           global $configuracion;    
		   $mysqli=  $configuracion->mysqli;
        $consulta = "SELECT * FROM cliente WHERE correo ='$from'";
        $result = $mysqli->query($consulta);
        $resultad = $result->fetch_assoc();
        $codigo = $resultad['codigo_verficacion'];
        $celular = $resultad['numero_temporal'];

        if ($codigo == $vco && enviarCodigoRegistro($celular,$from) == true) {
            $query = "UPDATE cliente  SET
      celular='".$celular."',
      fecha_registro='".$fecha."'
  WHERE correo='".$from."'";

            $rs = $mysqli->query($query);
            if ($rs) {
                $subject = "VCO*SI";
                $body = "            ";
                $result=        enviarcorreo($from, $body, $subject);
            }



        }
        else {
            $subject = "VCO*NO";
            $body = "          ";
            $result=      enviarcorreo($from, $body, $subject);
        }

    }
    else {
        $subject = "VCO*NO";
        $body = "          ";
        $result=      enviarcorreo($from, $body, $subject);

    }
    traza($fecha,$from, $subject,$body,'sistema');
    logs('VCO',$result,$from,$host,$user_agent,$remote_address);
}
function getPhonebyEmail($from){
       global $configuracion;     
	   $mysqli=  $configuracion->mysqli;
    $query = " SELECT celular FROM cliente WHERE correo ='$from'";

    $rs=$mysqli->query($query);
    if($rs->num_rows >0){
        $cliente = $rs->fetch_assoc();
        return $cliente['celular'];
    }
    return null;
}
function getLinks(){
    global $configuracion;
    $linkeados = array();
    $mysqli=  $configuracion->mysqli;
    $query = " SELECT link FROM link";

    $rs=$mysqli->query($query);
    for ($links = array (); $row = $rs->fetch_assoc(); $links[] = $row);

    for($i=0;$i<count($links);$i++){
        $linkeados[$i] = $links[$i]['link'];
    }

    return $linkeados;
}
function getContent($from){
    $referencia_cliente = getReferenciaCliente($from);
    $contenido = getContenido();
    if($referencia_cliente != $contenido['referencia_contenido']){
        actualizarReferenciaCliente($from,$contenido['referencia_contenido']);
     return $contenido['contenido'];
    }
return false;
}
function getReferenciaCliente($from){
    global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $query = " SELECT referencia_contenido FROM cliente WHERE  correo = '".$from."' ";
    $rs=$mysqli->query($query);
    if($rs->num_rows >0){
        $cliente = $rs->fetch_assoc();
        return $cliente['referencia_contenido'];
    }
    return false;
}
function getContenido(){
    global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $query = " SELECT * FROM contenido";
    $rs=$mysqli->query($query);
    if($rs->num_rows >0){
        $contenido = $rs->fetch_assoc();
        return $contenido;
    }
    return false;
}
function actualizarReferenciaCliente($from,$refencia){
    global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $query = "UPDATE cliente  SET referencia_contenido = '".$refencia ."' WHERE correo = '" . $from . "'";
    $mysqli->query($query);
}
function isEmailAddress($email)
{
    // Split the email into a local and domain
    $atIndex = strrpos($email, "@");
    $domain = substr($email, $atIndex + 1);
    $local = substr($email, 0, $atIndex);

    // Check Length of domain
    $domainLen = strlen($domain);

    if ($domainLen < 1 || $domainLen > 255)
    {
        return false;
    }

    /*
     * Check the local address
     * We're a bit more conservative about what constitutes a "legal" address, that is, a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-
     * The first and last character in local cannot be a period ('.')
     * Also, period should not appear 2 or more times consecutively
     */
    $allowed = 'a-zA-Z0-9.!#$%&’*+\/=?^_`{|}~-';
    $regex = "/^[$allowed][\.$allowed]{0,63}$/";

    if (!preg_match($regex, $local) || substr($local, -1) == '.' || $local[0] == '.' || preg_match('/\.\./', $local))
    {
        return false;
    }

    // No problem if the domain looks like an IP address, ish
    $regex = '/^[0-9\.]+$/';

    if (preg_match($regex, $domain))
    {
        return true;
    }

    // Check Lengths
    $localLen = strlen($local);

    if ($localLen < 1 || $localLen > 64)
    {
        return false;
    }

    // Check the domain
    $domain_array = explode(".", rtrim($domain, '.'));
    $regex = '/^[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/';

    foreach ($domain_array as $domain)
    {
        // Convert domain to punycode
       // $domain = JStringPunycode::toPunycode($domain);

        // Must be something
        if (!$domain)
        {
            return false;
        }

        // Check for invalid characters
        if (!preg_match($regex, $domain))
        {
            return false;
        }

        // Check for a dash at the beginning of the domain
        if (strpos($domain, '-') === 0)
        {
            return false;
        }

        // Check for a dash at the end of the domain
        $length = strlen($domain) - 1;

        if (strpos($domain, '-', $length) === $length)
        {
            return false;
        }
    }

    return true;
}
function actualizarAnuevo($cuerpo){
    global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $query = "UPDATE contenido  SET contenido = '".$cuerpo ."'";
    $rs=$mysqli->query($query);
    if($rs ==true){

        return true;
    }
    return false;
}
function actualizarALink($cuerpo){
    global $configuracion;
    $mysqli=  $configuracion->mysqli;
    $query = "UPDATE link  SET link = '".$cuerpo ."'";
    $rs=$mysqli->query($query);
    if($rs ==true){

        return true;
    }
    return false;
}


<?php

/**
 * Created by PhpStorm.
 * User: EGI
 * Date: 27/07/2016
 * Time: 17:53
 */
include_once 'multichat/autoload.php';


class Configuracion{
    public $enviroment = 'casa';
    public $dbhost = 'localhost';
    public $dbuser = 'recargador';
    public $dbpass = 'deuimata7072';
    public $dbname = 'sxm_multichat';
    public $smsnacional = '0.02';
    public $platform = 'mail';
    public $imap_user = 'multichat@localhost';
    public $imap_pass = 'multichat';
    public $imap_server = 'localhost';
    public $bustia = 'smsxmail@localhost';
    public $bustiarecarga= 'droid@localhost';
    public $dominioemail = '@localhost';
    public $test = 'turyhg12@gmail.com';
    public $test1 = 'bachecubano16@gmail.com';
    public $mailhost ='127.0.0.1';
    public  $mailport = '143';
    public  $mailFromName = 'multichat';
    public  $mailFrom = 'multichat@localhost';
    public $mailUsername = 'multichat';
    public $mailPassword= 'multichat';
    public   $prefix=  '';
    public  $driver ='mysql';
    public $mysqli;

    public function  __construct($enviroment='casa', $accion=' ') {

        if($enviroment=='mocha')
        {
        $this->changeEnviromentMocha();
        }

        if($accion == 'prueba') {
            $this->changetoDBPrueba();
        }
        $this->Conexion();
    }

    public function getConfiguracion(){
        return "Falta implementarla";
 }
    public function setConfiguracion(){

     return "Falta implementarla";


    }
    public function setPlatform($platform){

        $this->platform = $platform;
    }
    public function getPlatform(){
    return $this->platform;
    }
    public function cambiarConexion($dbuser,$dbname,$dbpass,$dbhost ='localhost'){
        $this->mysqli =  new mysqli($dbhost, $dbuser, $dbpass, $dbname);
        if ($this->mysqli->connect_error) {
            die('Error General');
            exit();
        }
    }
    public function Conexion(){

            $this->mysqli = new  mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);

        if ($this->mysqli->connect_error) {
            die('Error General');
            exit();
        }

    }

    public function getDBCredentials(){
        $array = array(
            'dbhost'=> $this->dbhost,
            'dbuser'=> $this->dbuser,
            'dbname'=> $this->dbname,
            'dbpass'=> $this->dbpass

        );
        return $array;

}
    public function changetoDBExternaInternet(){
        $this->dbuser = 'sxm_smsxmail';
        $this->dbname = 'sxm_smsxmail'; // Database name
        $this->dbpass = '_KDTwC5XbP1';       // Password for database authentication

        $this->cambiarConexion( $this->dbuser, $this->dbname,   $this->dbpass,$this->dbhost);

    }
    public function changetoDBExterna(){
        $this->dbuser = 'recargador';
        $this->dbname = 'erich33_w2m'; // Database name
        $this->dbpass = 'deuimata7072';       // Password for database authentication

        $this->cambiarConexion( $this->dbuser, $this->dbname,   $this->dbpass,$this->dbhost);

    }
    public function changetoDBPrueba(){
        $this->dbuser = 'sxm_smsxmail';
        $this->dbname = 'sxm_labx'; // Database name
        $this->dbpass = 'smscubano123*';       // Password for database authentication
    $this->cambiarConexion($this->dbuser,  $this->dbname ,   $this->dbpass,$this->dbhost);

    }

    public function changetoDBOrigen(){
        $this->dbhost = 'localhost';      // Database host name
        $this->dbuser = 'recargador';
        $this->dbname = 'nxc_smsc'; // Database name
        $this->dbpass = 'deuimata7072';       // Password for database authentication
        $this->cambiarConexion( $this->dbuser, $this->dbname,   $this->dbpass,$this->dbhost);

    }

    public function  changeEnviromentMocha(){
        $this->dbuser  = 'sxm_smscubano';
        $this->dbname= 'sxm_smscubano';
        $this->dbpass = 'smscubano123*';
        $this->imap_user = 'labx@smsxmail.com';
        $this->imap_pass = 'rON8W8[FN-SB';
        $this->bustia = 'turyhg12@gmail.com';
        $this->mailhost = 'localhost';
        $this-> mailFromName = 'SMSCUBANO';
        $this->mailFrom = 'smscubano@smsxmail.com';
        $this->mailUsername = 'labx';
        $this->mailPassword= '@Q!$h#.?2nBF';
        $this->bustiarecarga = 'droid@nauta.cu';
        $this->dominioemail = '@mms.cubacel.cu';

    }
    public function  changeEnviromentCasa(){
        $this->dbuser  = 'recargador';
        $this->dbname= 'nxc_smsc';
        $this->dbpass = 'deuimata7072';
        $this->imap_user = 'smscubano@smsxmail.com';
        $this->imap_pass = 'smscubano';
        $this->bustia = 'smsxmail@localhost';
        $this->bustiarecarga = 'droid@localhost';
        $this->dominioemail = '@localhost';
        $this->mailhost ='127.0.0.1';
       $this->mailFromName = 'smscubano';
       $this->mailFrom = 'smscubano@localhost';
        $this->mailUsername = 'smscubano';
        $this->mailPassword= 'smscubano';

    }
    public function setDBCredentials($dbuser,$dbname,$dbpass,$dbhost ='localhost'){
        $this->dbhost = $dbhost;
        $this->dbuser = $dbuser;
        $this->dbname = $dbname;
        $this->dbpass =$dbpass;

}
    public function getImapCredentials(){
        $array = array(
            'imap_user'=> $this->imap_user,
            'imap_pass'=> $this->imap_pass,
            'imap_server'=> $this->imap_server

        );
        return $array;

}
    public function getServerMailCredentials(){
        $array = array(
            'mailhost'=> $this->mailhost,
            'mailFromName'=> $this->mailFromName,
            'mailFrom'=> $this->mailFrom,
            'mailUsername'=> $this->mailUsername,
            'mailPassword'=> $this->mailPassword

        );
        return $array;
    }
    public function setServerCredentials($imap_user,$imap_pass,$imap_server){
        $this->imap_user= $imap_user;
        $this->imap_pass=$imap_pass;
        $this->imap_server = $imap_server;
}
    public function getValuesmsNacional(){

        return $this->smsnacional;
    }
    public function setValuesmsNacional($smsnacional){
        $this->smsnacional= $smsnacional;
}


}
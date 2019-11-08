<?php

/*if ( empty($tarea_programada) ){
    // Mapping
    include(PATH_CONSULTAS . 'controlador/mappings.php');

    // Acciones del M�dulo Frontend
    //require_once PATH_CONSULTAS . 'controlador/actions/consultar_clienteAction.php';

    // Acciones del M�dulo Backend
    require_once PATH_CONSULTAS . 'controlador/actions/conectarse_dbAction.php';

    // Formularios que son validables al lado del servidor
    require_once PATH_CONSULTAS . 'controlador/forms/validar_bdForm.php';
    //require_once PATH_CONSULTAS . 'controlador/forms/Coe_ingresar_sistemaForm.php';
}*/

class Consulta {
    //** Variables de la clase
    var $campos;
    var $tabla;
    var $condiciones;
    var $valores;
    var $consulta;
    var $numfilas;
    var $afecfilas;
    var $error;
    var $error_msn;
    private $link;
    private $lastID = "SELECT SCOPE_IDENTITY() AS ID";
    public $lastid;
    public $transaccion;
    public $resultado;

    /**
     * Consulta constructor.
     * @param $campos
     */
    public function __construct()
    {
        $this->conectar();
    }


    //** Realiza la conexi�n a la base de datos de acuerdo a la arquitectura
    private function conectar()
    {
        //Almacena el tipo de base de datos a la que se quiere conectar
        $tipo_db = strtoupper(TIPO_DB);
        $valida  = true;

        //Define los arreglos en mayuscula de los resultados de la consulta
        //define('ADODB_ASSOC_CASE', 1);

        //Averigua si tiene asociado el funcionamiento de ASP para buscar la base de datos correspondiente
        if (  ASP_SERVICE == 1 && !empty($_SESSION['DB']) )
            $DB = $_SESSION['DB'];
        else
            $DB = DB;

        //Inicilaiza las variables de transaccion
        $this->transaccion = 1024;
        $this->resultado = 2;

        //Arquitectura para ORACLE
        if($tipo_db == "ORACLE"){
            $this->link = ADONewConnection("oci8"); # eg. 'mysql' or 'oci8'
            $this->link->Connect(INSTANCIA, USUARIO, CLAVE);

            //Arquitectura para SQL SERVER
        }elseif($tipo_db == "SQL SERVER" || $tipo_db == "MSSQL"){


            $this->link = ADONewConnection('odbc_mssql');
            $dsn = "Driver={SQL Server};Server=".SERVIDOR.";Database=".DB.";";
            $this->link->Connect($dsn, USUARIO, CLAVE);
            $this->link->SetFetchMode(2);
            $this->link->fmtTimeStamp="'Y-m-d H:i:s'";
            /*
            $link = &ADONewConnection("ado_mssql");
			$DSN="PROVIDER=MSDASQL;DRIVER={SQL Server};"
			. "SERVER=".SERVIDOR.";DATABASE=".DB.";UID=".USUARIO.";PWD=".CLAVE.";"  ;
			$link->Connect($DSN);
			$link->SetFetchMode(2);
			$link->fmtTimeStamp="'Y-m-d H:i:s'";


            $link = &ADONewConnection('mssql'); # create a connection
			$link->PConnect(SERVIDOR, USUARIO, CLAVE, $DB);
			$link->SetFetchMode(2);
			$link->fmtTimeStamp="'Ymd H:i:s'";
            */

            //Arquitectura para MYSQL
        }elseif($tipo_db == "MYSQL"){
            $this->link = ADONewConnection("mysql"); # eg. 'mysql' or 'oci8'
            $this->link->Connect(SERVIDOR, USUARIO, CLAVE, $DB);

            //Arquitectura para POSTGRES
        }elseif($tipo_db == "POSTGRES"){
            $this->link = ADONewConnection("postgres"); # eg. 'mysql' or 'oci8'
            $this->link->Connect(SERVIDOR, USUARIO, CLAVE, $DB);

        }elseif($tipo_db == "SQLITE"){
            $this->link =& NewADOConnection('pdo');
            $this->link->Connect($DB);

        }


        // Verifica  si la transaccion tuvo exito
        if ($valida == true)
            return $this->link;
        else
            return $valida;
    }


    //** Realiza la desconexi�n de la base de datos
    function desconectar()
    {
        $this->link->Close();
    }

    function getLink() {
        return $this->link;
    }


    //** Instancia los campos de la consulta
    function setCampos($valor){
        $this->campos = $valor;
    }


    //** Instancia las tablas de la consulta
    function setTabla($valor){
        $this->tabla = $valor;
    }


    //** Instancia las condiciones de la consulta
    function setCondicion($valor){
        $this->condiciones = $valor;
    }


    //** Instancia una consulta completa sin necesidad de cargar las demas variables
    function setValores($valor){
        $this->valores = $valor;
    }


    //** Instancia una consulta completa sin necesidad de cargar las demas variables
    function setConsulta($valor){
        $this->consulta = $valor;
        $this->resultado = 2;
    }


    //** Arma la consulta  de selecci�n de registros
    function consultaSelect(){

        if (strpos($this->tabla, "VISTA_USUARIO") !== false )
            $this->consulta = "SELECT DISTINCT ".$this->campos." FROM ".$this->tabla." WHERE 1=1 ".$this->condiciones;
        else
            $this->consulta = "SELECT ".$this->campos." FROM ".$this->tabla." WHERE 1=1 ".$this->condiciones;

        $this->resultado = 2;
    }


    //** Arma la consulta de inserci�n de  registros
    function consultaInsert(){
        $this->consulta = "INSERT INTO ".$this->tabla." (".$this->campos.") VALUES (".$this->valores.")";
        $this->resultado = 2;
    }


    //** Arma la consulta de actualizaci�n de registros
    function consultaUpdate(){
        $this->consulta = "UPDATE ".$this->tabla." SET ".$this->campos." WHERE 1=1 ".$this->condiciones;
        $this->resultado = 2;
    }


    //** Arma la  consulta de eliminaci�n de registros
    function consultaDelete(){
        //$this->consulta = "DELETE FROM ".$this->tabla." WHERE 1=1 ".$this->condiciones;
        $this->consulta = "UPDATE ".$this->tabla." SET ELIMINADO = '".$_SESSION['usrio_numero']."' WHERE 1=1 ".$this->condiciones;
        $this->resultado = 2;
    }


    //** Realiza una transacci�n a la base de datos de acuerdo a la variable "consulta"
    function ejecutarConsulta ($metodo="show", $inicio=0, $numreg=""){
        //Inicializa la variable de conteo para los registros encontrados

        $this->numfil = 0;
        //global $link;

        if ( SQL_GENERAL == 1 )
            echo "----".$this->consulta;

        // Se codifica la cadena de la transaccion para que acepte caracteres especiales
        if ( strpos($this->consulta, "SELECT") === false )
            $this->consulta = utf8_decode($this->consulta);
        else
            $this->consulta = $this->consulta;

        //Verifica  si no existe error en consultas anteriores
        if ( $this->resultado == 2 && $this->consulta != "" ){
            if ( $numreg != "" )
                $ejecucion = $this->link->Selectlimit($this->consulta, $numreg, $inicio);
            else
                $ejecucion = $this->link->Execute($this->consulta);


            $errorMSG = $this->link->ErrorMSG();
            $errorNO = $this->link->errorNO();

            $this->afecfilas = $this->link->Affected_Rows();
            $this->lastid    = $this->link->Insert_ID();

            if ($ejecucion == false){
                Consulta::codificaError($this->link->ErrorMSG());
                $this->error = $errorNO;
                $this->error_msn = $errorMSG;
                $this->resultado = 0;

                $fecha_regis = date("Y-m-d H:i:s");

                //Registro de los errores generados en cualquier query.
                $regist_error  = "INSERT INTO GT_ERRORES (USRIO_NUMERO, FECHA, ERROR_MSG, TRANSACCION, EMPRESA) VALUES (";
                $regist_error .= "1, ".$this->link->DBtimestamp($fecha_regis).", '".addslashes($errorMSG)."', ";
                $regist_error .= "'".addslashes($this->consulta)."', 'claro')";

                if ( strpos($regist_error, "CL_PAGOSCLARO") !== false || strpos($regist_error, "GT_PAGO_PASARELA") !== false ){
                    $file = fopen("../tmp/LogTrans_".date('YmdHis').".txt", "w");
                    fwrite($file, $regist_error);
                    fclose($file);
                }
                $this->link->execute($regist_error);

                $correos_enviar = CORREO_ALERTAS;
                if( !empty($_SESSION['usrio_empr']) && !empty($correos_enviar) ){

                    $descrip = file_get_contents(PATH_ARCHIVOS_LOCAL.'Documentacion/plantillaLog.php');

                    $asunto = "Sistema Rgtech: Alerta - ".$_SESSION['usrio_empr'];

                    $descrip  = str_replace("#EMPRESA#", $_SESSION['usrio_empr'], $descrip);
                    $descrip  = str_replace("#USUARIO#", $_SESSION['usrio_nombre'], $descrip);
                    $descrip  = str_replace("#ROL#", $_SESSION['usrio_rolname'], $descrip);
                    $descrip  = str_replace("#EMAIL#", $_SESSION['usrio_email'], $descrip);
                    $descrip  = str_replace("#TELEFONO#", $_SESSION['usrio_telef'], $descrip);
                    $descrip  = str_replace("#MOVIL#", $_SESSION['usrio_movil'], $descrip);
                    $descrip  = str_replace("#FECHA#", $fecha_regis, $descrip);
                    $descrip  = str_replace("#ERROR#", $errorMSG, $descrip);
                    $descrip  = str_replace("#TRANSACCION#", $this->consulta, $descrip);


                    if ( strpos($correos_enviar, ";") === false )
                        $correos_enviar .= ";";

                    $lista_correos = explode(";", $correos_enviar);

                    foreach( $lista_correos as $key => $val){

                        if ( !empty($val) )
                            Tarea::enviarCorreo("Soporte Infodec", "", "", $descrip, "", $val, $asunto, "", "", "", "1");
                    }
                }
            }
        }

        //Si no se ha producido error en la consulta y esta en m�todo "show" (capturando datos) , carga los datos para ser devueltos
        if ($metodo == "show" && $this->resultado == 2 && $this->consulta != "" && strpos($this->consulta, "SELECT") !== false ){
            $datos = $ejecucion->GetArray();
            $this->numfil = $ejecucion->RecordCount();
            return $datos;
        }
    }


    //** Almacena los errores efectudos en las transacciones
    function codificaError($codigo)
    {
        global $error;

        if( is_array($error) )
            array_push($error, $codigo);
    }


    function printQuery() {
        echo $this->consulta."<br>";
    }


    //** Retornar el numero de filas afectadas
    function numeroFilas($tipo="numfil")
    {
        if ($tipo == "numfil")
            return $this->numfil;
        else
            return $this->afecfilas;
    }


    //** Retornar el query generado
    function getConsulta()
    {
        return $this->consulta;
    }


    //**  Devuelve el resultado de la transaccion efectuada en la base de datos
    function getResultado(){
        //echo $this->error;
        return $this->resultado;
    }


    //** Carga la variable de control para definir si se realiza el query
    function setResultado(){
        $this->resultado = 2;
    }


    //** Iniciar la transaccion en la base de datos
    function inicioTransaccion(){
        //global $link;
        $this->link->BeginTrans();
    }


    //** Iniciar la transaccion en la base de datos
    function finalTransaccion(){
        //global $link;
        $this->link->CommitTrans();
    }


    //** Devuelve el mensaje de error en el resultado de la transaccion efectuada en la base de datos
    function getError(){
        //echo $this->error_msn;
        return $this->error_msn;
    }

    //** Devuelve el mensaje de error en el resultado de la transaccion efectuada en la base de datos
    function getidError(){
        //echo $this->error_msn;
        return $this->error;
    }

    //** Forza a que falle la transaccion
    function fallaTransaccion(){
        //echo $this->error_msn;
        //global $link;
        $this->link->RollbackTrans();
    }

    function getInsertID(){
        return $this->ejecutarConsulta($this->lastID);
    }

}
?>
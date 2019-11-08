<?php
/**
 * Created by Infodec S.A.S.
 * Project: claro
 * User: Usuario
 * Date: 16 de enero de 2019
 * Time: 9:35 AM
 */

class ServiciosClaro
{
    /*
     * Funciones para consumir tramas iTEL
     */

    function consultaImpoconsumo( $informacionCompraPaquete )
    {
        # Se consultan las variables asociadas a la trama Impoconsumo (iTEL 223)
        $infoVariable = $this->getVariables("'VAR_CLWSIMPOCONSUMO'");

        # Se obtienen lo datos del consumo desde Base de Datos
        list($paquetes, $usuario, $clave, $metodo, $wsdl) = explode( ";", $infoVariable['VAR_CLWSIMPOCONSUMO']);

        $tiposValidos = str_replace('"', '', $informacionCompraPaquete['tiposValidos']);

        // solo se validan los paquetes de datos y todo incluido (DATOS, COMBO) prepago
        if( (strpos($paquetes, trim($tiposValidos)) !== false) /*&& ($valores['TIPO_COMPRA_DESC'] != 'WF')*/ ){

            if($infoVariable['error'] == false) {

                $msisdn = $informacionCompraPaquete['numeroLinea'];

                $trama = $usuario.'!'.$clave.'!'.$metodo.'!'.$msisdn.'!!*';

                $parametrosPeticion = array('TRAMA' => $trama, 'IP_ORIGEN' => $_SERVER['REMOTE_ADDR']);
                $parametrosWS = array( 'Input_Parameters' => $parametrosPeticion );
                $opciones = array('connection_timeout' => '10');

                try{
                    $soap = new Soap();
                    $respuesta = $soap->callSoapWS($wsdl,'ejecutarTrama', $parametrosWS, $opciones);

                    if( !is_array($respuesta) ){
                        $codigo = $respuesta->CODIGO;
                        $mensaje = $respuesta->MENSAJE;
                        $usuario = $mensaje->USUARIO;
                        $descripcion  = $mensaje->DESCRIPCION;

                        if (LOG_FILE == 1) {
                            $log = "\n Fecha: " . date('Y-m-d H:i:s') . "\n\n Trama: " . $trama
                                . ".\n\n Respuesta: " . $mensaje . ".";
                            $file = fopen("../tmp/impoconsumo" . date('YmdHis') . ".txt", "a+");
                            fwrite($file, $log);
                            fclose($file);
                        }

                        if( $codigo == 1 ){

                            $impoconsumo_tmp = explode('!', $mensaje);
                            $impoconsumo = $impoconsumo_tmp[4];
                            list($valor_ipc, $ipc_actua, $cobrar_ipc) = explode( ";", $impoconsumo);

                            if($cobrar_ipc == 0) {
                                $valorPermitido = $valor_ipc - $ipc_actua;
                                #$_SESSION['permitido_impoconsumo'] = $valorPermitido."--".$log_trama;
                                $retorno = array("error" => false, "mensaje" => $cobrar_ipc, "valorPermitido" => $valorPermitido);
                            } else {
                                $retorno = array("error" => true, "mensaje" => 'Paquetes no disponibles en el momento, intente más tarde. 2');
                            }
                        } else {
                            $retorno = array("error" => true, "mensaje" => "En este momento no podemos atender esta solicitud, intenta más tarde 3");
                        }
                    } else {
                        echo $respuesta;
                        $retorno = array("error" => true, "mensaje" => "En este momento no podemos atender esta solicitud, intenta más tarde 4 " . $respuesta);
                    }

                } catch(Exception $e) {
                    $retorno = array("error" => true, "mensaje" => "En este momento no podemos atender esta solicitud, intenta más tarde 5");
                }

            } else {
                $retorno = array("error" => true, "mensaje" => "En este momento no podemos atender esta solicitud, intenta más tarde 6");
            }
        } else {
            $retorno = array("error" => false, "valorPermitido" => 0);
        }

        return $retorno;
    }

    /*
     * Funciones generales y/o utilitarias
     */

    /**
     * Función que consulta nombre y valor de las variables enviadas como parámetro
     * @param $nombreVariables "Nombre de variables que se desean consultar"
     * @return array Error e información de las variables si existen en la base de datos
     */
    private function getVariables($nombreVariables){

        $consulta = new Consulta();

        $queryVariables  = "SELECT NOMBRE, VALOR FROM CP_VARIABLES WHERE NOMBRE IN (" . $nombreVariables .") ";
        $queryVariables .= "AND " . $consulta->getLink()->SQLDate("Y-m-d") . " BETWEEN ";
        $queryVariables .= $consulta->getLink()->SQLDate('Y-m-d', 'FECHA_INICIO')." AND ";
        $queryVariables .= $consulta->getLink()->SQLDate('Y-m-d',  $consulta->getLink()->ifNull("FECHA_FIN", "'3000-01-01'"));
        $queryVariables .= " AND ELIMINADO = -1 AND ACTIVO = '1'";

        $consulta->setConsulta($queryVariables);
        $infoVariables = $consulta->ejecutarConsulta();

        if( $consulta->numeroFilas() == 0 )
            $retorno = array('error'=> true, 'mensaje' => 'Variable(s) no encontrada(s)');
        else {

            $retorno = array();

            foreach($infoVariables as $key => $valor){
                $retorno[$valor["NOMBRE"]] = $valor["VALOR"];
            }

            $retorno['error'] = false;
        }

        $consulta->desconectar();
        unset($consulta);

        return $retorno;
    }

    /**
     * Función que busca cuales son los tipos de paquetes asociados a un tipo de compra
     * @param string $listNumeroPadre
     * @param string $listNumeroHijo
     * @param string $valorBusqueda
     * @param string $tipoBusqueda
     * @param string $descriptor
     * @return array
     */
    public function buscarValoresDependencias($listNumeroPadre="", $listNumeroHijo="", $valorBusqueda="", $tipoBusqueda="", $descriptor="0"){

        $consulta = new Consulta();

        $val_select  = " DISTINCT B.VALOR_LISTA AS OPCIONES_PADRE, C.VALOR_LISTA AS OPCIONES_HIJO, D.VALOR_ES";
        $val_select .= " AS PADRE_VALOR, E.VALOR_ES AS HIJO_VALOR ";

        $condicion ="";

        if( $tipoBusqueda == "HIJOS" ){
            if( $descriptor == "1" )
                $val_select = " DISTINCT E.CODIGO AS CODIGO, E.VALOR_ES AS VALOR ";
            $condicion = " AND B.VALOR_LISTA = '".$valorBusqueda."' ";
        }elseif( $tipoBusqueda == "PADRE" ){
            if( $descriptor == "1" )
                $val_select = " DISTINCT D.CODIGO AS CODIGO, D.VALOR_ES AS VALOR ";
            $condicion = " AND C.VALOR_LISTA = '".$valorBusqueda."' ";
        }

        $consultaSQL  = "SELECT ".$val_select." FROM GT_DEPENDENCIAS A, GT_VALORES_PADRE B, GT_VALORES_HIJO C, GT_VALORES D, ";
        $consultaSQL .= "GT_VALORES E WHERE A.DEPE_NUMERO = B.DEPE_NUMERO AND A.LIST_NUMERO_P = B.LIST_NUMERO_P AND ";
        $consultaSQL .= "A.LIST_NUMERO_H = B.LIST_NUMERO_H AND B.PADR_NUMERO = C.PADR_NUMERO AND A.DEPE_NUMERO = C.DEPE_NUMERO ";
        $consultaSQL .= "AND B.LIST_NUMERO_H = C.LIST_NUMERO_H AND D.LIST_NUMERO = A.LIST_NUMERO_P AND ";
        $consultaSQL .= "E.LIST_NUMERO = A.LIST_NUMERO_H AND B.VALOR_LISTA = D.CODIGO AND C.VALOR_LISTA = E.CODIGO ";
        $consultaSQL .= "AND D.ACTIVO = '1' AND E.ACTIVO = '1' AND A.LIST_NUMERO_P = ".$listNumeroPadre." AND ";
        $consultaSQL .= "A.LIST_NUMERO_H = ".$listNumeroHijo." ".$condicion." AND A.ELIMINADO = '-1' AND ";
        $consultaSQL .= "B.ELIMINADO = '-1' AND C.ELIMINADO = '-1' AND D.ELIMINADO = '-1' AND E.ELIMINADO = '-1'";

        //return $consultaSQL;

        $consulta->setConsulta($consultaSQL);
        $datos = $consulta->ejecutarConsulta();

        if( $consulta->numeroFilas() == 0 )
            $retorno = array('error'=> true, 'mensaje' => 'Valores no encontrados');
        else{
            $retorno = $datos;
        }

        return $retorno;

    }
}
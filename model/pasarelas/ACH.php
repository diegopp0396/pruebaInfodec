<?php

class ACH extends Pasarela
{
    var $pseUrl; //Ingrese aquí su propio apiKey.
    var $ppeUrl; //Ingrese aquí su propio apiLogin.
    var $ppeCode; //Ingrese su Id de cuenta.
    var $serviceCode; //Ingrese aquí su propio apiLogin de PSE.
    var $options;
    var $input_header;
    var $xmlSoapOrg;
    var $faultcode;
    var $faultstring;
    var $language;
    var $isTest;
    var $currency = "COP";
    var $paisPago = "CO";
    var $paisComp = "CO";
    var $paisOrde = "CO";
    var $paisPaga = "CO";

    var $nonce;
    var $timestamp;
    var $idBody;
    var $idSecurityToken;
    var $messageHeader;
    var $soapHeader;
    var $soapUriAction;
    var $soapAction;
    var $soapNameSpaces;

    /**
     * ACH constructor.
     */
    public function __construct()
    {
        $this->pseUrl = ACH_URL; //Ingrese aquí su propio wsdl externo pseUrl.
        $this->ppeUrl = ACH_PPE_URL; //Ingrese aquí su propio url de retorno ppeUrl.
        $this->ppeCode = ACH_PPE_CODE; //Ingrese aquí su propio Nit ppeCode.
        $this->serviceCode = ACH_SERVICE_CODE; //Ingrese aquí su propio serviceCode de PSE.
        $this->language = "es"; //Seleccione el idioma.
        $this->isTest = (ACH_TEST == 1) ? true : false; //Dejarlo True cuando sean pruebas.
        $this->options = array("trace" => 1);
        $this->xmlSoapOrg = "http://schemas.xmlsoap.org/ws/2002/07";
        $this->soapUriAction = ACH_SOAP_URIACTION;
        $this->soapNameSpaces = explode(',', ACH_SOAP_NAMESPACES);
    }

    function achTransaccion($datosPago, $datosMedioPago)
    {
        set_time_limit(0);

        $existeTransaccion = $this->consultarTransaccion($datosPago["IDTransaccion"], $datosPago["numeroFactura"]);

        if ($existeTransaccion["error"] == true)
            return array("error" => true, "mensaje" => $existeTransaccion["mensaje"]);

        if (empty($datosPago["valorTotal"]) /*|| $datosPago['registroPago'] == "fracaso" No se usa es la misma validacion de existeTransaccion*/)
            return array("error" => true, "mensaje" => "Error con los valores");

        if ($datosPago["iva"] == "")
            $datosPago["iva"] = 0;

        //Se quitan los espacios en blanco que puedan haber en los valores

        $datosPago["valorTotal"] = trim($datosPago["valorTotal"]);
        $datosPago["iva"] = trim($datosPago["iva"]);
        $datosMedioPago["email"] = trim($datosMedioPago["email"]);

        $currency = (empty($datosPago["moneda"])) ? $this->currency : $datosPago["moneda"];

        $datosEmpresa = $this->consultarDatosEmpresa();

        if ($datosEmpresa["error"] == false) {
            $empresa = $datosEmpresa["empresa"];
            $nit = $datosEmpresa["nit"];
        } else {
            return array("error" => true, "mensaje" => $datosEmpresa["mensaje"]);
        }

        $valores['PAIS'] = $this->paisPago;
        $valores['COMP_DIRPAIS'] = $this->paisComp;
        $valores['ORDE_DIRPAIS'] = $this->paisOrde;
        $valores['PAGA_DIRPAIS'] = $this->paisPaga;

        if ($datosMedioPago["formaPago"] == 1) {
            $tiket_id = $datosPago["numeroFactura"];
            $valor_llave = $existeTransaccion["valor_llave"];

            if( strlen($datosPago["numeroFactura"]) > 18 || !is_numeric($datosPago["numeroFactura"]) )
                $tiket_id = $valor_llave;

            $datosTransaccion['PSE_SERVICE_CODE'] = $this->accountId;
            $datosTransaccion['PSE_FINANCIAL_INSTITUTION_CODE'] = $datosMedioPago["banco"];
            $datosTransaccion['PSE_TRANSACTION_VALUE'] = $datosPago["valorTotal"];
            $datosTransaccion['PSE_VAT_VALUE'] = $datosPago["iva"]; // IVA $valores['IVA'];
            $datosTransaccion['PSE_TICKET_ID'] = $tiket_id; //Número de Factura o referencia del Pago (ticketID).
            $datosTransaccion['PSE_ENTITY_URL'] = $url_retorno;
            $datosTransaccion['PSE_USER_TYPE'] = $datosMedioPago["tipoCliente"];
            $datosTransaccion['PSE_REFERENCE1'] = $datosPago['numeroFactura']."_".$datosPago['numeroIntentos'];
            $datosTransaccion['PSE_REFERENCE2'] = $datosMedioPago['tipoDocumento'];
            $datosTransaccion['PSE_REFERENCE3'] = $datosMedioPago['numeroDocumento'];
            $datosTransaccion['PSE_SOLICITE_DATE'] = date('Y-m-d');
            $datosTransaccion['PSE_PAYMENT_DESCRIPTION'] = $datosPago['descripcion'];
            $datosTransaccion['PSE_CURRENCY'] = $currency;

            $resultado = json_decode(json_encode($this->procesarTransaccion($datosTransaccion)), true);

            $returnCode = $resultado['createTransactionPaymentResponseInformation']['returnCode'];

            if ($returnCode == "SUCCESS") {
                $transaccion = $resultado['createTransactionPaymentResponseInformation'];

                $actu_pago  = "UPDATE GT_PAGO_PASARELA SET EMPRESA = '".$empresa."', NIT = '".$nit."', ";
                $actu_pago .= "FECHA_TRANSACCION = ##FECHA_TRANSACCION##, ";
                $actu_pago .= "ID_ORDEN = '".$valor_llave."', CUS = '".$transaccion['trazabilityCode']."', ";
                $actu_pago .= "VALOR = '".$datosPago['valorTotal']."', IVA = '".$datosPago['iva']."', VALOR_TOTAL = '".$datosPago['valorTotal'].
                $actu_pago .= "', MONEDA = '".$currency."', IP_ORIGEN = '".$_SERVER['REMOTE_ADDR']."', ";
                $actu_pago .= "ERROR_TRANSACC = '".$resultado['Error']."', ";
                $actu_pago .= "TRAZABI_CODIG = '".$transaccion['trazabilityCode']."', RESPUE_CODIG = '".$returnCode."', ";
                $actu_pago .= "INTENTOS = ".$datosPago['numeroIntentos'].", CLACO_NUMERO = NULL, CICLO_TRANSACCION ='".$transaccion['transactionCycle']."', ";
                $actu_pago .= "RAZON_PENDIENTE = '".$transaccion['pendingReason']."' WHERE PASA_NUMERO = ".$valor_llave;

                $this->actualizarRegistroExitoso($actu_pago);
            } else {
                $actu_pago  = "UPDATE GT_PAGO_PASARELA SET EMPRESA = '".$empresa."', NIT = '".$nit."', ";
                $actu_pago .= "VALOR = '".$datosPago['valorTotal']."', IVA = '".$datosPago['iva']."', VALOR_TOTAL = '";
                $actu_pago .= $datosPago['valorTotal']."', MONEDA = '".$currency."', IP_ORIGEN = '".$_SERVER['REMOTE_ADDR']."', ";
                $actu_pago .= "CODIG_TRANSACC = '".$returnCode."', ERROR_TRANSACC = '".$resultado['Error']."', INTENTOS = ";
                $actu_pago .= $datosPago['numeroIntentos'].", CICLO_TRANSACCION = '".$resultado['transactionCycle']."', ";
                $actu_pago .= "CLACO_NUMERO = NULL WHERE PASA_NUMERO = ".$valor_llave;

                $this->actualizarRegistroFallido($actu_pago);
            }

            $error_exepcion = ',FAIL_ENTITYNOTEXISTSORDISABLED,FAIL_BANKNOTEXISTSORDISABLED,FAIL_SERVICENOTEXISTS,FAIL_INVALIDAMOUNT,FAIL_INVALIDSOLICITDATE,FAIL_BANKUNREACHEABLE,FAIL_NOTCONFIRMEDBYBANK,FAIL_CANNOTGETCURRENTCYCLE,FAIL_ACCESSDENIED,FAIL_TIMEOUT,FAIL_DESCRIPTIONNOTFOUND,FAIL_EXCEEDEDLIMIT,FAIL_TRANSACTIONNOTALLOWED,';

            if ( !empty($resultado["Error"])  ){
                return array('transacc' => $resultado["Error"], 'result'=> "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PSE_INTE );

            } elseif ( $returnCode == 'FAIL_EXCEEDEDLIMIT' ){
                return array('transacc' => $resultado, 'result'=> "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PSE_EXCE );

            } elseif ( $returnCode == 'FAIL_BANKUNRECHEABLE' ){
                return array('transacc' => $resultado, 'result'=> "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PSE_UNRE );

            } elseif (  strpos(','.trim($returnCode).',',$error_exepcion) !== false ){
                return array('transacc' => $resultado, 'result'=> "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PSE_INTE );

            } elseif ( $returnCode == 'SUCCESS' ){
                $resultado['transactionResponse']['extraParameters']['BANK_URL'] = $transaccion['bankurl'];
                $resultado['transactionResponse']['state'] = $returnCode;

                return array('transacc' => $resultado, 'result'=> "exito", 'valor_llave' => $valor_llave, 'mensaje' => '' );
            } else{
                return array('transacc' => $resultado, 'result'=> "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PSE_INTE );

            }
        } else {
            return array("error" => true, "mensaje" => "Forma de pago incorrecta. ACH.");
        }
    }

    private function procesarTransaccion(array $datosTransaccion)
    {
        // Peticion XML enviada al servicio de PSE
        $soapxml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/" xmlns:ean="http://www.uc-council.org/smp/schemas/eanucc">
   <soapenv:Header/>
   <soapenv:Body>
      <tem:createTransactionPayment>
         <tem:createTransactionPaymentInformation>
            <ean:financialInstitutionCode>' . $datosTransaccion['PSE_FINANCIAL_INSTITUTION_CODE'] . '</ean:financialInstitutionCode>
            <ean:entityCode>' . $this->ppeCode . '</ean:entityCode>
            <ean:serviceCode>' . $datosTransaccion['PSE_SERVICE_CODE'] . '</ean:serviceCode>
            <ean:transactionValue ean:currencyISOcode="' . $datosTransaccion['PSE_CURRENCY'] . '">' . $datosTransaccion['PSE_TRANSACTION_VALUE'] . '</ean:transactionValue>
            <ean:vatValue ean:currencyISOcode="' . $datosTransaccion['PSE_CURRENCY'] . '">' . $datosTransaccion['PSE_VAT_VALUE'] . '</ean:vatValue>
            <ean:ticketId>' . $datosTransaccion['PSE_TICKET_ID'] . '</ean:ticketId>
            <ean:entityurl>' . $datosTransaccion['PSE_ENTITY_URL'] . '</ean:entityurl>
            <ean:userType>' . $datosTransaccion['PSE_USER_TYPE'] . '</ean:userType>
            <ean:referenceNumber>' . $datosTransaccion['PSE_REFERENCE1'] . '</ean:referenceNumber>
			<ean:referenceNumber>' . $datosTransaccion['PSE_REFERENCE2'] . '</ean:referenceNumber>
			<ean:referenceNumber>' . $datosTransaccion['PSE_REFERENCE3'] . '</ean:referenceNumber>
            <ean:soliciteDate>' . $datosTransaccion['PSE_SOLICITE_DATE'] . '</ean:soliciteDate>
            <ean:paymentDescription>' . $datosTransaccion['PSE_PAYMENT_DESCRIPTION'] . '</ean:paymentDescription>
         </tem:createTransactionPaymentInformation>
      </tem:createTransactionPayment>
   </soapenv:Body>
</soapenv:Envelope>';

        // Se valida la action que va a ejecutar al peticion XML
        if( !empty($this->soapUriAction) )
            $this->soapAction = $this->soapUriAction.'/createTransactionPayment';
        else
            $this->soapAction = $this->soapUriAction;

        // Se ejecuta el SOAP y se obtiene el resultado
        try {
            if( empty($this->faultcode) ){
                $retornoSoap = Soap::callSoapCurlWS($this->pseUrl, $soapxml, $this->soapNameSpaces, $this->soapAction);

                if(is_array($retornoSoap)) {
                    $retorno = $retornoSoap;
                } else if ( !empty($retornoSoap->Body->Fault) || !empty($retornoSoap->body->fault) ) {
                    // Se verifican errores en la respuesta
                    $retorno['Error'] = '"'.$retornoSoap->Body->Fault->faultcode.$retornoSoap->body->fault->faultcode . '  '
                        . $retornoSoap->Body->Fault->faultstring . $retornoSoap->body->fault->faultstring.'"';

                    if( $this->isTest ){
                        // Flujo si está en modo Test
                    }

                    $retorno['Error'] = str_replace("'",'', $retorno['Error']);
                } else {
                    $datosPago = $retornoSoap->Body->createTransactionPaymentResponse;

                    if( !empty ($retornoSoap->Body->createTransactionPaymentResponse->createTransactionPaymentResult) ){
                        $datosPago = $retornoSoap->Body->createTransactionPaymentResponse->createTransactionPaymentResult;

                        $retorno['createTransactionPaymentResponseInformation']['trazabilityCode'] = $datosPago->trazabilityCode.'';
                        $retorno['createTransactionPaymentResponseInformation']['returnCode'] = $datosPago->returnCode.'';
                        $retorno['createTransactionPaymentResponseInformation']['bankurl'] =  $datosPago->bankurl.'';
                        $retorno['createTransactionPaymentResponseInformation']['transactionCycle'] =  $datosPago->transactionCycle.'';
                    } else {
                        $retorno = $datosPago;
                    }
                }
            }else{
                $retorno['Error'] = '"' . $this->faultcode . '  ' . $this->faultstring.'"';
                $retorno['Error'] = str_replace("'",'', $retorno['Error']);
            }
        } catch (SoapFault $fault) {
            $retorno['Error'] = '"'.$fault->faultcode.'  '.$fault->faultstring. "REQUEST". parent::__getLastRequest()."RESPONSE". parent::__getLastResponse().'"';
            $retorno['Error'] = str_replace("'",'', $retorno['Error']);
        }

        return $retorno;
    }

    private function consultarTransaccion($id, $numeroFactura)
    {
        if (empty($id))
            return array('error' => true, "mensaje" => "ID vacio");

        if (empty($numeroFactura))
            return array('error' => true, "mensaje" => "Numero factura vacio");

        $consulta = new Consulta;

        $sql_llave = "SELECT PASA_NUMERO FROM GT_PAGO_PASARELA WHERE ID_TRANSACCION = " . $id . " AND NUMEROFACTURA = '";
        $sql_llave .= $numeroFactura . "' AND ELIMINADO = -1 ORDER BY PASA_NUMERO DESC";

        $consulta->setConsulta($sql_llave);
        $info_trans = $consulta->ejecutarConsulta();

        if ($consulta->numeroFilas() == 0 || $consulta->getResultado() != 2) {
            $consulta->desconectar();
            return array('error' => true, 'mensaje' => MSG_ERROR_PAYU);
        } else {
            $valor_llave = $info_trans[0]['PASA_NUMERO'];
            $consulta->desconectar();
            return array('error' => false, 'valor_llave' => $valor_llave, 'mensaje' => "Correcto");
        }
    }

    private function consultarDatosEmpresa()
    {
        $consulta = new Consulta();

        $select_empr = "SELECT VALOR FROM CP_VARIABLES WITH (NOLOCK) WHERE NOMBRE IN ('VAR_EMPRESA', 'VAR_NIT') ";
        $select_empr .= "AND FECHA_INICIO BETWEEN FECHA_INICIO AND " . $consulta->getLink()->ifNull("FECHA_FIN", "'3000-01-01'");
        $select_empr .= " AND ELIMINADO = -1 AND ACTIVO = '1'";

        $consulta->setConsulta($select_empr);
        $datos_empr_nit = $consulta->ejecutarConsulta();

        if ($consulta->numeroFilas() == 0 || $consulta->getResultado() != 2) {
            $consulta->desconectar();
            return array('error' => true, 'mensaje' => "Error al obtener información de la empresa");
        } else {
            $empresa = $datos_empr_nit[0]['VALOR'];
            $nit = $datos_empr_nit[1]['VALOR'];
            $consulta->desconectar();
            return array('error' => false, 'empresa' => $empresa, 'nit' => $nit, 'mensaje' => "Correcto");
        }
    }

    private function actualizarRegistroExitoso($query)
    {
        $consulta = new Consulta();

        $fechaTransaccion = $consulta->getLink()->DBtimestamp(date('Y-m-d')." ".date("H").":".date("i").":".date("s"));

        str_replace("##FECHA_TRANSACCION##", $fechaTransaccion, $query);

        $consulta->setConsulta($query);

        $consulta->ejecutarConsulta();

        if ($consulta->getResultado() == 2) {
            $consulta->desconectar();
            return array("error" => false, "Mensaje" => "Correcto");
        } else {
            $consulta->desconectar();
            return array("error" => true, "Mensaje" => "Error al actualizar el registro exitoso");
        }
    }

    private function actualizarRegistroFallido($query)
    {
        $consulta = new Consulta();

        $consulta->setConsulta($query);

        $consulta->ejecutarConsulta();

        if ($consulta->getResultado() == 2) {
            $consulta->desconectar();
            return array("error" => false, "Mensaje" => "Correcto");
        } else {
            $consulta->desconectar();
            return array("error" => true, "Mensaje" => "Error al actualizar el registro fallido");
        }
    }
}
<?
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/* +----------------------------------------------------------------------+
 * | PHP version 5.2.5                                                    |
 * +----------------------------------------------------------------------+
 * | Productor (c) 2010 - Infodec S.A.                                    |
 * +----------------------------------------------------------------------+
 * | Authors: Jhon Fredy Marin Cortazar <jhon.marin@infodeclat.com>       |
 * | 		  Juan Fernando Eraso       <juan.eraso@infodeclat.com>       |
 * +----------------------------------------------------------------------+
 * 
 *
 *  Manejo pasarela PayU
 *
 * @package  Sistema
 * @version  1
 * @author   Jhon Fredy Marin <jhon.marin@infodeclat.com>, Juan Fernando Eraso <juan.eraso@infodeclat.com>
 * @since    PHP 5.2.5
 */

/*if ( empty($tarea_programada) ){
    // Mapping
    include(PATH_PASARELA . 'controlador/mappings.php');
    
    // Acciones del Módulo Backend
    require_once PATH_PASARELA . 'controlador/actions/generar_formupayuAction.php';
    require_once PATH_PASARELA . 'controlador/actions/transaccion_payuAction.php';
    require_once PATH_PASARELA . 'controlador/actions/confirmacion_payuAction.php';
    require_once PATH_PASARELA . 'controlador/actions/reintento_payuAction.php';
    require_once PATH_PASARELA . 'controlador/actions/registra_payuAction.php';     
}*/

class PayU extends Pasarela
{

    var $apiKey; //Ingrese aquí su propio apiKey.
    var $apiLogin; //Ingrese aquí su propio apiLogin.
    var $merchantId; //Ingrese aquí su Id de Comercio.
    var $accountId; //Ingrese su Id de cuenta.
    var $apiKey_PSE; //Ingrese aquí su propio apiKey de PSE.
    var $apiLogin_PSE; //Ingrese aquí su propio apiLogin de PSE.
    var $merchantId_PSE; //Ingrese aquí su Id de Comercio de PSE.
    var $language; //Seleccione el idioma.
    var $isTest; //Dejarlo True cuando sean pruebas.
    var $currency = "COP";
    var $paisPago = "CO";
    var $paisComp = "CO";
    var $paisOrde = "CO";
    var $paisPaga = "CO";
    var $urlPayments;

    /**
     * PayU constructor.
     */
    public function __construct()
    {
        $this->apiKey = PAYU_APIKEY; //Ingrese aquí su propio apiKey.
        $this->apiLogin = PAYU_APILOGIN; //Ingrese aquí su propio apiLogin.
        $this->merchantId = PAYU_MERCHANTID; //Ingrese aquí su Id de Comercio.
        $this->apiKey_PSE = PAYU_APIKEY_PSE; //Ingrese aquí su propio apiKey de PSE.
        $this->apiLogin_PSE = PAYU_APILOGIN_PSE; //Ingrese aquí su propio apiLogin de PSE.
        $this->merchantId_PSE = PAYU_MERCHANTID_PSE; //Ingrese aquí su Id de Comercio.
        $this->language = "es";//SupportedLanguages::ES; //Seleccione el idioma.
        $this->isTest = (PAYU_TEST == 1) ? true : false; //Dejarlo True cuando sean pruebas.
        $this->urlPayments = PAYU_URL_PAYMENTS;
        $_SESSION['lang'] = "_ES";
    }

    /**
     * Carga el accountID de PayU. Necesario para domiciliación
     *
     * @return
     */
    function asignaCuenta($cuenta)
    {

        if ($cuenta != "") {
            $this->accountId = $cuenta;
        } else {
            $this->accountId = PAYUDOM_ACCOUNTID; //Ingrese aqui su Id de Cuenta
        }
    }


    /**
     * Carga el accountID de PayU
     *
     * @return
     */
    function setAccount($forma_pago = "", $id_objeto = "", $param_account = "")
    {
        $trace = 'Peticion: ' . PHP_EOL . $forma_pago . PHP_EOL . $id_objeto . PHP_EOL . $param_account;
        $file = fopen("../tmp/setAccount".date('YmdHis').".txt", "w");
        fwrite($file, $trace);
        fclose($file);

        $consulta = new Consulta;

        if (!empty($forma_pago) && !empty($id_objeto)) {
            $queryCuenta = "SELECT VALOR_ES AS CUENTA FROM GT_VALORES WHERE LIST_NUMERO = 42 AND CODIGO = '";
            $queryCuenta .= $forma_pago . $id_objeto . $param_account . "' AND ACTIVO = '1' AND ELIMINADO = -1 ";

            $consulta->setConsulta($queryCuenta);
            $cuenta = $consulta->ejecutarConsulta();
        }

        #Se asigna la cuenta consultada en la Base de Datos o la configuración del archivo config.var
        if ($consulta->numeroFilas() > 0) {
            $this->accountId = $cuenta[0]['CUENTA'];
        } else {
            $this->accountId = PAYU_ACCOUNTID;
        }

    }


    /**
     * Ping al sistema de PayU
     *
     * @return array/boolean  Retorna la respuesta de conectividad con PayU.
     */
    function payuPing()
    {

        $datos_ping = array("language" => $this->language, "command" => 'PING',
            "merchant" => array("apiLogin" => $this->apiLogin, "apiKey" => $this->apiKey),
            "test" => $this->isTest);

        return $this->peticionPost($datos_ping, PAYU_URL_PAYMENTS);

    }


    /**
     * Metodos de pago al sistema de PayU
     *
     * @return array/boolean  Retorna las formas de pago de PayU con tarjeta de crédito
     */
    function payuMetodos()
    {

        $datos_ping = array("language" => $this->language, "command" => 'GET_PAYMENT_METHODS',
            "merchant" => array("apiLogin" => $this->apiLogin, "apiKey" => $this->apiKey),
            "test" => $this->isTest);

        return $this->peticionPost($datos_ping, PAYU_URL_PAYMENTS);

    }

    /**
     * Metodos de pago al sistema de PayU para domiciliación
     *
     * @return array/boolean  Retorna las formas de pago de PayU con tarjeta de crédito
     */
    function payuMetodosDomicilia()
    {

        $isTestDom = (PAYUDOM_TEST == 1) ? true : false;

        $datos_ping = array("language" => $this->language, "command" => 'GET_PAYMENT_METHODS',
            "merchant" => array("apiLogin" => PAYUDOM_APILOGIN, "apiKey" => PAYUDOM_APIKEY),
            "test" => $isTestDom);

        return $this->peticionPost($datos_ping, PAYUDOM_URL_PAYMENTS);

    }


    /**
     * Bancos de pago al sistema de PayU
     * @param  string $metodo Metodo para capturar la lista de bancos
     * @param  string $pais Pais donde se liste los bancos
     * @return array/boolean  Retorna los bancos de PayU para pago con tarjeta débito
     */
    function payuBancos($metodo, $pais)
    {

        $datos_bancos = array("language" => $this->language, "command" => 'GET_BANKS_LIST',
            "merchant" => array("apiLogin" => $this->apiLogin_PSE, "apiKey" => $this->apiKey_PSE),
            "test" => $this->isTest, "bankListInformation" => array("paymentMethod" => $metodo,
                "paymentCountry" => $pais));

        return $this->peticionPost($datos_bancos, PAYU_URL_PAYMENTS);

    }


    /**
     * Consulta por order id
     *
     * @param  array $valores Datos de consulta por orden
     * @return array/boolean  Retorna la respuesta el estado de PayU
     */
    function payuConsultaOrder($valores)
    {

        $this->isTest = (PAYU_TEST == 1) ? "true" : "false";

        if ($valores['FORMA_PAGO'] == 2 || $valores['FORMA_PAGO'] == 4 || $valores['FORMA_PAGO'] == 5 || $valores['FORMA_PAGO'] == 6) {
            $datos_ordenes = '{"language":"' . $this->language . '", "command":"ORDER_DETAIL", ';
            $datos_ordenes .= '"merchant":{"apiLogin":"' . $this->apiLogin . '","apiKey":"' . $this->apiKey . '"}, ';
            $datos_ordenes .= '"test":' . $this->isTest . ', "details":{"orderId":' . $valores['ID_ORDEN'] . '}}';

        } else {
            $datos_ordenes = '{"language":"' . $this->language . '", "command":"ORDER_DETAIL", ';
            $datos_ordenes .= '"merchant":{"apiLogin":"' . $this->apiLogin_PSE . '","apiKey":"' . $this->apiKey_PSE . '"}, ';
            $datos_ordenes .= '"test":' . $this->isTest . ', "details":{"orderId":' . $valores['ID_ORDEN'] . '}}';

        }

        if ($valores['debug'] == '1') {
            echo $datos_ordenes;
        }

        return $this->peticionPost($datos_ordenes, PAYU_URL_REPORTS, "1");

    }


    /**
     * Consulta por referencia
     *
     * @param  array $valores Datos de consulta por referencia
     * @return array/boolean  Retorna la respuesta el estado de PayU
     */
    function payuConsultaRefer($valores)
    {

        $this->isTest = (PAYU_TEST == 1) ? "true" : "false";

        if ($valores['FORMA_PAGO'] == 2 || $valores['FORMA_PAGO'] == 4 || $valores['FORMA_PAGO'] == 5 || $valores['FORMA_PAGO'] == 6) {
            $datos_ordenes = '{"language":"' . $this->language . '", "command":"ORDER_DETAIL_BY_REFERENCE_CODE", ';
            $datos_ordenes .= '"merchant":{"apiLogin":"' . $this->apiLogin . '","apiKey":"' . $this->apiKey . '"}, ';
            $datos_ordenes .= '"test":' . $this->isTest . ', "details":{"referenceCode":' . $valores['NUMEROFACTURA'] . '}}';

        } else {
            $datos_ordenes = '{"language":"' . $this->language . '", "command":"ORDER_DETAIL_BY_REFERENCE_CODE", ';
            $datos_ordenes .= '"merchant":{"apiLogin":"' . $this->apiLogin_PSE . '","apiKey":"' . $this->apiKey_PSE . '"}, ';
            $datos_ordenes .= '"test":' . $this->isTest . ', "details":{"referenceCode":' . $valores['NUMEROFACTURA'] . '}}';

        }

        return $this->peticionPost($datos_ordenes, PAYU_URL_REPORTS, "1");

    }


    /**
     * Generar pago a PayU
     *
     * @param  array $valores Datos del formulario para transaccion
     * @return array/boolean  Retorna los bancos de PayU para pago con tarjeta débito
     */
    /*function payuTransaccion($valores)
    {

        set_time_limit(0);

        $consulta = new Consulta;

        $sql_llave = "SELECT PASA_NUMERO FROM GT_PAGO_PASARELA WHERE ID_TRANSACCION = " . $valores['ID_TRANSACCION'] . " AND NUMEROFACTURA = '" . $valores['NUMEROFACTURA'] . "' ";
        $sql_llave .= "AND ELIMINADO = -1 ORDER BY PASA_NUMERO DESC";

        $consulta->setConsulta($sql_llave);
        $info_trans = $consulta->ejecutarConsulta();
        $valor_llave = $info_trans[0]['PASA_NUMERO'];

        if ($consulta->numeroFilas() == 0 || $consulta->getResultado() != 2 || empty($valor_llave) || empty($valores['VALOR_TOTAL']) || $valores['registroPago'] == "fracaso") {
            $consulta->desconectar();
            return array('transacc' => $resultado, 'result' => "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PAYU);
        }

        $valorTotal_Base = "";
        if ($valores['ID_TRANSACCION'] != "" && PAYU_VALOR != "") {

            list($clase, $metodo) = explode("::", PAYU_VALOR);
            $infoValor = call_user_func(array($clase, $metodo), $valores);

            if ($infoValor['VALORTOTAL'] != "") {
                $valorTotal_Base = $infoValor['VALORTOTAL'];
            }
        }

        if (!empty($_SESSION['ValorTotal']) && empty($valorTotal_Base)) {
            $valores['VALOR_TOTAL'] = $_SESSION['ValorTotal'];

        } elseif (!empty($valorTotal_Base)) {
            $valores['VALOR_TOTAL'] = $valorTotal_Base;

        } else {
            return array('transacc' => 'Error valor pago', 'result' => "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PAYU);
        }

        if ($valores['IVA'] == "") $valores['IVA'] = 0;
        if ($valores['VALOR'] == "" || $valores['VALOR'] == 0) $valores['VALOR'] = $valores['VALOR_TOTAL'];

        $valores['IVA'] = trim(str_replace("$", "", $valores['IVA']));
        $valores['IVA'] = trim(str_replace(".", "", $valores['IVA']));
        $valores['IVA'] = trim(str_replace(",", ".", $valores['IVA']));

        if (empty($valorTotal_Base)) {
            $valores['VALOR'] = trim(str_replace("$", "", $valores['VALOR']));
            $valores['VALOR'] = trim(str_replace(".", "", $valores['VALOR']));
            $valores['VALOR'] = trim(str_replace(",", ".", $valores['VALOR']));

            $valores['VALOR_TOTAL'] = trim(str_replace("$", "", $valores['VALOR_TOTAL']));
            $valores['VALOR_TOTAL'] = trim(str_replace(".", "", $valores['VALOR_TOTAL']));
            $valores['VALOR_TOTAL'] = trim(str_replace(",", ".", $valores['VALOR_TOTAL']));
        }

        $valores['EMAIL'] = trim($valores['EMAIL']);

        $currency = (empty($valores['MONEDA'])) ? $this->currency : $valores['MONEDA'];

        $select_empr = "SELECT VALOR FROM CP_VARIABLES WHERE NOMBRE = 'VAR_EMPRESA' AND FECHA_INICIO BETWEEN FECHA_INICIO AND ";
        $select_empr .= $consulta->getLink()->ifNull("FECHA_FIN", "'3000-01-01'") . " AND ELIMINADO = -1 AND ACTIVO = '1'";
        $consulta->setConsulta($select_empr);
        $datos_empr = $consulta->ejecutarConsulta();

        $select_nit = "SELECT VALOR FROM CP_VARIABLES WHERE NOMBRE = 'VAR_NIT' AND FECHA_INICIO BETWEEN FECHA_INICIO AND ";
        $select_nit .= $consulta->getLink()->ifNull("FECHA_FIN", "'3000-01-01'") . " AND ELIMINADO = -1 AND ACTIVO = '1'";
        $consulta->setConsulta($select_nit);
        $datos_nit = $consulta->ejecutarConsulta();

        //$select_cus  = "SELECT VALOR FROM CP_VARIABLES WHERE NOMBRE = 'VAR_CUS' AND FECHA_INICIO BETWEEN FECHA_INICIO AND ";
        //$select_cus .= $link->ifNull( "FECHA_FIN", "'3000-01-01'" )." AND ELIMINADO = -1 AND ACTIVO = '1'";
        //$consulta->setConsulta($select_cus);
        //$datos_cus = $consulta->ejecutarConsulta();

        $empresa = $_SESSION['msg_pasa_empresa'];
        $nit = $_SESSION['msg_pasa_nit'];

        if (empty($empresa))
            $empresa = $datos_empr[0]['VALOR'];

        if (empty($nit))
            $nit = $datos_nit[0]['VALOR'];

        $valores['PAIS'] = (empty($valores['PAIS'])) ? $this->paisPago : $valores['PAIS'];
        $valores['COMP_DIRPAIS'] = (empty($valores['COMP_DIRPAIS'])) ? $this->paisComp : $valores['COMP_DIRPAIS'];
        $valores['ORDE_DIRPAIS'] = (empty($valores['ORDE_DIRPAIS'])) ? $this->paisOrde : $valores['ORDE_DIRPAIS'];
        $valores['PAGA_DIRPAIS'] = (empty($valores['PAGA_DIRPAIS'])) ? $this->paisPaga : $valores['PAGA_DIRPAIS'];


        if ($valores['FORMA_PAGO'] == 2 || $valores['FORMA_PAGO'] == 6) {

            $stringToHash = $this->apiKey . "~" . $this->merchantId . "~" . trim($valores['NUMEROFACTURA'] . "_" . $valores['INTENTOS']) . "~" . trim($valores['VALOR_TOTAL']) . "~" . $currency;
            $signature = md5($stringToHash);

            $datos_trans = array('language' => $this->language,
                'command' => 'SUBMIT_TRANSACTION',
                'merchant' => array('apiKey' => $this->apiKey,
                    'apiLogin' => $this->apiLogin),
                'transaction' => array('order' => array('accountId' => $this->accountId,
                    'referenceCode' => $valores['NUMEROFACTURA'] . "_" . $valores['INTENTOS'],
                    'description' => $valores['DESCRIPCION_COMPRA'],
                    'language' => $this->language,
                    'signature' => $signature,
                    'notifyUrl' => '',
                    'additionalValues' => array('TX_VALUE' => array('value' => $valores['VALOR_TOTAL'],
                        'currency' => $currency
                    ),
                        'TX_TAX' => array('value' => 0,
                            'currency' => $currency
                        ),
                        'TX_TAX_RETURN_BASE' => array('value' => 0,
                            'currency' => $currency
                        )
                    ),
                    'buyer' => array('fullName' => $valores['COMP_NOMBRE'],
                        'emailAddress' => $valores['COMP_EMAIL'],
                        'contactPhone' => $valores['COMP_TELEFONO'],
                        'dniNumber' => $valores['COMP_IDENTIF'],
                        'shippingAddress' => array('street1' => $valores['COMP_DIRCALLE1'],
                            'street2' => $valores['COMP_DIRCALLE2'],
                            'city' => $valores['COMP_DIRCIUDAD'],
                            'state' => $valores['COMP_DIRESTADO'],
                            'country' => $valores['COMP_DIRPAIS'],
                            'postalCode' => $valores['COMP_DIRPOSTAL'],
                            'phone' => $valores['COMP_DIRTELEFO']
                        )
                    ),
                    'shippingAddress' => array('street1' => $valores['ORDE_DIRCALLE1'],
                        'street2' => $valores['ORDE_DIRCALLE2'],
                        'city' => $valores['ORDE_DIRCIUDAD'],
                        'state' => $valores['ORDE_DIRESTADO'],
                        'country' => $valores['ORDE_DIRPAIS'],
                        'postalCode' => $valores['ORDE_DIRPOSTAL'],
                        'phone' => $valores['ORDE_DIRTELEFO']
                    )
                ),
                    'payer' => array('fullName' => $valores['NOMBRE_TARJETA'],
                        'emailAddress' => $valores['EMAIL'],
                        'contactPhone' => $valores['TELEFONO'],
                        'dniNumber' => $valores['NUMERO_DOCUMENTO'],
                        'billingAddress' => array('street1' => $valores['PAGA_DIRCALLE1'],
                            'street2' => $valores['PAGA_DIRCALLE2'],
                            'city' => $valores['PAGA_DIRCIUDAD'],
                            'state' => $valores['PAGA_DIRESTADO'],
                            'country' => $valores['PAGA_DIRPAIS'],
                            'postalCode' => $valores['PAGA_DIRPOSTAL'],
                            'phone' => $valores['PAGA_DIRTELEFO']
                        )
                    ),
                    'creditCard' => array('number' => $valores['NUMERO_TARJETA'],
                        'securityCode' => $valores['CODIGO_SEGURIDAD'],
                        'expirationDate' => $valores['FECHA_VENC'],
                        'name' => $valores['NOMBRE_TARJETA']),
                    'extraParameters' => array('INSTALLMENTS_NUMBER' => $valores['CUOTAS']),
                    'type' => 'AUTHORIZATION_AND_CAPTURE',
                    'paymentMethod' => $valores['TARJETA_CREDITO'],
                    'paymentCountry' => $valores['PAIS'],
                    'deviceSessionId' => $_SESSION['id_session'],
                    'ipAddress' => $valores['IP_ORIGEN'],
                    'cookie' => session_id(),
                    'userAgent' => $_SERVER['HTTP_USER_AGENT']
                ),
                'test' => $this->isTest);
            //AGREGAR CAMPO dniType (CC, CE, NIT, TI, PP, IDC, CEL, RC, DE)
            //CODENSA
            if ($valores['FORMA_PAGO'] == 6 || $valores['TARJETA_CREDITO'] == 'CODENSA') {
                $datos_trans['transaction']['payer']['dniType'] = $valores['TIPO_DOCUMENTO'];
            }

        } else {

            $stringToHash = $this->apiKey_PSE . "~" . $this->merchantId_PSE . "~" . trim($valores['NUMEROFACTURA'] . "_" . $valores['INTENTOS']) . "~" . trim($valores['VALOR_TOTAL']) . "~" . $currency;
            $signature = md5($stringToHash);

            $url_confirma = "&valor_llave=" . $valor_llave . "&pasa_personal=" . $valores['PASA_PERSONAL'];
            $url_confirma .= "&pasa_confirm=" . OBJE_CONFIRM . "&empresa=" . $_SESSION['usrio_empr'] . "&clase_confirm=" . $valores['clase_confirm'];
            $url_confirma .= "&metod_confirm=" . $valores['metod_confirm'] . "&obje_cancel=" . $valores['obje_cancel'] . "&FORMA_PAGO=" . $valores['FORMA_PAGO'];

            if ($valores['redir_retorno'] != '') {
                $url_retorno = NOMBRE_HOST . "phrame.php?action=despliegue_personal&clase=" . $valores['clase_retorno'] . "&metodo=" . $valores['metod_retorno'] . $url_confirma;
            } else {
                $url_retorno = NOMBRE_HOST . "phrame.php?action=confirma_payu" . $url_confirma;
            }

            $datos_trans = array('language' => $this->language,
                'command' => 'SUBMIT_TRANSACTION',
                'merchant' => Array('apiKey' => $this->apiKey_PSE,
                    'apiLogin' => $this->apiLogin_PSE),
                'transaction' => Array('order' => Array('accountId' => $this->accountId,
                    'referenceCode' => $valores['NUMEROFACTURA'] . "_" . $valores['INTENTOS'],
                    'description' => $valores['DESCRIPCION_COMPRA'],
                    'language' => $this->language,
                    'signature' => $signature,
                    'notifyUrl' => NOMBRE_HOST . "phrame.php?action=registra_payu" . $url_confirma,
                    'additionalValues' => Array('TX_VALUE' => Array('value' => $valores['VALOR_TOTAL'],
                        'currency' => $currency
                    ),
                        'TX_TAX' => array('value' => 0,
                            'currency' => $currency
                        ),
                        'TX_TAX_RETURN_BASE' => array('value' => 0,
                            'currency' => $currency
                        )
                    ),
                    'buyer' => Array('emailAddress' => $valores['COMP_EMAIL'],
                    )
                ),
                    'payer' => Array('fullName' => $valores['TITULAR'],
                        'emailAddress' => $valores['EMAIL'],
                        'contactPhone' => $valores['TELEFONO']
                    ),
                    "extraParameters" => Array('RESPONSE_URL' => $url_retorno,
                        'PSE_REFERENCE1' => $valores['IP_ORIGEN'],
                        'FINANCIAL_INSTITUTION_CODE' => $valores['BANCO'],
                        'USER_TYPE' => $valores['TIPO_CLIENTE'],
                        'PSE_REFERENCE2' => $valores['TIPO_DOCUMENTO'],
                        'PSE_REFERENCE3' => $valores['NUMERO_DOCUMENTO']
                    ),
                    'type' => 'AUTHORIZATION_AND_CAPTURE',
                    'paymentMethod' => "PSE",
                    'paymentCountry' => $valores['PAIS'],
                    'ipAddress' => $valores['IP_ORIGEN'],
                    'cookie' => session_id(),
                    'userAgent' => $_SERVER['HTTP_USER_AGENT']
                ),
                'test' => false
            );
        }

        $valores['NUMERO_TARJETA'] = str_pad('', strlen($valores['NUMERO_TARJETA']) - 4, "*", STR_PAD_LEFT) . substr($valores['NUMERO_TARJETA'], -4);

        $inicio_pago = "UPDATE GT_PAGO_PASARELA SET EMPRESA = '" . $empresa . "', NIT = '" . $nit . "', ";
        $inicio_pago .= "VALOR = '" . $valores['VALOR'] . "', IVA = '" . $valores['IVA'] . "', VALOR_TOTAL = '" . $valores['VALOR_TOTAL'] . "', MONEDA = '" . $currency . "', IP_ORIGEN = '" . $valores['IP_ORIGEN'] . "', ";
        $inicio_pago .= "INTENTOS = " . $valores['INTENTOS'] . ", NUMERO_TARJETA = '" . $valores['NUMERO_TARJETA'] . "', CLACO_NUMERO = 1 ";
        $inicio_pago .= "WHERE PASA_NUMERO = " . $valor_llave;

        $consulta->setConsulta($inicio_pago);
        $consulta->ejecutarConsulta("noshow");

        $resultado = $this->peticionPost($datos_trans, PAYU_URL_PAYMENTS);

        $trace = 'solicitud:' . PHP_EOL . json_encode($datos_trans) . PHP_EOL . 'respuesta:' . PHP_EOL . json_encode($resultado);
        $file = fopen("tmp/payuTransacc" . $valor_llave . "_" . date('YmdHis') . ".txt", "w");
        fwrite($file, $trace);
        fclose($file);

        // Porcesar el resultado        
        if ($resultado['code'] == "SUCCESS") {

            $transaccion = $resultado['transactionResponse'];

            $actu_pago = "UPDATE GT_PAGO_PASARELA SET EMPRESA = '" . $empresa . "', NIT = '" . $nit . "', ";
            $actu_pago .= "FECHA_TRANSACCION = " . Patron::validarVacios(4, date('Y-m-d')) . ", ESTADO = '" . $resultado['transactionResponse']['state'] . "', ";
            $actu_pago .= "ID_ORDEN = '" . $transaccion['orderId'] . "', CUS = '" . $transaccion['trazabilityCode'] . "', ";
            $actu_pago .= "VALOR = '" . $valores['VALOR'] . "', IVA = '" . $valores['IVA'] . "', VALOR_TOTAL = '" . $valores['VALOR_TOTAL'] . "', MONEDA = '" . $currency . "', IP_ORIGEN = '" . $valores['IP_ORIGEN'] . "', ";
            $actu_pago .= "CODIG_TRANSACC = '" . $resultado['code'] . "', ERROR_TRANSACC = '" . $resultado['error'] . "', ID_TRANSACC = '";
            $actu_pago .= $transaccion['transactionId'] . "', RED_RESPUE_CODIG = '" . $transaccion['paymentNetworkResponseCode'] . "', ";
            $actu_pago .= "RED_RESPUE_MENSA = '" . $transaccion['paymentNetworkResponseErrorMessage'] . "', TRAZABI_CODIG = '" . $transaccion['trazabilityCode'] . "', ";
            $actu_pago .= "AUTORIZ_CODIG = '" . $transaccion['authorizationCode'] . "', RESPUE_CODIG = '" . $transaccion['responseCode'] . "', ";
            $actu_pago .= "ERROR_CODIG = '" . $transaccion['errorCode'] . "', ERROR_MENSA = '" . $transaccion['responseMessage'] . "', ";
            $actu_pago .= "FECHA_OPERACION = '" . $transaccion['operationDate'] . "', INTENTOS = " . $valores['INTENTOS'] . ", CLACO_NUMERO = NULL, ";
            $actu_pago .= "RAZON_PENDIENTE = '" . $transaccion['pendingReason'] . "', NUMERO_TARJETA = '" . $valores['NUMERO_TARJETA'] . "' WHERE PASA_NUMERO = " . $valor_llave;

        } elseif ($resultado['code'] == "ERROR") {

            $actu_pago = "UPDATE GT_PAGO_PASARELA SET EMPRESA = '" . $empresa . "', NIT = '" . $nit . "', ESTADO = '" . $resultado['code'] . "', ";
            $actu_pago .= "VALOR = '" . $valores['VALOR'] . "', IVA = '" . $valores['IVA'] . "', VALOR_TOTAL = '" . $valores['VALOR_TOTAL'] . "', MONEDA = '" . $currency . "', IP_ORIGEN = '" . $valores['IP_ORIGEN'] . "', ";
            $actu_pago .= "CODIG_TRANSACC = '" . $resultado['code'] . "', ERROR_TRANSACC = '" . $resultado['error'] . "', INTENTOS = " . $valores['INTENTOS'] . ",  ";
            $actu_pago .= "NUMERO_TARJETA = '" . $valores['NUMERO_TARJETA'] . "', CLACO_NUMERO = NULL WHERE PASA_NUMERO = " . $valor_llave;

        } else {

            $actu_pago = "UPDATE GT_PAGO_PASARELA SET EMPRESA = '" . $empresa . "', NIT = '" . $nit . "', ";
            $actu_pago .= "VALOR = '" . $valores['VALOR'] . "', IVA = '" . $valores['IVA'] . "', VALOR_TOTAL = '" . $valores['VALOR_TOTAL'] . "', MONEDA = '" . $currency . "', IP_ORIGEN = '" . $valores['IP_ORIGEN'] . "', ";
            $actu_pago .= "CODIG_TRANSACC = 'ERROR - " . $resultado['code'] . "', ERROR_TRANSACC = 'No responde - " . $resultado['error'] . "', INTENTOS = " . $valores['INTENTOS'] . ",  ";
            $actu_pago .= "NUMERO_TARJETA = '" . $valores['NUMERO_TARJETA'] . "', CLACO_NUMERO = NULL WHERE PASA_NUMERO = " . $valor_llave;

        }


        if ($resultado['transactionResponse']['state'] == 'SUBMITTED') {
            $file = fopen("tmp/TransaPayU_" . $valor_llave . "_" . date('YmdHis') . ".txt", "w");
            fwrite($file, $actu_pago);
            fclose($file);
        }

        $consulta->setConsulta($actu_pago);
        $consulta->ejecutarConsulta("noshow");

        $consulta->desconectar();

        $url = $resultado['transacc']['transactionResponse']['extraParameters']['BANK_URL'];

        if ($resultado == "Error") {
            return array('transacc' => $resultado, 'result' => "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PAYU);

        } elseif ($resultado['code'] == "ERROR" && ($valores['FORMA_PAGO'] == 2 || ['FORMA_PAGO'] == 6)) { //CODENSA
            return array('transacc' => $resultado, 'result' => "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PAYU . "<br><br>" . utf8_decode($resultado['error']));

        } elseif ($resultado['code'] == "ERROR" && $valores['FORMA_PAGO'] == 1 && $transaccion['responseCode'] == 'EXCEEDED_AMOUNT') {
            return array('transacc' => $resultado, 'result' => "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_EXCE);

        } elseif ($resultado['code'] == "ERROR" && $valores['FORMA_PAGO'] == 1 && $transaccion['responseCode'] == 'BANK_UNREACHABLE') {
            return array('transacc' => $resultado, 'result' => "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_UNRE);

        } elseif ($resultado['code'] == "ERROR" && $valores['FORMA_PAGO'] == 1 && $transaccion['responseCode'] == 'INTERNAL_PAYMENT_PROVIDER_ERROR') {
            return array('transacc' => $resultado, 'result' => "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_INTE);

        } elseif ($resultado['code'] == "ERROR" && $valores['FORMA_PAGO'] == 1 && ($transaccion['responseCode'] == '')) {
            return array('transacc' => $resultado, 'result' => "error", 'valor_llave' => $valor_llave, 'mensaje' => utf8_decode($resultado['error']));

        } elseif ($resultado['code'] == "ERROR" && $valores['FORMA_PAGO'] == 1 && empty($url)) {
            return array('transacc' => $resultado, 'result' => "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_INTE);


        } else {
            return array('transacc' => $resultado, 'result' => "exito", 'valor_llave' => $valor_llave, 'mensaje' => '');

        }

    }*/

    function payuTransaccion($datosPago, $datosMedioPago)
    {
        set_time_limit(0);

        $consulta = new Consulta;

        /*$sql_llave = "SELECT PASA_NUMERO FROM GT_PAGO_PASARELA WHERE ID_TRANSACCION = " . $datosPago["IDTransaccion"];
        $sql_llave .= " AND NUMEROFACTURA = '" . $datosPago["numeroFactura"] . "' ";
        $sql_llave .= "AND ELIMINADO = -1 ORDER BY PASA_NUMERO DESC";*/

        $sql_llave  = "SELECT G.PASA_NUMERO, C.TIPO_TRANS, C.TIPO_TRANS_ORIG, C.CodigoCliente, C.NRO_CUENTA FROM ";
        $sql_llave .= "GT_PAGO_PASARELA G INNER JOIN CL_PAGOSCLARO C ON G.ID_TRANSACCION = C.CLACO_NUMERO WHERE ";
        $sql_llave .= "G.ID_TRANSACCION = " . $datosPago['IDTransaccion'] . " AND G.NUMEROFACTURA = '";
        $sql_llave .= $datosPago['numeroFactura'] . "' AND G.ELIMINADO = -1 ORDER BY G.PASA_NUMERO DESC";

        $consulta->setConsulta($sql_llave);
        $info_trans = $consulta->ejecutarConsulta();
        $valor_llave = $info_trans[0]['PASA_NUMERO'];

        if ($consulta->numeroFilas() == 0 || $consulta->getResultado() != 2 || empty($valor_llave)
            || empty($datosPago["valorTotal"]) || $datosPago['registroPago'] == "fracaso") {
            $consulta->desconectar();
            return array('error' => true, 'result' => "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PAYU);
        }

        // Inicio Asignación valor informativo de la referencia de pago dentro de la consulta
        if ($info_trans[0]['TIPO_TRANS'] == 3) {
            //Si es servicio fijo se envía el #cuenta
            $referenciaPago = $info_trans[0]['NRO_CUENTA'];
            //Se crea variable de sesión para mostrar la referencia de pago en el resumen de la transacción
            $_SESSION["REFERENCIA_PAGO_VISTA_RESUMEN"] = null;
            $_SESSION["REFERENCIA_PAGO_VISTA_RESUMEN"] = $info_trans[0]['NRO_CUENTA'];
        } elseif ($info_trans[0]['TIPO_TRANS'] == 2) {
            //Si es servicio móvil se envía la referencia de pago
            $referenciaPago = $info_trans[0]['CodigoCliente'];
            //Se crea variable de sesión para mostrar la referencia de pago en el resumen de la transacción
            $_SESSION["REFERENCIA_PAGO_VISTA_RESUMEN"] = null;
            $_SESSION["REFERENCIA_PAGO_VISTA_RESUMEN"] = $info_trans[0]['CodigoCliente'];;
        } else {
            $referenciaPago = '';
        }
        // Fin Asignación valor informativo de la referencia de pago dentro de la consulta

        $valorTotal_Base = $datosPago["valorTotal"];
        // Se llama a la funcion consultavalor, pero no es necesario en APP
        /*if ($pago->getIDTransaccion() != "" && PAYU_VALOR != "") {

            list($clase, $metodo) = explode("::", PAYU_VALOR);
            $infoValor = call_user_func(array($clase, $metodo), $valores);

            if ($infoValor['VALORTOTAL'] != "") {
                $valorTotal_Base = $infoValor['VALORTOTAL'];
            }
        }*/

        if (empty($valorTotal_Base)) {
            return array('error' => true, 'result' => "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PAYU . ' Error valor pago.');
        }

        if ($datosPago["iva"] == "") $datosPago["iva"] = 0;
        //if ($valores['VALOR'] == "" || $valores['VALOR'] == 0) $valores['VALOR'] = $valores['VALOR_TOTAL'];

        //Se quitan los espacios en blanco que puedan haber en los valores

        $datosPago["valorTotal"] = trim($datosPago["valorTotal"]);
        $datosPago["iva"] = trim($datosPago["iva"]);
        $datosMedioPago["email"] = trim($datosMedioPago["email"]);

        /*$valores['IVA'] = trim(str_replace("$", "", $valores['IVA']));
        $valores['IVA'] = trim(str_replace(".", "", $valores['IVA']));
        $valores['IVA'] = trim(str_replace(",", ".", $valores['IVA']));

        if (empty($valorTotal_Base)) {
            $valores['VALOR'] = trim(str_replace("$", "", $valores['VALOR']));
            $valores['VALOR'] = trim(str_replace(".", "", $valores['VALOR']));
            $valores['VALOR'] = trim(str_replace(",", ".", $valores['VALOR']));

            $valores['VALOR_TOTAL'] = trim(str_replace("$", "", $valores['VALOR_TOTAL']));
            $valores['VALOR_TOTAL'] = trim(str_replace(".", "", $valores['VALOR_TOTAL']));
            $valores['VALOR_TOTAL'] = trim(str_replace(",", ".", $valores['VALOR_TOTAL']));
        }

        $valores['EMAIL'] = trim($valores['EMAIL']);*/

        $currency = (empty($datosPago["moneda"])) ? $this->currency : $datosPago["moneda"];

        # Inicio asignación nombre y NIT de la empresa que recibe el pago Comcel S.A o Telmex Colombia S.A
        if ($info_trans[0]['TIPO_TRANS'] == 3) {
            $variablesEmpresa = "'VAR_EMPRESAFIJ', 'VAR_NITFIJ'";
        } else {
            $variablesEmpresa = "'VAR_EMPRESA', 'VAR_NIT'";
        }

        $queryEmpresa = "SELECT VALOR FROM CP_VARIABLES WITH (NOLOCK) WHERE NOMBRE IN (" . $variablesEmpresa . ") ";
        $queryEmpresa .= "AND FECHA_INICIO BETWEEN FECHA_INICIO AND " . $consulta->getLink()->ifNull("FECHA_FIN", "'3000-01-01'");
        $queryEmpresa .= " AND ELIMINADO = -1 AND ACTIVO = '1'";

        $consulta->setConsulta($queryEmpresa);
        $datosEmpresa = $consulta->ejecutarConsulta();

        $empresa = $datosEmpresa[0]['VALOR'];
        $nit = $datosEmpresa[1]['VALOR'];
        # Fin asignación nombre y NIT de la empresa que recibe el pago Comcel S.A o Telmex Colombia S.A

        $valores['PAIS'] = $this->paisPago;
        $valores['COMP_DIRPAIS'] = $this->paisComp;
        $valores['ORDE_DIRPAIS'] = $this->paisOrde;
        $valores['PAGA_DIRPAIS'] = $this->paisPaga;

        # Se construye el signature que se envia en la petición de pago, para Crédito y PayUTeFía
        $stringToHash = $this->apiKey . "~" . $this->merchantId . "~" . trim($datosPago["numeroFactura"] . "_"
                . $datosPago["numeroIntentos"]) . "~" . trim($datosPago["valorTotal"]) . "~" . $currency;
        $signature = md5($stringToHash);

        if ($datosMedioPago["formaPago"] == 2 || $datosMedioPago["formaPago"] == 6) {

            $datos_trans = array('language' => $this->language,
                'command' => 'SUBMIT_TRANSACTION',
                'merchant' => array('apiKey' => $this->apiKey,
                    'apiLogin' => $this->apiLogin),
                'transaction' => array('order' => array('accountId' => $this->accountId,
                    'referenceCode' => $datosPago["numeroFactura"] . "_" . $datosPago["numeroIntentos"],
                    'description' => $datosPago["descripcion"],
                    'language' => $this->language,
                    'signature' => $signature,
                    'notifyUrl' => '',
                    'additionalValues' => array('TX_VALUE' => array('value' => $datosPago["valorTotal"],
                        'currency' => $currency
                    ),
                        'TX_TAX' => array('value' => 0,
                            'currency' => $currency
                        ),
                        'TX_TAX_RETURN_BASE' => array('value' => 0,
                            'currency' => $currency
                        )
                    ),
                    'buyer' => array('fullName' => '',//$datosPago['COMP_NOMBRE'],
                        'emailAddress' => '',//$datosPago['COMP_EMAIL'],
                        'contactPhone' => '',//$datosPago['COMP_TELEFONO'],
                        'dniNumber' => '',//$datosPago['COMP_IDENTIF'],
                        'shippingAddress' => array('street1' => '',//$datosPago['COMP_DIRCALLE1'],
                            'street2' => '',//$datosPago['COMP_DIRCALLE2'],
                            'city' => '',//$datosPago['COMP_DIRCIUDAD'],
                            'state' => '',//$datosPago['COMP_DIRESTADO'],
                            'country' => $valores['COMP_DIRPAIS'],
                            'postalCode' => '',//$datosPago['COMP_DIRPOSTAL'],
                            'phone' => '',//$datosPago['COMP_DIRTELEFO']
                        )
                    ),
                    'shippingAddress' => array('street1' => '',//$datosPago['ORDE_DIRCALLE1'],
                        'street2' => '',//$datosPago['ORDE_DIRCALLE2'],
                        'city' => '',//$datosPago['ORDE_DIRCIUDAD'],
                        'state' => '',//$datosPago['ORDE_DIRESTADO'],
                        'country' => $valores['ORDE_DIRPAIS'],
                        'postalCode' => '',//$datosPago['ORDE_DIRPOSTAL'],
                        'phone' => '',//$datosPago['ORDE_DIRTELEFO']
                    )
                ),
                    'payer' => array('fullName' => $datosMedioPago["nombre"],//,$valores['NOMBRE_TARJETA'],
                        'emailAddress' => $datosMedioPago["email"],
                        'contactPhone' => $datosMedioPago["telefono"],
                        'dniNumber' => $datosMedioPago["numeroDocumento"],
                        'billingAddress' => array('street1' => '',//$datosPago['PAGA_DIRCALLE1'],
                            'street2' => '',//$datosPago['PAGA_DIRCALLE2'],
                            'city' => '',//$datosPago['PAGA_DIRCIUDAD'],
                            'state' => '',//$datosPago['PAGA_DIRESTADO'],
                            'country' => $valores['PAGA_DIRPAIS'],
                            'postalCode' => '',//$datosPago['PAGA_DIRPOSTAL'],
                            'phone' => '',//$datosPago['PAGA_DIRTELEFO']
                        )
                    ),
                    'creditCard' => array('number' => $datosMedioPago["numero"],//$valores['NUMERO_TARJETA'],
                        'securityCode' => $datosMedioPago["CVV"],
                        'expirationDate' => $datosMedioPago["anoVencimiento"]."/".$datosMedioPago["mesVencimiento"],
                        'name' => $datosMedioPago["nombre"]),
                    'extraParameters' => array('INSTALLMENTS_NUMBER' => $datosMedioPago["cuotas"], 'EXTRA1' => $referenciaPago),
                    'type' => 'AUTHORIZATION_AND_CAPTURE',
                    'paymentMethod' => $datosMedioPago["franquicia"],
                    'paymentCountry' => $valores['PAIS'],
                    'deviceSessionId' => session_id(),//session_create_id(),//$_SESSION['id_session'],
                    'ipAddress' => $_SERVER['REMOTE_ADDR'], //$valores['IP_ORIGEN'],
                    'cookie' => session_id(),
                    'userAgent' => $_SERVER['HTTP_USER_AGENT']
                ),
                'test' => $this->isTest);
            //AGREGAR CAMPO dniType (CC, CE, NIT, TI, PP, IDC, CEL, RC, DE)
            //CODENSA
            if ($datosMedioPago["formaPago"] == 6 || $datosMedioPago["franquicia"] == 'CODENSA') {
                $datos_trans['transaction']['payer']['dniType'] = $datosMedioPago["tipoDocumento"];
            }

        } elseif ($datosMedioPago["formaPago"] == 9) {
            $url_confirma = "valor_llave=" . $valor_llave . "&IDTransaccion=" . $datosPago["IDTransaccion"]
                . "&formaPago=" . $datosMedioPago["formaPago"] . "&nombreUsuario=". $datosMedioPago["nombreUsuario"]
                . "&tipoTrans=" . $info_trans[0]['TIPO_TRANS'];

            $url_retorno = NOMBRE_HOST . "transaccion/debito?".$url_confirma;

            /*if ( $valores['redir_retorno'] != '' ){
                $url_retorno = NOMBRE_HOST."phrame.php?action=despliegue_personal&clase=".$valores['clase_retorno']."&metodo=".$valores['metod_retorno'].$url_confirma;
            }else{
                $url_retorno = NOMBRE_HOST."phrame.php?action=confirma_payu".$url_confirma;
            }*/

            $datos_trans = array('language' => $this->language,
                'command' => 'SUBMIT_TRANSACTION',
                'merchant' => array('apiKey' => $this->apiKey,
                    'apiLogin' => $this->apiLogin),
                'transaction' => array('order' => array('accountId' => $this->accountId,
                    'referenceCode' => $datosPago['numeroFactura'] . "_" . $datosPago['numeroIntentos'],
                    'description' => $datosPago["descripcion"],
                    'language' => $this->language,
                    'signature' => $signature,
                    'notifyUrl' => NOMBRE_HOST . "phrame.php?action=registra_payu" . $url_confirma,
                    'additionalValues' => array('TX_VALUE' => array('value' => $datosPago["valorTotal"],
                        'currency' => $currency
                    ),
                        'TX_TAX' => array('value' => 0,
                            'currency' => $currency
                        ),
                        'TX_TAX_RETURN_BASE' => array('value' => 0,
                            'currency' => $currency
                        )
                    ),
                    'buyer' => array('fullName' => '',//$datosPago['COMP_NOMBRE'],
                        'emailAddress' => '',//$datosPago['COMP_EMAIL'],
                        'contactPhone' => '',//$datosPago['COMP_TELEFONO'],
                        'dniNumber' => '',//$datosPago['COMP_IDENTIF'],
                        'shippingAddress' => array('street1' => '',//$datosPago['COMP_DIRCALLE1'],
                            'street2' => '',//$datosPago['COMP_DIRCALLE2'],
                            'city' => '',//$datosPago['COMP_DIRCIUDAD'],
                            'state' => '',//$datosPago['COMP_DIRESTADO'],
                            'country' => $valores['COMP_DIRPAIS'],
                            'postalCode' => '',//$datosPago['COMP_DIRPOSTAL'],
                            'phone' => '',//$datosPago['COMP_DIRTELEFO']
                        )
                    ),
                    'shippingAddress' => array('street1' => '',//$datosPago['ORDE_DIRCALLE1'],
                        'street2' => '',//$datosPago['ORDE_DIRCALLE2'],
                        'city' => '',//$datosPago['ORDE_DIRCIUDAD'],
                        'state' => '',//$datosPago['ORDE_DIRESTADO'],
                        'country' => $valores['ORDE_DIRPAIS'],
                        'postalCode' => '',//$datosPago['ORDE_DIRPOSTAL'],
                        'phone' => '',//$datosPago['ORDE_DIRTELEFO']
                    )
                ),
                    'payer' => array('fullName' => $valores['titular'],
                        'emailAddress' => $valores['email'],
                        'contactPhone' => $valores['telefono'],
                        'dniNumber' => $valores['numeroDocumento'],
                        'dniType' => $valores['tipoDocumento'],
                        'billingAddress' => array('street1' => '',
                            'street2' => '',
                            'city' => '',
                            'state' => '',
                            'country' => $valores['PAGA_DIRPAIS'],
                            'postalCode' => '',
                            'phone' => ''
                        )
                    ),
                    "extraParameters" => Array('RESPONSE_URL' => $url_retorno,
                        'LENDING_SIGNATURE' => $datosPago['signature'],
                        'EXTRA1' => $referenciaPago
                    ),
                    'type' => 'AUTHORIZATION_AND_CAPTURE',
                    'paymentMethod' => 'LENDING',
                    'paymentCountry' => $valores['PAIS'],
                    'deviceSessionId' => session_id(),
                    'ipAddress' => $_SERVER['REMOTE_ADDR'],
                    'cookie' => session_id(),
                    'userAgent' => $_SERVER['HTTP_USER_AGENT']
                ),
                'test' => false);
        } elseif ($datosMedioPago["formaPago"] == 1) {

            $stringToHash = $this->apiKey_PSE . "~" . $this->merchantId_PSE . "~" . trim($datosPago['numeroFactura'] . "_" . $datosPago['numeroIntentos']) . "~" . trim($datosPago['valorTotal']) . "~" . $currency;
            $signature = md5($stringToHash);

            // Se define la URL donde se confirma el pago

            //valor_llave & datosPago["IDTrasaccion"]

            $url_confirma = "valor_llave=" . $valor_llave . "&IDTransaccion=" . $datosPago["IDTransaccion"]
                . "&formaPago=" . $datosMedioPago["formaPago"] . "&nombreUsuario=". $datosMedioPago["nombreUsuario"]
                . "&tipoTrans=" . $info_trans[0]['TIPO_TRANS'];

            /*$url_confirma = "&valor_llave=" . $valor_llave . "&pasa_personal=" . $valores['PASA_PERSONAL'];
            $url_confirma .= "&pasa_confirm=" . OBJE_CONFIRM . "&empresa=" . $_SESSION['usrio_empr'] . "&clase_confirm=" . $valores['clase_confirm'];
            $url_confirma .= "&metod_confirm=" . $valores['metod_confirm'] . "&obje_cancel=" . $valores['obje_cancel'] . "&FORMA_PAGO=" . $valores['FORMA_PAGO'];*/

            /*if ($valores['redir_retorno'] != '') {
                $url_retorno = NOMBRE_HOST . "phrame.php?action=despliegue_personal&clase=" . $valores['clase_retorno'] . "&metodo=" . $valores['metod_retorno'] . $url_confirma;
            } else {
                $url_retorno = NOMBRE_HOST . "phrame.php?action=confirma_payu" . $url_confirma;
            }*/

            $url_retorno = NOMBRE_HOST . "transaccion/debito?".$url_confirma;

            $datos_trans = array('language' => $this->language,
                'command' => 'SUBMIT_TRANSACTION',
                'merchant' => Array('apiKey' => $this->apiKey_PSE,
                    'apiLogin' => $this->apiLogin_PSE),
                'transaction' => Array('order' => Array('accountId' => $this->accountId,
                    'referenceCode' => $datosPago['numeroFactura'] . "_" . $datosPago['numeroIntentos'],
                    'description' => $datosPago['descripcion'],
                    'language' => $this->language,
                    'signature' => $signature,
                    'notifyUrl' => NOMBRE_HOST . "phrame.php?action=registra_payu" . $url_confirma,
                    'additionalValues' => Array('TX_VALUE' => Array('value' => $datosPago['valorTotal'],
                        'currency' => $currency
                    ),
                        'TX_TAX' => array('value' => 0,
                            'currency' => $currency
                        ),
                        'TX_TAX_RETURN_BASE' => array('value' => 0,
                            'currency' => $currency
                        )
                    ),
                    'buyer' => Array('emailAddress' => '',//$valores['COMP_EMAIL'],
                    )
                ),
                    'payer' => Array('fullName' => $datosMedioPago["nombre"],//$valores['TITULAR'],
                        'emailAddress' => $datosMedioPago["email"],//$valores['EMAIL'],
                        'contactPhone' => $datosMedioPago["telefono"],//$valores['TELEFONO']
                    ),
                    "extraParameters" => Array('RESPONSE_URL' => $url_retorno,
                        'PSE_REFERENCE1' => $_SERVER["REMOTE_ADDR"],
                        'FINANCIAL_INSTITUTION_CODE' => $datosMedioPago["banco"],
                        'USER_TYPE' => $datosMedioPago["tipoCliente"],
                        'PSE_REFERENCE2' => $datosMedioPago["tipoDocumento"],
                        'PSE_REFERENCE3' => $datosMedioPago["numeroDocumento"]
                    ),
                    'type' => 'AUTHORIZATION_AND_CAPTURE',
                    'paymentMethod' => "PSE",
                    'paymentCountry' => $valores['PAIS'],
                    'ipAddress' => $_SERVER["REMOTE_ADDR"],
                    'cookie' => session_id(),
                    'userAgent' => $_SERVER['HTTP_USER_AGENT']
                ),
                'test' => false
            );
        } else {
            return array("error" => true, "mensaje" => "Forma de pago incorrecta. PayU.");
        }

        if ($datosMedioPago["formaPago"] != 1) {
            $datosMedioPago["numero"] = str_pad('', strlen($datosMedioPago["numero"]) - 4, "*", STR_PAD_LEFT) . substr($datosMedioPago["numero"], -4);
        } else {
            $datosMedioPago["numero"] = '';
        }

        $inicio_pago = "UPDATE GT_PAGO_PASARELA SET EMPRESA = '" . $empresa . "', NIT = '" . $nit . "', ";
        $inicio_pago .= "VALOR = '" . $datosPago['valorTotal'] . "', IVA = '" . $datosPago['iva'] . "', VALOR_TOTAL = '";
        $inicio_pago .= $datosPago['valorTotal'] . "', MONEDA = '" . $currency . "', IP_ORIGEN = '" . $_SERVER['REMOTE_ADDR'];
        $inicio_pago .= "', INTENTOS = " . $datosPago["numeroIntentos"] . ", NUMERO_TARJETA = '" . $datosMedioPago['numero'];
        $inicio_pago .= "', CLACO_NUMERO = 1 WHERE PASA_NUMERO = " . $valor_llave;

        $consulta->setConsulta($inicio_pago);
        $consulta->ejecutarConsulta("noshow");

        $resultado = $this->peticionPost($datos_trans, PAYU_URL_PAYMENTS);

        if (LOG_FILE == 1) {
            $trace = 'solicitud:' . PHP_EOL . json_encode($datos_trans) . PHP_EOL . 'respuesta:' . PHP_EOL . json_encode($resultado);
            $file = fopen("../tmp/payuTransacc" . $valor_llave . "_" . date('YmdHis') . ".txt", "w");
            fwrite($file, $trace);
            fclose($file);
        }

        // Porcesar el resultado
        if ($resultado['code'] == "SUCCESS") {

            $transaccion = $resultado['transactionResponse'];

            $fechaTransaccion = $consulta->getLink()->DBtimestamp(date('Y-m-d')." ".date("H").":".date("i").":".date("s"));

            $actu_pago = "UPDATE GT_PAGO_PASARELA SET EMPRESA = '" . $empresa . "', NIT = '" . $nit . "', ";
            $actu_pago .= "FECHA_TRANSACCION = " . $fechaTransaccion . ", ESTADO = '" . $resultado['transactionResponse']['state'] . "', ";
            $actu_pago .= "ID_ORDEN = '" . $transaccion['orderId'] . "', CUS = '" . $transaccion['trazabilityCode'] . "', ";
            $actu_pago .= "VALOR = '" . $datosPago['valorTotal'] . "', IVA = '" . $datosPago['iva'] . "', VALOR_TOTAL = '" . $datosPago['valorTotal'] . "', MONEDA = '" . $currency . "', IP_ORIGEN = '" . $_SERVER['REMOTE_ADDR'] . "', ";
            $actu_pago .= "CODIG_TRANSACC = '" . $resultado['code'] . "', ERROR_TRANSACC = '" . $resultado['error'] . "', ID_TRANSACC = '";
            $actu_pago .= $transaccion['transactionId'] . "', RED_RESPUE_CODIG = '" . $transaccion['paymentNetworkResponseCode'] . "', ";
            $actu_pago .= "RED_RESPUE_MENSA = '" . $transaccion['paymentNetworkResponseErrorMessage'] . "', TRAZABI_CODIG = '" . $transaccion['trazabilityCode'] . "', ";
            $actu_pago .= "AUTORIZ_CODIG = '" . $transaccion['authorizationCode'] . "', RESPUE_CODIG = '" . $transaccion['responseCode'] . "', ";
            $actu_pago .= "ERROR_CODIG = '" . $transaccion['errorCode'] . "', ERROR_MENSA = '" . $transaccion['responseMessage'] . "', ";
            $actu_pago .= "FECHA_OPERACION = '" . $transaccion['operationDate'] . "', INTENTOS = " . $datosPago['numeroIntentos'] . ", CLACO_NUMERO = NULL, ";
            $actu_pago .= "RAZON_PENDIENTE = '" . $transaccion['pendingReason'] . "', NUMERO_TARJETA = '" . $datosMedioPago['numero'] . "' WHERE PASA_NUMERO = " . $valor_llave;

        } elseif ($resultado['code'] == "ERROR") {

            $actu_pago = "UPDATE GT_PAGO_PASARELA SET EMPRESA = '" . $empresa . "', NIT = '" . $nit . "', ESTADO = '" . $resultado['code'] . "', ";
            $actu_pago .= "VALOR = '" . $datosPago['valorTotal'] . "', IVA = '" . $datosPago['iva'] . "', VALOR_TOTAL = '" . $datosPago['valorTotal'] . "', MONEDA = '" . $currency . "', IP_ORIGEN = '" . $_SERVER['REMOTE_ADDR'] . "', ";
            $actu_pago .= "CODIG_TRANSACC = '" . $resultado['code'] . "', ERROR_TRANSACC = '" . $resultado['error'] . "', INTENTOS = " . $datosPago['numeroIntentos'] . ",  ";
            $actu_pago .= "NUMERO_TARJETA = '" . $datosMedioPago['numero'] . "', CLACO_NUMERO = NULL WHERE PASA_NUMERO = " . $valor_llave;

        } else {

            $actu_pago = "UPDATE GT_PAGO_PASARELA SET EMPRESA = '" . $empresa . "', NIT = '" . $nit . "', ";
            $actu_pago .= "VALOR = '" . $datosPago['valorTotal'] . "', IVA = '" . $datosPago['iva'] . "', VALOR_TOTAL = '" . $datosPago['valorTotal'] . "', MONEDA = '" . $currency . "', IP_ORIGEN = '" . $_SERVER['REMOTE_ADDR'] . "', ";
            $actu_pago .= "CODIG_TRANSACC = 'ERROR - " . $resultado['code'] . "', ERROR_TRANSACC = 'No responde - " . $resultado['error'] . "', INTENTOS = " . $datosPago['numeroIntentos'] . ",  ";
            $actu_pago .= "NUMERO_TARJETA = '" . $datosMedioPago['numero'] . "', CLACO_NUMERO = NULL WHERE PASA_NUMERO = " . $valor_llave;

        }

        if ($resultado['transactionResponse']['state'] == 'SUBMITTED' && LOG_FILE == 1) {
            $file = fopen("tmp/TransaPayU_" . $valor_llave . "_" . date('YmdHis') . ".txt", "w");
            fwrite($file, $actu_pago);
            fclose($file);
        }

        $consulta->setConsulta($actu_pago);
        $consulta->ejecutarConsulta("noshow");

        $consulta->desconectar();

        // Se genera de nuevo la fecha para construir el arreglo de datos para retornar
        $fechaTransaccion = $consulta->getLink()->DBtimestamp(date('Y-m-d')." ".date("H").":".date("i").":".date("s"));

        $datos = array("CUS" => $resultado["transactionResponse"]["trazabilityCode"], "empresa" => $empresa,
            "NIT" => $nit, "IP" => $_SERVER["REMOTE_ADDR"], "fecha" => $fechaTransaccion,
            "estado" => $resultado["transactionResponse"]["state"], "transaccion" => $resultado["transactionResponse"]["orderId"]);

        // Se obtiene la URL para redireccionar al banco seleccionado en forma de pago 1
        if ($datosMedioPago["formaPago"] == 1) {
            $url = $resultado['transactionResponse']['extraParameters']['BANK_URL'];
            $datos["BANK_URL"] = $url;
        }

        if ($resultado == "Error") {
            return array('transaccion' => $resultado, 'respuesta' => $datos, 'error' => true, 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PAYU);

        } elseif ($resultado['code'] == "ERROR" && ($datosMedioPago["formaPago"] == 2 || $datosMedioPago["formaPago"] == 6)) { //CODENSA
            return array('transaccion' => $resultado, 'respuesta' => $datos, 'error' => true, 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PAYU . "<br><br>" . utf8_decode($resultado['error']));

        } elseif ($resultado['code'] == "ERROR" && $datosMedioPago["formaPago"] == 1 && $transaccion['responseCode'] == 'EXCEEDED_AMOUNT') {
            return array('transaccion' => $resultado, 'respuesta' => $datos, 'error' => true, 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_EXCE);

        } elseif ($resultado['code'] == "ERROR" && $datosMedioPago["formaPago"] == 1 && $transaccion['responseCode'] == 'BANK_UNREACHABLE') {
            return array('transaccion' => $resultado, 'respuesta' => $datos, 'error' => true, 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_UNRE);

        } elseif ($resultado['code'] == "ERROR" && $datosMedioPago["formaPago"] == 1 && $transaccion['responseCode'] == 'INTERNAL_PAYMENT_PROVIDER_ERROR') {
            return array('transaccion' => $resultado, 'respuesta' => $datos, 'error' => true, 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_INTE);

        } elseif ($resultado['code'] == "ERROR" && $datosMedioPago["formaPago"] == 1 && ($transaccion['responseCode'] == '')) {
            return array('transaccion' => $resultado, 'respuesta' => $datos, 'error' => true, 'valor_llave' => $valor_llave, 'mensaje' => utf8_decode($resultado['error']));

        } elseif ($resultado['code'] == "ERROR" && $datosMedioPago["formaPago"] == 1 && empty($url)) {
            return array('transaccion' => $resultado, 'respuesta' => $datos, 'error' => true, 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_INTE);
        } else {
            return array('transaccion' => $resultado, 'respuesta' => $datos, 'error' => false, 'valor_llave' => $valor_llave, 'mensaje' => '');

        }

    }

    /**
     * Generar pago a PayU
     *
     * @param  array $valores Datos del formulario para transaccion
     * @return array/boolean  Retorna los bancos de PayU para pago con tarjeta débito
     */
    function payuTransaccionToken($datosPago, $datosMedioPago)
    {
        set_time_limit(0);

        $consulta = new Consulta;
        $isTestDom = ($this->isTest == 1) ? true : false;

        /*$sql_llave = "SELECT PASA_NUMERO FROM GT_PAGO_PASARELA WHERE ID_TRANSACCION = " . $datosPago['IDTransaccion'] . " AND NUMEROFACTURA = '" . $datosPago['numeroFactura'] . "' ";
        $sql_llave .= "AND ELIMINADO = -1 ORDER BY PASA_NUMERO DESC";*/

        $sql_llave  = "SELECT G.PASA_NUMERO, C.TIPO_TRANS, C.TIPO_TRANS_ORIG, C.CodigoCliente, C.NRO_CUENTA FROM GT_PAGO_PASARELA G ";
        $sql_llave .= "INNER JOIN CL_PAGOSCLARO C ON G.ID_TRANSACCION = C.CLACO_NUMERO WHERE G.ID_TRANSACCION = ".$datosPago['IDTransaccion'];
        $sql_llave .= " AND G.NUMEROFACTURA = '".$datosPago['numeroFactura']."' AND G.ELIMINADO = -1 ORDER BY G.PASA_NUMERO DESC";

        $consulta->setConsulta($sql_llave);
        $info_trans = $consulta->ejecutarConsulta();
        $valor_llave = $info_trans[0]['PASA_NUMERO'];

        if ($consulta->numeroFilas() == 0 || $consulta->getResultado() != 2 || empty($valor_llave) || empty($datosPago["valorTotal"]) || $datosPago['registroPago'] == "fracaso") {
            //return array('transacc' => $resultado, 'error' => true, 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PAYU);
            return array('error' => true, 'result' => "error", 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PAYU);
        }

        // Inicio Asignación valor informativo de la referencia de pago dentro de la consulta
        if ($info_trans[0]['TIPO_TRANS'] == 3) {
            //Si es servicio fijo se envía el #cuenta
            $referenciaPago = $info_trans[0]['NRO_CUENTA'];
        } elseif ($info_trans[0]['TIPO_TRANS'] == 2) {
            //Si es servicio móvil se envía la referencia de pago
            $referenciaPago = $info_trans[0]['CodigoCliente'];
        } else {
            $referenciaPago = '';
        }
        // Fin Asignación valor informativo de la referencia de pago dentro de la consulta

        $valorTotal_Base = $datosPago["valorTotal"];

        if (empty($valorTotal_Base)) {
            return array('transacc' => 'Error valor pago', 'error' => true, 'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PAYU);
        }

        if ($datosPago["iva"] == "") $datosPago["iva"] = 0;

        //Se quitan los espacios en blanco que puedan haber en los valores

        $datosPago["valorTotal"] = trim($datosPago["valorTotal"]);
        $datosPago["iva"] = trim($datosPago["iva"]);
        $datosMedioPago["email"] = trim($datosMedioPago["email"]);

        $currency = (empty($datosPago["moneda"])) ? $this->currency : $datosPago["moneda"];

        # Inicio asignación nombre y NIT de la empresa que recibe el pago Comcel S.A o Telmex Colombia S.A
        if ($info_trans[0]['TIPO_TRANS'] == 3) {
            $variablesEmpresa = "'VAR_EMPRESAFIJ', 'VAR_NITFIJ'";
        } else {
            $variablesEmpresa = "'VAR_EMPRESA', 'VAR_NIT'";
        }

        $queryEmpresa = "SELECT VALOR FROM CP_VARIABLES WITH (NOLOCK) WHERE NOMBRE IN (" . $variablesEmpresa . ") ";
        $queryEmpresa .= "AND FECHA_INICIO BETWEEN FECHA_INICIO AND " . $consulta->getLink()->ifNull("FECHA_FIN", "'3000-01-01'");
        $queryEmpresa .= " AND ELIMINADO = -1 AND ACTIVO = '1'";

        $consulta->setConsulta($queryEmpresa);
        $datosEmpresa = $consulta->ejecutarConsulta();

        $empresa = $datosEmpresa[0]['VALOR'];
        $nit = $datosEmpresa[1]['VALOR'];
        # Fin asignación nombre y NIT de la empresa que recibe el pago Comcel S.A o Telmex Colombia S.A

        $valores['PAIS'] = (empty($valores['PAIS'])) ? $this->paisPago : $valores['PAIS'];
        $valores['COMP_DIRPAIS'] = (empty($valores['COMP_DIRPAIS'])) ? $this->paisComp : $valores['COMP_DIRPAIS'];
        $valores['ORDE_DIRPAIS'] = (empty($valores['ORDE_DIRPAIS'])) ? $this->paisOrde : $valores['ORDE_DIRPAIS'];
        $valores['PAGA_DIRPAIS'] = (empty($valores['PAGA_DIRPAIS'])) ? $this->paisPaga : $valores['PAGA_DIRPAIS'];

        /*$valores['PAIS'] = $this->paisPago;
        $valores['COMP_DIRPAIS'] = $this->paisComp;
        $valores['ORDE_DIRPAIS'] = $this->paisOrde;
        $valores['PAGA_DIRPAIS'] = $this->paisPaga;*/

        if (empty($datosPago['numeroIntentos']))
            $datosPago['numeroIntentos'] = 0;

        // Se valida el medio de pago seleccionado
        if ($datosMedioPago["formaPago"] == 2 || $datosMedioPago["formaPago"] == 4 || $datosMedioPago["formaPago"] == 5 || $datosMedioPago["formaPago"] == 6) {

            $stringToHash = $this->apiKey . "~" . $this->merchantId . "~" . trim($datosPago['numeroFactura'] . "_" . $datosPago['numeroIntentos']) . "~" . trim($datosPago['valorTotal']) . "~" . $currency;
            $signature = md5($stringToHash);

            $datos_trans = array('language' => $this->language,
                'command' => 'SUBMIT_TRANSACTION',
                'merchant' => array('apiKey' => $this->apiKey,
                    'apiLogin' => $this->apiLogin),
                'transaction' => array('order' => array('accountId' => $this->accountId,
                    'referenceCode' => $datosPago['numeroFactura'] . "_" . $datosPago['numeroIntentos'],
                    'description' => $datosPago['descripcion'],
                    'language' => $this->language,
                    'signature' => $signature,
                    'notifyUrl' => '',
                    'additionalValues' => array('TX_VALUE' => array('value' => $datosPago['valorTotal'],
                        'currency' => $currency
                    ),
                        'TX_TAX' => array('value' => 0,
                            'currency' => $currency
                        ),
                        'TX_TAX_RETURN_BASE' => array('value' => 0,
                            'currency' => $currency
                        )
                    ),
                    'buyer' => array('fullName' => '',//$valores['COMP_NOMBRE'],
                        'emailAddress' => '',//$valores['COMP_EMAIL'],
                        'contactPhone' => '',//$valores['COMP_TELEFONO'],
                        'dniNumber' => '',//$valores['COMP_IDENTIF'],
                        'shippingAddress' => array('street1' =>'',// $valores['COMP_DIRCALLE1'],
                            'street2' => '',//$valores['COMP_DIRCALLE2'],
                            'city' => '',//$valores['COMP_DIRCIUDAD'],
                            'state' => '',//$valores['COMP_DIRESTADO'],
                            'country' => $valores['COMP_DIRPAIS'],
                            'postalCode' => '',//$valores['COMP_DIRPOSTAL'],
                            'phone' => '',//$valores['COMP_DIRTELEFO']
                        )
                    ),
                    'shippingAddress' => array('street1' => '',//$valores['ORDE_DIRCALLE1'],
                        'street2' => '',//$valores['ORDE_DIRCALLE2'],
                        'city' => '',//$valores['ORDE_DIRCIUDAD'],
                        'state' => '',//$valores['ORDE_DIRESTADO'],
                        'country' => $valores['ORDE_DIRPAIS'],
                        'postalCode' => '',//$valores['ORDE_DIRPOSTAL'],
                        'phone' => '',//$valores['ORDE_DIRTELEFO']
                    )
                ),
                    'payer' => array('fullName' => $datosMedioPago["nombre"],//$valores['NOMBRE_TARJETA'],
                        'emailAddress' => $datosMedioPago["email"],
                        'contactPhone' => $datosMedioPago["telefono"],
                        'dniNumber' => $datosMedioPago["numeroDocumento"],
                        'billingAddress' => array('street1' => '',//$valores['PAGA_DIRCALLE1'],
                            'street2' => '',//$valores['PAGA_DIRCALLE2'],
                            'city' => '',//$valores['PAGA_DIRCIUDAD'],
                            'state' => '',//$valores['PAGA_DIRESTADO'],
                            'country' => $valores['PAGA_DIRPAIS'],
                            'postalCode' => '',//$valores['PAGA_DIRPOSTAL'],
                            'phone' => '',//$valores['PAGA_DIRTELEFO']
                        )
                    ),
                    'creditCardTokenId' => $datosMedioPago['token'],
                    'extraParameters' => array('INSTALLMENTS_NUMBER' => $datosMedioPago["cuotas"], 'EXTRA1' => $referenciaPago),
                    'type' => 'AUTHORIZATION_AND_CAPTURE',
                    'paymentMethod' => $datosMedioPago["franquicia"],
                    'paymentCountry' => $valores['PAIS'],
                    'deviceSessionId' => session_id(),
                    'ipAddress' => $_SERVER['REMOTE_ADDR'],
                    'userAgent' => $_SERVER['HTTP_USER_AGENT']
                ),
                'test' => $isTestDom);

            // Se agrega el código de seguridad si viene dentro de los datosMedioPago
            if ($datosMedioPago['CVV'] != '') {
                $datos_trans['transaction']['creditCard'] = array("securityCode" => $datosMedioPago["CVV"]);
            }
            //AGREGAR CAMPO dniType (CC, CE, NIT, TI, PP, IDC, CEL, RC, DE)
            //CODENSA
            if ($datosMedioPago["formaPago"] == 6 || $datosMedioPago["franquicia"] == 'CODENSA') {
                $datos_trans['transaction']['payer']['dniType'] = $datosMedioPago['tipoDocumento'];
            }

        } else {
            return array("error" => true, "mensaje" => "Forma de pago incorrecta. PayU Tokenizada.");
        }

        if (LOG_FILE == 1) {
            $file = fopen("../tmp/TransaPayTok_" . $valor_llave . "_" . date('YmdHis') . ".txt", "w");
            fwrite($file, json_encode($datos_trans));
            fclose($file);
        }

        $resultado = $this->peticionPost($datos_trans, $this->urlPayments);

        // Porcesar el resultado        
        if ($resultado['code'] == "SUCCESS") {

            $transaccion = $resultado['transactionResponse'];

            $fechaTransaccion = $consulta->getLink()->DBtimestamp(date('Y-m-d')." ".date("H").":".date("i").":".date("s"));

            $actu_pago = "UPDATE GT_PAGO_PASARELA SET EMPRESA = '" . $empresa . "', NIT = '" . $nit . "', ";
            $actu_pago .= "FECHA_TRANSACCION = " . $fechaTransaccion . ", ESTADO = '" . $resultado['transactionResponse']['state'] . "', ";
            $actu_pago .= "ID_ORDEN = '" . $transaccion['orderId'] . "', CUS = '" . $transaccion['trazabilityCode'] . "', ";
            $actu_pago .= "VALOR = '" . $datosPago['valorTotal'] . "', IVA = '" . $datosPago['iva'] . "', VALOR_TOTAL = '" . $datosPago['valorTotal'] . "', MONEDA = '" . $currency . "', IP_ORIGEN = '" . $_SERVER['REMOTE_ADDR'] . "', ";
            $actu_pago .= "CODIG_TRANSACC = '" . $resultado['code'] . "', ERROR_TRANSACC = '" . $resultado['error'] . "', ID_TRANSACC = '";
            $actu_pago .= $transaccion['transactionId'] . "', RED_RESPUE_CODIG = '" . $transaccion['paymentNetworkResponseCode'] . "', ";
            $actu_pago .= "RED_RESPUE_MENSA = '" . $transaccion['paymentNetworkResponseErrorMessage'] . "', TRAZABI_CODIG = '" . $transaccion['trazabilityCode'] . "', ";
            $actu_pago .= "AUTORIZ_CODIG = '" . $transaccion['authorizationCode'] . "', RESPUE_CODIG = '" . $transaccion['responseCode'] . "', ";
            $actu_pago .= "ERROR_CODIG = '" . $transaccion['errorCode'] . "', ERROR_MENSA = '" . $transaccion['responseMessage'] . "', ";
            $actu_pago .= "FECHA_OPERACION = '" . $transaccion['operationDate'] . "', ";
            $actu_pago .= "RAZON_PENDIENTE = '" . $transaccion['pendingReason'] . "' WHERE PASA_NUMERO = " . $valor_llave;

        } elseif ($resultado['code'] == "ERROR") {

            $actu_pago = "UPDATE GT_PAGO_PASARELA SET EMPRESA = '" . $empresa . "', NIT = '" . $nit . "', ";
            $actu_pago .= "VALOR = '" . $datosPago['valorTotal'] . "', IVA = '" . $datosPago['iva'] . "', VALOR_TOTAL = '" . $datosPago['valorTotal'] . "', MONEDA = '" . $currency . "', IP_ORIGEN = '" . $_SERVER['REMOTE_ADDR'] . "', ";
            $actu_pago .= "CODIG_TRANSACC = '" . $resultado['code'] . "', ERROR_TRANSACC = '" . $resultado['error'] . "', INTENTOS = " . $datosPago['numeroIntentos'] . "  ";
            $actu_pago .= "WHERE PASA_NUMERO = " . $valor_llave;

        } else {

            $actu_pago = "UPDATE GT_PAGO_PASARELA SET EMPRESA = '" . $empresa . "', NIT = '" . $nit . "', ";
            $actu_pago .= "VALOR = '" . $valores['VALOR'] . "', IVA = '" . $valores['IVA'] . "', VALOR_TOTAL = '" . $valores['VALOR_TOTAL'] . "', MONEDA = '" . $currency . "', IP_ORIGEN = '" . $_SERVER['REMOTE_ADDR'] . "', ";
            $actu_pago .= "CODIG_TRANSACC = 'ERROR - " . $resultado['code'] . "', ERROR_TRANSACC = 'No responde - " . $resultado['error'] . "', INTENTOS = " . $valores['INTENTOS'] . "  ";
            $actu_pago .= "WHERE PASA_NUMERO = " . $valor_llave;

        }

        /*$file = fopen("tmp/TransaPayU_".$valor_llave."_".date('YmdHis').".txt", "w");
        fwrite($file, $actu_pago);
        fclose($file);*/

        $consulta->setConsulta($actu_pago);
        $consulta->ejecutarConsulta("noshow");

        $consulta->desconectar();

        // Se genera de nuevo la fecha para construir el arreglo de datos para retornar
        $fechaTransaccion = $consulta->getLink()->DBtimestamp(date('Y-m-d')." ".date("H").":".date("i").":".date("s"));

        $datos = array("CUS" => $resultado["transactionResponse"]["trazabilityCode"], "empresa" => $empresa,
            "NIT" => $nit, "IP" => $_SERVER["REMOTE_ADDR"], "fecha" => $fechaTransaccion,
            "estado" => $resultado["transactionResponse"]["state"], "transaccion" => $resultado["transactionResponse"]["orderId"]);

        if ($resultado == "Error" || empty($resultado)) {
            return array('transaccion' => $resultado, 'respuesta' => $datos, 'datos_trans' => $datos_trans, 'error' => true,
                'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PAYU);

        } elseif ($resultado['code'] == "ERROR" && ($datosMedioPago["formaPago"] == 2 || $datosMedioPago["formaPago"] == 6)) { //CODENSA
            return array('transaccion' => $resultado, 'respuesta' => $datos, 'error' => true, 'valor_llave' => $valor_llave,
                'mensaje' => MSG_ERROR_PAYU . "<br><br>" . utf8_decode($resultado['error']));

        } elseif ($resultado['code'] == "ERROR") {
            return array('transaccion' => $resultado, 'respuesta' => $datos, 'datos_trans' => $datos_trans, 'error' => true,
                'valor_llave' => $valor_llave, 'mensaje' => MSG_ERROR_PAYU . "<br><br>" . utf8_decode($resultado['error']));

        } else {
            return array('transaccion' => $resultado, 'respuesta' => $datos, 'datos_trans' => $datos_trans, 'error' => false,
                'valor_llave' => $valor_llave, 'mensaje' => '');
        }

    }

    /**
     * Peticiones realizadas via Json a PayU
     *
     * @param  array $datos Informacion enviada a PayU
     * @param  string $url URL de conexion para PayU
     * @return array/boolean  Retorna un arreglo con la informacion que se devuelve de PayU
     */
    function payuConfirmaPSE($valores)
    {

        $consulta = new Consulta;

        if ($valores['state_pol'] == 4) {
            $estadoPayU = 'APPROVED';
        } elseif ($valores['state_pol'] == 6) {
            $estadoPayU = 'DECLINED';
        } elseif ($valores['state_pol'] == 5) {
            $estadoPayU = 'EXPIRED';
        } else {
            $estadoPayU = 'PENDING';
        }

        $actu_pago = "UPDATE GT_PAGO_PASARELA SET ";
        $actu_pago .= "FECHA_TRANSACCION = " . Patron::validarVacios(4, date('Y-m-d')) . ", ESTADO = '" . $estadoPayU . "', ";
        $actu_pago .= "RESPUE_CODIG = '" . $valores['response_code_pol'] . "', ERROR_CODIG = '" . $valores['response_message_pol'] . "', ";
        $actu_pago .= "CUS = '" . $valores['cus'] . "' ";
        $actu_pago .= "WHERE PASA_NUMERO = " . $valores['valor_llave'];

        /*$datos = json_encode($valores);                    
        
        $file = fopen("tmp/registraPSE_".$valores['valor_llave']."_".date('YmdHis').".txt", "w");
        fwrite($file, $actu_pago);
        fwrite($file, $datos);
        fclose($file);*/

        $consulta->setConsulta($actu_pago);
        $consulta->ejecutarConsulta("noshow");
        $consulta->desconectar();
    }


    /**
     * Peticiones realizadas via Json a PayU
     *
     * @param  array $datos Informacion enviada a PayU
     * @param  string $url URL de conexion para PayU
     * @return array/boolean  Retorna un arreglo con la informacion que se devuelve de PayU
     */
    function peticionPost($datos, $url, $json = "")
    {

        @ini_set('display_errors', 'off');

        //creamos el json a partir de nuestro arreglo
        $jsonData = $datos;
        if ($json == "")
            $jsonData = json_encode($datos);

        $jsonData = str_replace("\/", "/", $jsonData);
        $httpHeader = array('Content-Type: application/json; charset=UTF-8',
            'Content-Length: ' . strlen($jsonData),
            'Accept: application/json');

        //inicializamos el objeto CUrl
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeader);

        //Ejecutamos la petición        
        $curlResponse = curl_exec($curl);
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        //--------------------------------        
        if ($curlResponse === false && $httpStatus === 0) {
            return "Error";
            throw new ConnectionException('Error conection', 'the url [' . $url . '] did not respond');
        }

        if ($curlResponse === false) {
            $requestInfo = http_build_query(curl_getinfo($curl), ' ', ',');
            $curlMsgError = sprintf(" error occured during curl exec info: curl message[%s], curl error code [%s], curl request details [%s]",
                curl_error($curl), curl_errno($curl), $requestInfo);

            curl_close($curl);
            throw new RuntimeException($curlMsgError);
        }

        curl_close($curl);

        /*if(empty($curlResponse)){
            echo $httpStatus;
        }else{
            echo $curlResponse;
        }*/

        return json_decode($curlResponse, true);

    }


    /**
     * Permite generar las listas para las fechas de vencimiento
     *
     * @return array  Retorna el html de las lista de meses y años de vencimiento
     */
    function generaFechavencimie()
    {

        $anno_inicio = date('Y') - 1;
        $anno_final = date('Y') + 10;

        $nombre_lista_mes = $_SESSION['name'] . " = \"FECHA_VENC_MES\" " . $_SESSION['id'] . " = \"FECHA_VENC_MES\" ";
        $nombre_lista_anno = $_SESSION['name'] . " = \"FECHA_VENC_ANNO\" " . $_SESSION['id'] . " = \"FECHA_VENC_ANNO\" ";

        $indice_mes = $_SESSION['index'] . " = \"10\"";
        $indice_anno = $_SESSION['index'] . " = \"11\"";

        $eventos_mes = "onchange=\"validarFechaVencim();\"";
        $eventos_anno = "onchange=\"$('#FECHA_VENC_MES').valid();\"";

        $datos_mes = Campos::generarListas(36, "", $nombre_lista_mes, "1", $indice_mes, "", "", "", "", "1", 1, "", "", $eventos_mes);
        $datos_anno = Campos::generarListas(37, "", $nombre_lista_anno, "1", $indice_anno, "", "AND (GT_VALORES.CODIGO = '-1' OR GT_VALORES.CODIGO BETWEEN '" . $anno_inicio . "' AND '" . $anno_final . "')", "", "", "1", 1, "", "", $eventos_anno);

        //Retorna el HTML que fue construido
        return array("mes" => $datos_mes, "anno" => $datos_anno);


    }

    /**
     * Permite generar la lista de franquicias con su respectivo Icono.
     *
     * @param  array $franquicia Lista de franquicias permitidas
     * @return String  Retorna el html en radio para las franquicias
     */
    function generaFranquicia($franquicia = "", $formaPago = "")
    {

        $html_prov = "";
        $condicion_lista = "";

        if ($formaPago == 6) {
            $condicion_lista .= "AND CODIGO IN('CODENSA')";
            $html_prov = "width=\"66px\" height=\"47px\" style=\"margin-right:80%;\"";
        } else {
            $condicion_lista .= "AND CODIGO <> 'CODENSA'";
        }

        $datos = Campos::generarListas(35, "", "", "1", "10", "", $condicion_lista, "", "", "1", 1, 1);

        $html = "";
        $lista_fran = ",";
        $i = 1;

        foreach ($datos as $llav => $fran) {

            foreach ($franquicia as $key => $val) {
                if (trim(substr($val['description'], 0, 10)) == $fran['CODIGO'] && strpos($lista_fran, $fran['CODIGO']) === false) {

                    $lista_fran .= $fran['CODIGO'] . ",";
                    $html .= "<label class=\"cards" . $active . " \"><input  ";
                    $html .= "type=\"radio\" name=\"TARJETA_CREDITO\" id=\"" . $fran['CODIGO'] . "\" value=\"" . $fran['CODIGO'] . "\" " . $checked . " required >";
                    $html .= "<img " . $html_prov . " src=\"imagenes/icono-" . $fran['CODIGO'] . ".svg\" alt=\"" . $fran['VALOR'] . "\"></label>";
                }

            }
        }

        //$html = $html."</div>";

        //Retorna el HTML que fue construido
        return $html;

    }


    /**
     * Permite generar la lista de bancos
     *
     * @param  array $datos Informacion enviada a PayU
     * @return array/boolean  Retorna un arreglo con la informacion que se devuelve de PayU
     */
    function generaBancos($bancos = "")
    {

        $consulta = new Consulta;

        $sele_bancos = "SELECT CODIGO FROM GT_VALORES WHERE LIST_NUMERO = 38 ";
        $consulta->setConsulta($sele_bancos);
        $lista_bancos = $consulta->ejecutarConsulta();

        $inact_val = "UPDATE GT_VALORES SET ACTIVO = '2', ELIMINADO = '-10', ORDEN = 1 WHERE LIST_NUMERO = 38 AND CODIGO <> '-1' AND ELIMINADO = -1 ";
        $consulta->setConsulta($inact_val);
        $consulta->ejecutarConsulta("noshow");

        $i = 1;

        foreach ($bancos as $key => $val) {
            $encontro = false;
            foreach ($lista_bancos as $lla => $dat) {

                if ($dat['CODIGO'] == $val['pseCode']) {
                    $acti_val = "UPDATE GT_VALORES SET VALOR_ES = '" . $val['description'] . "', ACTIVO = '1', ORDEN = " . $i . ", ELIMINADO = -1 WHERE LIST_NUMERO = 38 AND CODIGO = '" . $dat['CODIGO'] . "'";
                    $consulta->setConsulta($acti_val);
                    $consulta->ejecutarConsulta("noshow");
                    $encontro = true;
                }
            }

            if ($encontro == false) {
                $nuev_val = "INSERT INTO GT_VALORES (LIST_NUMERO, CODIGO, VALOR_ES, ORDEN, ACTIVO, USRIO_NUMERO, ELIMINADO) VALUES (";
                $nuev_val .= "38, '" . $val['pseCode'] . "', '" . $val['description'] . "', " . $i . ", '1', 1, -1)";
                $consulta->setConsulta($nuev_val);
                $consulta->ejecutarConsulta("noshow");
            }

            $i = $i + 1;

        }

        $consulta->desconectar();

        //Retorna el HTML que fue construido		
        return;

    }


    /**
     * Ajusta el formulario de confirmación cuando es TC.
     *
     * @param  array $campos Datos del formulario de confirmacion
     * @return array/boolean  Retorna un arreglo ajustando el formulario de confirmación.
     */
    function formulario($campos = "", $valores = "")
    {

        foreach ($campos as $key => $val) {
            if ($val['name'] == "BANCO") {
                $campos[$key]['etiqueta'] = "";
            }
        }

        return $campos;
    }


    /**
     * Ajusta el formulario de confirmación.
     *
     * @param  array $campos Datos del formulario de confirmacion
     * @return array/boolean  Retorna un arreglo ajustando el formulario de confirmación.
     */
    function sinIva($campos = "", $valores = "")
    {

        foreach ($campos as $key => $val) {

            if ($val['name'] == "IVA") {
                if ($valores['IVA'] == '$ 0') {
                    $campos[$key]['etiqueta'] = "";
                    $campos[$key]['html'] = str_replace($valores['IVA'], "", $campos[$key]['html']);
                }
            }

            if ($val['name'] == "VALOR") {
                if ($valores['IVA'] == '$ 0') {
                    $campos[$key]['etiqueta'] = "";
                    $campos[$key]['html'] = str_replace($valores['VALOR'], "", $campos[$key]['html']);
                }
            }

            if ($val['name'] == "VALOR_TOTAL") {
                $campos[$key]['etiqueta'] = str_replace("Valor Total", "Valor Total" . $valores['INCLU_IMPUESTO'], $campos[$key]['etiqueta']);
            }

            if ($val['name'] == "ASOCIAR_DOMICILIA") {

                if ($_SESSION['logueo_autenticacionTokenSSO'] != true) {
                    $campos[$key]['etiqueta'] = "";
                    $campos[$key]['html'] = "";
                }
            }
        }

        return $campos;
    }


    /**
     * Intentos por referencia
     *
     * @return Integer  Retorna la cantidad de intentos generados para una transaccion
     */
    function payuIntentos($numero)
    {

        $consulta = new Consulta;

        $intentos = "SELECT COUNT(1) + 1 AS CANTIDAD FROM GT_PAGO_PASARELA WITH (NOLOCK) WHERE NUMEROFACTURA = '";
        $intentos .= $numero . "' AND  ELIMINADO = -1 ";
        $consulta->setConsulta($intentos);
        $dato_intentos = $consulta->ejecutarConsulta();

        $consulta->desconectar();
        unset($consulta);

        return $dato_intentos[0]['CANTIDAD'];

    }

    /**
     * Valida la descripcion del estado
     *
     * @param  String $estado Codigo de estado a validar
     * @return Srting  Retorna el descriptor de la lista de valores
     */
    function validaEstados($estado)
    {

        $consulta = new Consulta;

        $consul_descrip = "SELECT VALOR" . $_SESSION['lang'] . " AS VALOR FROM GT_VALORES WHERE CODIGO = '" . $estado . "' AND LIST_NUMERO = 41 AND ACTIVO = '1' AND  ELIMINADO = -1 ";
        $consulta->setConsulta($consul_descrip);
        $dato_estado = $consulta->ejecutarConsulta();

        $consulta->desconectar();

        return $dato_estado[0]['VALOR'];

    }

    /**
     * Creacion token en payu
     *
     * @return array/boolean  Retorna la respuesta de conectividad con PayU.
     */
    function payuCreaToken($valores)
    {

        $datos_tarjeta = array("language" => $this->language, "command" => 'CREATE_TOKEN',
            "merchant" => array("apiLogin" => $this->apiLogin, "apiKey" => $this->apiKey),
            "creditCardToken" => array("payerId" => $valores['PAYER_ID'], "name" => $valores['TARJETA_HABIENTE'],
                "identificationNumber" => $valores['NUMERO_DOCTARH'], "paymentMethod" => $valores['FRANQUICIA'],
                "number" => $valores['NUMERO_TARJETA'], "expirationDate" => $valores['FECHA_VENCIMIENTO']));


        return $this->peticionPost($datos_tarjeta, $this->urlPayments);
    }

    /**
     * Eliminacion Token Payu
     *
     * @return array/boolean  Retorna la respuesta de conectividad con PayU.
     */
    function payuEliminaToken($valores)
    {

        $datos_token = array("language" => $this->language, "command" => 'REMOVE_TOKEN',
            "merchant" => array("apiLogin" => $this->apiLogin, "apiKey" => $this->apiKey),
            "removeCreditCardToken" => array("payerId" => $valores['PAYERID'], "creditCardTokenId" => $valores['TOKEN']));

        return $this->peticionPost($datos_token, $this->urlPayments);
    }

    public function pagar(Pago $pago)
    {
        $this->payuTransaccion($pago);
    }


    function maxPayUPay()
    {

        $this->apiKey = PAYUPAY_APIKEY; //Ingrese aquí su propio apiKey.
        $this->apiLogin = PAYUPAY_APILOGIN; //Ingrese aquí su propio apiLogin.
        $this->merchantId = PAYUPAY_MERCHANTID; //Ingrese aquí su Id de Comercio.        
        $this->apiKey_PSE = PAYUPAY_APIKEY_PSE; //Ingrese aquí su propio apiKey de PSE.
        $this->apiLogin_PSE = PAYUPAY_APILOGIN_PSE; //Ingrese aquí su propio apiLogin de PSE.
        $this->merchantId_PSE = PAYUPAY_MERCHANTID_PSE; //Ingrese aquí su Id de Comercio.        
        $this->language = SupportedLanguages::ES; //Seleccione el idioma.
        $this->isTest = (PAYUPAY_TEST == 1) ? true : false; //Dejarlo True cuando sean pruebas.
        $this->urlPayments = PAYUPAY_URL_PAYMENTS;
        $this->accountId = PAYUPAY_ACCOUNTID;
    }

    /**
     * Función que valida si un cliente es apto para la forma de pago PayU Te Fia
     * @param $valores
     * @return array
     */
    function payuValidacionPayuTeFia($datosCliente)
    {
        $this->setAccount($datosCliente['formaPago'], $datosCliente['idObjeto']);

        $datosCliente['valorTotal'] = trim(str_replace(",", ".", $datosCliente['valorTotal']));

        $datos = array("merchantId" => $this->merchantId,
            "accountId" => $this->accountId,
            "amount" =>$datosCliente['valorTotal'],
            "convertedAmount" => $datosCliente['valorTotal'],
            "currency" => $this->currency,
            "lendingDeviceToken" => "",
            "email" => $datosCliente['nombreUsuario']
        );

        //creamos el json a partir de nuestro arreglo
        $jsonData = json_encode($datos);

        $jsonData = str_replace("\/", "/", $jsonData);

        $httpHeader = array('Content-Type: application/json; charset=UTF-8',
            'Content-Length: '.strlen($jsonData),
            'Accept: application/json');

        //inicializamos el objeto CUrl
        $curl = curl_init(PAYU_URL_VALIDACION_PAYUTEFIA);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER,$httpHeader);
        curl_setopt($curl, CURLOPT_USERPWD, $this->apiLogin.":".$this->apiKey);

        #Ejecutamos la petición
        $curlResponse = curl_exec($curl);

        #Se obiene el codigo de estado de la solicitud
        $httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        #Se valida el codigo y respuesta del servicio
        if($curlResponse === false && $httpStatus === 0){
            return array('result'=> "error", 'mensaje' => "Error de conexión con PayU");
        }

        #Se valida el codigo y respuesta del servicio
        if ($curlResponse === false) {
            return array('result'=> "error", 'mensaje' => "Error de conexión con PayU");
        }

        #Se cierra la conexión
        curl_close($curl);

        $respuesta = json_decode($curlResponse, true);

        #Se escribe archivo de Log
        if (LOG_FILE == 1) {
            $trace = 'Peticion: ' . PHP_EOL . $jsonData . 'Respuesta PayUTeFia:' . PHP_EOL . json_encode($respuesta);
            $file = fopen("../tmp/payuTeFiaValidacion_".date('YmdHis').".txt", "w");
            fwrite($file, $trace);
            fclose($file);
        }

        $trace = 'Peticion: ' . PHP_EOL . $jsonData . 'Respuesta PayUTeFia:' . PHP_EOL . json_encode($respuesta);
        $file = fopen("../tmp/payuTeFiaValidacion_".date('YmdHis').".txt", "w");
        fwrite($file, $trace);
        fclose($file);

        #Se retorna el mensaje asociado a la validación luego de la respuesta del serivicio
        if(empty($curlResponse) || $respuesta['code'] == "ERROR"){
            return array('error'=> true, 'mensaje' => "En este momento no eres apto para este medio de pago" );
        } else if (isset($respuesta['paymentMethod']['errorCode'])) {
            if ($respuesta['paymentMethod']['errorCode'] == "CLIENT_HAS_LOANS_IN_ARREARS"
                || $respuesta['paymentMethod']['errorCode'] == "CLIENT_HAS_ACCOUNTS_CONCURRENT_LOAN_LIMIT_EXCEEDED") {
                return array('error' => true, 'mensaje' => "Para poder hacer uso de PayU te Fía, realiza el pago de tus facturas pendientes");
            } else if ($respuesta['paymentMethod']['errorCode'] == "CLIENT_BALANCE_NOT_ENOUGH") {
                return array('error' => true, 'mensaje' => "Tu cupo disponible es menor que el valor de esta compra");
            } else if ($respuesta['paymentMethod']['errorCode'] == "NON_ELIGIBLES_MAX_AMOUNT_EXCEEDED") {
                return array('error' => true, 'mensaje' => "Tu transacción supera el monto para usar PayU Te Fía");
            }
        }

        #Se retorna el mensaje asociado a la validación luego de la respuesta del serivicio
        return array('error'=> false, 'respuesta' => $respuesta, 'mensaje' => "Es apto para el medio de pago PayU Te Fía");
    }

}

?>
<?php


abstract class APagosClaro implements ITransaccionPago
{
    public function searchPagoById($id)
    {
        $consulta = new Consulta();

        $queryPago = "SELECT C.CLACO_NUMERO, C.NRO_CUENTA, C.NumeroFactura, C.FechaFactura, C.NombreCliente, ";
        $queryPago .= "C.ValorTotal, C.Subtotal, C.Iva, C.OrigenPago, C.TIPO_TRANS, C.ESTADO_SRV, C.FechaVencimiento, ";
        $queryPago .= "C.FECHA_INICIO, C.FORMA_PAGO FROM CL_PAGOSCLARO C WITH (NOLOCK) WHERE C.CLACO_NUMERO = '" . $id;
        $queryPago .= "' ORDER BY C.CLACO_NUMERO DESC";

        $consulta->setConsulta($queryPago);

        $pago = $consulta->ejecutarConsulta();

        if ($consulta->numeroFilas() > 0) {
            $retorno = $pago[0];
        } else {
            $retorno = "No existen pagos con ese ID";
        }

        $consulta->desconectar();

        unset($consulta);

        return $retorno;
    }

    public function updatePago($id)
    {

    }

    public function updateEstadoPago($idTransaccion, $estadoPago, $fechaTransaccion)
    {
        $consulta = new Consulta;

        $buscarFormaPago  = "SELECT FORMA_PAGO FROM GT_PAGO_PASARELA WITH (NOLOCK) WHERE ID_TRANSACCION";
        $buscarFormaPago .= " = ".$idTransaccion." ORDER BY PASA_NUMERO DESC ";

        $consulta->setConsulta($buscarFormaPago);
        $formaPago = $consulta->ejecutarConsulta();

        $actualiza = '';
        if( $formaPago[0]['FORMA_PAGO'] != '' ){
            $actualiza = ", FORMA_PAGO = " . $formaPago[0]['FORMA_PAGO'];
        }

        $actuelimnd = '';
        if( $estadoPago == 'APPROVED' ){
            $actuelimnd = ", ELIMINADO = -1";
        }

        $fechaTransaccion = $consulta->getLink()->DBtimestamp($fechaTransaccion.":".date("s"));

        $actualizarPago  = "UPDATE CL_PAGOSCLARO SET ESTADO_PAGO = '" . $estadoPago . "' " . $actualiza;
        $actualizarPago .= ", FECHAHORA = " . $fechaTransaccion . " " . $actuelimnd . " ";
        $actualizarPago .= "WHERE CLACO_NUMERO = " . $idTransaccion;

        $consulta->setConsulta($actualizarPago);
        $consulta->ejecutarConsulta("noshow");

        $resultadoInsert = $consulta->getResultado();

        return ($resultadoInsert == 2);
    }

    public abstract function insertPago(Pago $pago);

    public function searchEstadoPagoByFactura($numeroFactura)
    {
        $consulta = new Consulta();

        $queryEstado  = "SELECT B.ESTADO FROM CL_PAGOSCLARO A, GT_PAGO_PASARELA B WITH (NOLOCK) WHERE A.CLACO_NUMERO = B.ID_TRANSACCION ";
        $queryEstado .= "AND A.NUMEROFACTURA = '".$numeroFactura."' AND A.ELIMINADO = -1 AND B.ELIMINADO = -1 ";
        $queryEstado .= "AND B.ESTADO IN ('APPROVED', 'PENDING') ORDER BY B.PASA_NUMERO DESC";

        $consulta->setConsulta($queryEstado);

        $estado = $consulta->ejecutarConsulta();

        if ($consulta->numeroFilas() > 0) {
            $estadoPago = ($estado[0]["ESTADO"] == "APPROVED") ? "APROBADA." : "PENDIENTE.";
            $mensaje  = "En este momento su factura " . $numeroFactura . " presenta un proceso de pago cuya transacción ";
            $mensaje .= "se encuentra " . $estadoPago;

            $retorno["estado"] = $mensaje;
        } else {
            $retorno = false;
        }

        $consulta->desconectar();
        unset($consulta);

        return $retorno;
    }

    protected function getLastID($numeroFactura, $tipoTrans)
    {
        $consulta = new Consulta();

        //SELECT MAX(CLACO_NUMERO) AS CLACO_NUMERO FROM CL_PAGOSCLARO WITH (NOLOCK) WHERE  NumeroFactura = '".$valores['NumeroFactura']."'
        // AND TIPO_TRANS = '".$valores['TIPO_TRANS']."' AND ELIMINADO = -1 AND ESTADO_PAGO = '-1'";

        $queryPago = "SELECT MAX(CLACO_NUMERO) AS ID FROM CL_PAGOSCLARO WITH (NOLOCK) WHERE NumeroFactura = '" . $numeroFactura;
        $queryPago .= "' AND TIPO_TRANS = '" . $tipoTrans . "' AND ELIMINADO = -1 AND ESTADO_PAGO = '-1'";

        $consulta->setConsulta($queryPago);

        $ID = $consulta->ejecutarConsulta()[0]["ID"];

        $consulta->desconectar();

        unset($consulta);

        return ($ID);
    }

    # Métodos para procesar el pago en GT_PAGO_PASARELA
    public function insertTransaccionDebito(Array $datosPago, TarjetaDebito $medioPago)
    {
        $consulta = new Consulta();

        //$datosPago = $pagoHogar->getDatosPago();
        $datosMedioPago = $medioPago->getDatosMedioPago();

        //INSERT INTO GT_PAGO_PASARELA (BANCO ,TITULAR ,TIPO_CLIENTE ,TIPO_DOCUMENTO
        // ,NUMERO_DOCUMENTO ,TELEFONO ,EMAIL ,NUMEROFACTURA
        // ,DESCRIPCION_COMPRA ,VALOR_TOTAL ,USRIO_NUMERO ,FORMA_PAGO ,ID_TRANSACCION ,
        //VALOR ,IVA ,ORIGEN_PAGO ,PARAM_SETACCOUNT ) VALUES
        // ('1081','Edgarrr','N','CC','123',123123,'a@c.com','842502668','Pago factura hogar - Multiplay','$ 105.765',1,'1',4310856,NULL,NULL,'3',NULL)

        $queryGTPagos = "INSERT INTO GT_PAGO_PASARELA (BANCO, TITULAR, TIPO_CLIENTE, TIPO_DOCUMENTO, ";
        $queryGTPagos .= "NUMERO_DOCUMENTO, TELEFONO, EMAIL, NUMEROFACTURA, ";
        $queryGTPagos .= "DESCRIPCION_COMPRA, VALOR_TOTAL, USRIO_NUMERO, FORMA_PAGO, ID_TRANSACCION, ";
        $queryGTPagos .= "VALOR, IVA, ORIGEN_PAGO, PARAM_SETACCOUNT) VALUES ('";
        $queryGTPagos .= $datosMedioPago["banco"] . "','" . $datosMedioPago["nombre"] . "','" . $datosMedioPago["tipoCliente"];
        $queryGTPagos .= "','" . $datosMedioPago["tipoDocumento"] . "','" . $datosMedioPago["numeroDocumento"] . "'," . $datosMedioPago["telefono"];
        $queryGTPagos .= ",'" . $datosMedioPago["email"] . "','" . $datosPago["numeroFactura"] . "','" . $datosPago["descripcion"];
        $queryGTPagos .= "','" . $datosPago["valorTotal"] . "', 1, '" . $datosMedioPago["formaPago"] . "'," . $datosPago["IDTransaccion"];
        $queryGTPagos .= ",'" . $datosPago["valorTotal"] . "','" . $datosPago["iva"] . "','". $datosPago["origenPago"] ."',NULL)";

        $consulta->setConsulta($queryGTPagos);

        $consulta->ejecutarConsulta();

        $resultadoInsert = $consulta->getResultado();

        return ($resultadoInsert == 2);
    }

    public function updateTransaccionDebito($id, $datosUpdate)
    {
        $consulta = new Consulta();

        $fecha = $consulta->getLink()->DBtimestamp(date('Y-m-d'." ".date("H").":".date("i").":".date("s")));

        $actu_pago = "UPDATE GT_PAGO_PASARELA SET ";
        $actu_pago .= "FECHA_TRANSACCION = " . $fecha . ", ESTADO = '" . $datosUpdate["estado"] . "', ";
        $actu_pago .= "CUS = '" . $datosUpdate['CUS'] . "' WHERE PASA_NUMERO = " . $id;

        $consulta->setConsulta($actu_pago);

        $consulta->ejecutarConsulta();

        $resultadoInsert = $consulta->getResultado();

        return ($resultadoInsert == 2);
    }

    public function insertTransaccionCredito(Array $datosPago, TarjetaCredito $medioPago)
    {
        $consulta = new Consulta();

        //$datosPago = $pagoHogar->getDatosPago();
        $datosMedioPago = $medioPago->getDatosMedioPago();

        $queryGTPagos = "INSERT INTO GT_PAGO_PASARELA (TARJETA_CREDITO, NUMERO_TARJETA, FECHA_VENC, CUOTAS, ";
        $queryGTPagos .= "NOMBRE_TARJETA, TIPO_DOCUMENTO, NUMERO_DOCUMENTO, TELEFONO, EMAIL, NUMEROFACTURA, ";
        $queryGTPagos .= "DESCRIPCION_COMPRA, VALOR_TOTAL, ASOCIAR_CLPAY, ASOCIAR_DOMICILIA, FORMA_PAGO, ";
        $queryGTPagos .= "ID_TRANSACCION, VALOR, IVA, ORIGEN_PAGO, PARAM_SETACCOUNT) VALUES ('";
        $queryGTPagos .= $datosMedioPago["franquicia"] . "'," . $datosMedioPago["numero"] . ",'" . $datosMedioPago["anoVencimiento"]."/".$datosMedioPago["mesVencimiento"];
        $queryGTPagos .= "','" . $datosMedioPago["cuotas"] . "','" . $datosMedioPago["nombre"] . "','" . $datosMedioPago["tipoDocumento"];
        $queryGTPagos .= "','" . $datosMedioPago["numeroDocumento"] . "'," . $datosMedioPago["telefono"];
        $queryGTPagos .= ",'" . $datosMedioPago["email"] . "','" . $datosPago["numeroFactura"] . "','" . $datosPago["descripcion"];
        $queryGTPagos .= "','" . $datosPago["valorTotal"] . "','2','2','" . $datosMedioPago["formaPago"] . "',";
        $queryGTPagos .= $datosPago["IDTransaccion"] . ",NULL,NULL,'" . $datosPago["origenPago"] . "',NULL)";

        $consulta->setConsulta($queryGTPagos);

        $consulta->ejecutarConsulta();

        $resultadoInsert = $consulta->getResultado();

        return ($resultadoInsert == 2);
    }

    public function insertTransaccionTokenizada(Array $datosPago, TarjetaTokenizada $medioPago)
    {
        $consulta = new Consulta();

        //$datosPago = $pagoHogar->getDatosPago();
        $datosMedioPago = $medioPago->getDatosMedioPago();

        //INSERT INTO GT_PAGO_PASARELA (ID_TRANSACCION, NUMEROFACTURA, DESCRIPCION_COMPRA, VALOR,
        // VALOR_TOTAL, IVA, TARJETA_CREDITO, CUOTAS, EMAIL, USRIO_NUMERO, ELIMINADO, FORMA_PAGO,
        // FECHA_TRANSACCION, ORIGEN_PAGO, PARAM_SETACCOUNT, TELEFONO, NOMBRE_TARJETA,
        // NUMERO_DOCUMENTO, INTENTOS) VALUES (4322808, '842502668', 'Pago factura hogar - Multiplay', 105765, 105765, 0, 'VISA', '11', 'leon_ct@hotmail.com', 1, -1, '5',
        // '2018-12-05 16:21:06', '3', '', '', 'APPROVED', '1105673739', 4)

        $queryGTPagos = "INSERT INTO GT_PAGO_PASARELA (ID_TRANSACCION, NUMEROFACTURA, DESCRIPCION_COMPRA, VALOR, ";
        $queryGTPagos .= "VALOR_TOTAL, IVA, TARJETA_CREDITO, CUOTAS, EMAIL, USRIO_NUMERO, ELIMINADO, FORMA_PAGO, ";
        $queryGTPagos .= "FECHA_TRANSACCION, ORIGEN_PAGO, PARAM_SETACCOUNT, TELEFONO, NOMBRE_TARJETA, ";
        $queryGTPagos .= "NUMERO_DOCUMENTO, INTENTOS) VALUES (";
        $queryGTPagos .= $datosPago["IDTransaccion"] . ",'" . $datosPago["numeroFactura"] . "','" . $datosPago["descripcion"];
        $queryGTPagos .= "','" . $datosPago["valorTotal"] . "','" . $datosPago["valorTotal"] . "','" . $datosPago["iva"];
        $queryGTPagos .= "','" . $datosMedioPago["franquicia"] . "'," . $datosMedioPago["cuotas"];
        $queryGTPagos .= ",'" . $datosMedioPago["email"] . "',1,-1,'".$datosMedioPago["formaPago"]."','" . date("Y-m-d H:i:s") . "','" . $datosPago["origenPago"];
        $queryGTPagos .= "','','" . $datosMedioPago["telefono"] . "','" . $datosMedioPago["nombre"] . "','";
        $queryGTPagos .= $datosMedioPago["numeroDocumento"] . "'," . $datosPago["numeroIntentos"] . ")";

        $consulta->setConsulta($queryGTPagos);

        $consulta->ejecutarConsulta();

        $resultadoInsert = $consulta->getResultado();

        return ($resultadoInsert == 2);
    }

    public function insertTransaccionPayUTeFia(array $pago, PayUTeFia $medioPago)
    {
        $consulta = new Consulta();

        #INSERT INTO GT_PAGO_PASARELA (ACEPTA_TERMINOS, TITULAR, TELEFONO, EMAIL, TIPO_DOCUMENTO, NUMERO_DOCUMENTO,
        #NUMEROFACTURA, DESCRIPCION_COMPRA, VALOR_TOTAL, USRIO_NUMERO, FORMA_PAGO, ID_TRANSACCION, VALOR, IVA, ORIGEN_PAGO )
        # VALUES ('1','234 JOHN FREDY MARIN CORTAZAR',3133603621,'edgar.vargas@infodeclat.com','CC','123456','845019069',
        #'Pago factura hogar - Multiplay','$ 105.765',1,'9',4311473,NULL,NULL,'2')

        $queryGTPagos  = "INSERT INTO GT_PAGO_PASARELA (ACEPTA_TERMINOS, TITULAR, TELEFONO, EMAIL, TIPO_DOCUMENTO, ";
        $queryGTPagos .= "NUMERO_DOCUMENTO, NUMEROFACTURA, DESCRIPCION_COMPRA, VALOR_TOTAL, USRIO_NUMERO, FORMA_PAGO, ";
        $queryGTPagos .= "ID_TRANSACCION, VALOR, IVA, ORIGEN_PAGO ) VALUES ('1', '" . $pago["nombreCliente"] . "', ";
        $queryGTPagos .= $pago["numeroCelular"] . ", '" ;
    }

    public function searchTransaccionById($idTransaccion)
    {
    }
}
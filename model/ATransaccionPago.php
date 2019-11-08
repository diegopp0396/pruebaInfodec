<?php

abstract class ATransaccionPago
{
    public function insertTransaccionDebito(Pago $pagoHogar, TarjetaDebito $medioPago)
    {
        $consulta = new Consulta();

        $datosPago = $pagoHogar->getDatosPago();
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

    public function insertTransaccionCredito(Pago $pagoHogar, TarjetaCredito $medioPago)
    {
        $consulta = new Consulta();

        $datosPago = $pagoHogar->getDatosPago();
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

    public function insertTransaccionTokenizada(Pago $pagoHogar, TarjetaTokenizada $medioPago)
    {
        $consulta = new Consulta();

        $datosPago = $pagoHogar->getDatosPago();
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

    public function searchTransaccionById($idTransaccion)
    {
        $consulta = new Consulta();

        $queryPago = "SELECT C.CLACO_NUMERO, C.NRO_CUENTA, C.NumeroFactura, C.FechaFactura, C.NombreCliente, ";
        $queryPago .= "C.ValorTotal, C.Subtotal, C.Iva, C.OrigenPago, C.TIPO_TRANS, C.ESTADO_SRV, C.FechaVencimiento, ";
        $queryPago .= "C.FECHA_INICIO, C.FORMA_PAGO, G.EMPRESA, G.NIT, G.CUS, G.IP_ORIGEN, G.ESTADO, G.FECHA_TRANSACCION, ";
        $queryPago .= "G.ID_ORDEN FROM CL_PAGOSCLARO C WITH (NOLOCK) INNER JOIN GT_PAGO_PASARELA G ";
        $queryPago .= "ON G.ID_TRANSACCION = C.CLACO_NUMERO WHERE C.CLACO_NUMERO = '" . $idTransaccion;
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
}
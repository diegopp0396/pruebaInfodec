<?php

class PagoMovilDAO extends APagosClaro
{
    # Se define el método para insertar el pago en CL_PAGOS_CLARO
    public function insertPago(Pago $pagoPostago)
    {
        $consulta = new Consulta();

        # INSERT INTO CL_PAGOSCLARO (NumeroCelular ,FORMA_PAGO ,CVV ,EMAIL ,CONSOL_PARCIAL ,CONSOL_DIARIO ,IdMiClaroAPP ,NRO_CUENTA ,FACTURAORIG ,TIPO_TRANS ,USRIO_NUMERO
        #,FECHA_TOKEN ,NOMBREUSUARIO_TOKEN ,CANAL_TOKEN ,AUTENTICACION_TOKEN ,VALIDACION_TOKEN ,TIPO_TRANS_ORIG ,NUMEROINTENTOS ,ESTADO_SRV ,ERROR_SRV ,ESTADO_PAGO
        #,FECHA_INICIO ,FechaHora ,Token ,NumeroIdentificacion ,NombreCliente ,CodigoCliente ,NumeroFactura ,FechaFactura ,IdentificacionDeudor ,OrigenPago ,FechaVencimiento
        #,Subtotal ,Iva ,Impoconsumo ,Propina ,ValorTotal ) VALUES ('3133603621','2',NULL,NULL,'-1','-1',NULL,NULL,NULL,'2',1,NULL,'leon_ct@hotmail.com',NULL,NULL,NULL,'2',
        #NULL,'6',NULL,'-1','2019-01-02 18:49:12',NULL,NULL,NULL,'Sr. EDGAR CAMILO VARGAS ALDANA','1173292793','5254710438',NULL,'-1','3','2019-01-02 18:50:12',
        #36492.61,0,NULL,NULL,36492.61)

        $queryPago = "INSERT INTO CL_PAGOSCLARO (NumeroCelular, FORMA_PAGO, CVV, EMAIL, CONSOL_PARCIAL, ";
        $queryPago .= "CONSOL_DIARIO, IdMiClaroAPP, NRO_CUENTA, FACTURAORIG, TIPO_TRANS, USRIO_NUMERO, ";
        $queryPago .= "FECHA_TOKEN, NOMBREUSUARIO_TOKEN, CANAL_TOKEN, AUTENTICACION_TOKEN, VALIDACION_TOKEN, TIPO_TRANS_ORIG, ";
        $queryPago .= "NUMEROINTENTOS, ESTADO_SRV, ERROR_SRV, ESTADO_PAGO, FECHA_INICIO, FechaHora, Token, NumeroIdentificacion, ";
        $queryPago .= "NombreCliente, CodigoCliente, NumeroFactura, FechaFactura, IdentificacionDeudor, OrigenPago, ";
        $queryPago .= "FechaVencimiento, Subtotal, Iva, Impoconsumo, Propina, ValorTotal)";
        $queryPago .= "VALUES ('" . $pagoPostago->getNumeroCuenta() . "', '" . $pagoPostago->getMedioPago()->getFormaPago() . "', NULL, NULL, ";
        $queryPago .= "'-1', '-1', NULL, NULL, NULL, '" . $pagoPostago->getTipoTrans() . "', 1, NULL, '" . $pagoPostago->getNombreUsuario();// getMedioPago()->getFormaPago();
        $queryPago .= "', NULL, NULL, NULL, '" . $pagoPostago->getTipoTrans() . "', NULL, '-1', NULL, '-1', '";
        $queryPago .= $pagoPostago->getFechaInicio() . "', '" . $pagoPostago->getFechaInicio() . "', NULL, NULL, NULL, '";
        $queryPago .= $pagoPostago->getNumeroReferencia() . "', '" . $pagoPostago->getNumeroFactura() . "', NULL, '-1' , '";
        $queryPago .= $pagoPostago->getOrigenPago() . "', '" . $pagoPostago->getFechaVencimiento() . "', " . $pagoPostago->getValorTotal();
        $queryPago .= ", " . $pagoPostago->getIva() . ", NULL, NULL, " . $pagoPostago->getValorTotal() . ")";
        /*$queryPago .= ", " . $pagoHogar->getTipoTrans() . ", " . $pagoHogar->getEstadoSRV() . ", '" . $pagoHogar->getFechaInicio();
        $queryPago .= "', " . $pagoHogar->getEstadoSRV() . ", '" .  $pagoHogar->getFechaInicio() . "', " . $pagoHogar->getNumeroFactura();
        $queryPago .= ", '" . $pagoHogar->getFechaVencimiento() . "', '" . $pagoHogar->getFechaVencimiento() . "', ";
        $queryPago .= $pagoHogar->getNumeroFactura() . ", '" . $pagoHogar->getNombreCliente() . "', ";
        $queryPago .= $pagoHogar->getSubTotal() . ", " . $pagoHogar->getIva() . ", " . $pagoHogar->getValorTotal();
        $queryPago .= ", " . $pagoHogar->getOrigenPago() . ", '" . Utils::getNavegadorOS() . "')";*/

        $consulta->setConsulta($queryPago);

        $consulta->ejecutarConsulta();

        $resultadoInsert = $consulta->getResultado();

        $consulta->desconectar();

        unset($consulta);

        if ($resultadoInsert == 2) {
            return array("ID" => $this->getLastID($pagoPostago->getNumeroFactura(), $pagoPostago->getTipoTrans()));
        } else {
            $resultado = "Error al insertar en CL_PAGOSCLARO";
            return array("error" => true, "mensaje" => $resultado);
        }

    }

    # Se define la búsqueda de transacción por tipo de Flujo
    public function searchTransaccionById($idTransaccion)
    {
        $consulta = new Consulta();

        $queryPago = "SELECT C.CLACO_NUMERO, C.NUMEROCELULAR AS NumeroCelular, C.CodigoCliente AS numeroReferencia, C.NumeroFactura, ";
        $queryPago .= "C.FechaFactura, C.NombreCliente, C.ValorTotal, C.Subtotal, C.Iva, C.OrigenPago, C.TIPO_TRANS, C.ESTADO_SRV, ";
        $queryPago .= "C.FechaVencimiento, C.FECHA_INICIO, C.FORMA_PAGO, G.EMPRESA, G.NIT, G.CUS, G.IP_ORIGEN, G.ESTADO, ";
        $queryPago .= "G.FECHA_TRANSACCION, G.ID_ORDEN FROM CL_PAGOSCLARO C WITH (NOLOCK) INNER JOIN GT_PAGO_PASARELA G ";
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
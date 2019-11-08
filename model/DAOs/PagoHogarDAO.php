<?php

class PagoHogarDAO extends APagosClaro
{
    # Se define el método para insertar el pago en CL_PAGOS_CLARO
    public function insertPago(Pago $pagoHogar)
    {
        $consulta = new Consulta();

        $queryPago = "INSERT INTO CL_PAGOSCLARO (NRO_CUENTA, VALOR, FORMA_PAGO, TIPO_TRANS, ESTADO_SRV, ";
        $queryPago .= "FECHA_INICIO, ERROR_SRV, FechaHora, NumeroFactura, FechaFactura, FechaVencimiento, ";
        $queryPago .= "NumeroIdentificacion, NombreCliente, Subtotal, Iva, ValorTotal, OrigenPago, AUDIT_NAVEGADOR) ";
        $queryPago .= "VALUES (" . $pagoHogar->getNumeroCuenta() . ", " . $pagoHogar->getValorTotal() . ", " . $pagoHogar->getMedioPago()->getFormaPago();
        $queryPago .= ", " . $pagoHogar->getTipoTrans() . ", " . $pagoHogar->getEstadoSRV() . ", '" . $pagoHogar->getFechaInicio();
        $queryPago .= "', " . $pagoHogar->getEstadoSRV() . ", '" .  $pagoHogar->getFechaInicio() . "', " . $pagoHogar->getNumeroFactura();
        $queryPago .= ", '" . $pagoHogar->getFechaVencimiento() . "', '" . $pagoHogar->getFechaVencimiento() . "', ";
        $queryPago .= $pagoHogar->getNumeroFactura() . ", '" . $pagoHogar->getNombreCliente() . "', ";
        $queryPago .= $pagoHogar->getSubTotal() . ", " . $pagoHogar->getIva() . ", " . $pagoHogar->getValorTotal();
        $queryPago .= ", " . $pagoHogar->getOrigenPago() . ", '" . Utils::getNavegadorOS() . "')";

        $consulta->setConsulta($queryPago);

        $consulta->ejecutarConsulta();

        $resultadoInsert = $consulta->getResultado();

        $consulta->desconectar();

        unset($consulta);

        if ($resultadoInsert == 2) {
            return array("ID" => $this->getLastID($pagoHogar->getNumeroFactura(), $pagoHogar->getTipoTrans()));
        } else {
            $resultado = "Error al insertar en CL_PAGOSCLARO";
            return array("error" => true, "mensaje" => $resultado);
        }

    }

    # Se define la búsqueda de transacción por tipo de Flujo
    public function searchTransaccionById($idTransaccion)
    {
        $consulta = new Consulta();

        $queryPago = "SELECT C.CLACO_NUMERO, C.NRO_CUENTA, C.NumeroFactura, C.FechaFactura, C.NombreCliente, ";
        $queryPago .= "C.ValorTotal, C.Subtotal, C.Iva, C.OrigenPago, C.TIPO_TRANS, C.ESTADO_SRV, C.FechaVencimiento, ";
        $queryPago .= "C.FECHA_INICIO, C.FORMA_PAGO, G.EMPRESA, G.NIT, G.CUS, G.IP_ORIGEN, G.ESTADO, G.FECHA_TRANSACCION, ";
        $queryPago .= "G.ID_ORDEN FROM CL_PAGOSCLARO C WITH (NOLOCK) INNER JOIN GT_PAGO_PASARELA G ";
        $queryPago .= "ON G.ID_TRANSACCION = C.CLACO_NUMERO WHERE C.CLACO_NUMERO = '" . $idTransaccion;
        $queryPago .= "' ORDER BY G.PASA_NUMERO DESC";

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
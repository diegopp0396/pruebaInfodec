<?php

class PagoDAO extends APagosClaro
{
    public function searchPagoById($id)
    {
        $consulta = new Consulta();

        $queryPago  = "SELECT C.CLACO_NUMERO AS IDTransaccion, C.CodigoCliente, C.NumeroCelular, C.NRO_CUENTA, ";
        $queryPago .= "C.NumeroFactura AS numeroFactura, C.FechaFactura, C.ValorTotal AS valorTotal, C.NombreCliente, ";
        $queryPago .= "C.Valor, C.Iva AS iva, C.OrigenPago AS origenPago, C.TIPO_TRANS, C.FechaVencimiento, ";
        $queryPago .= "C.FECHA_INICIO, C.FORMA_PAGO FROM CL_PAGOSCLARO C WITH (NOLOCK) ";
        $queryPago .= "WHERE C.CLACO_NUMERO = '" . $id . "' ORDER BY C.CLACO_NUMERO DESC";

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

    # Se crea esta consulta para traer info y enviar notificaciones de pago
    public function searchPagoTransaccionById($id)
    {
        $consulta = new Consulta();

        $queryPago  = "SELECT C.CLACO_NUMERO AS IDTransaccion, C.CodigoCliente, C.NumeroCelular, C.NRO_CUENTA, ";
        $queryPago .= "C.NumeroFactura AS numeroFactura, C.FechaFactura, C.ValorTotal AS valorTotal, C.NombreCliente, ";
        $queryPago .= "C.Valor, C.Iva AS iva, C.OrigenPago AS origenPago, C.TIPO_TRANS, C.FechaVencimiento, G.TELEFONO, ";
        $queryPago .= "C.FECHA_INICIO, C.FORMA_PAGO, G.EMAIL FROM CL_PAGOSCLARO C WITH (NOLOCK) INNER JOIN ";
        $queryPago .= "GT_PAGO_PASARELA G ON G.ID_TRANSACCION = C.CLACO_NUMERO WHERE C.CLACO_NUMERO = '" . $id;
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

    public function insertPago(Pago $pagoHogar)
    {
        return;
    }
}
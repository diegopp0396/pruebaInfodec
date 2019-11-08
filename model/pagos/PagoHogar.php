<?php

class PagoHogar extends Pago
{

    /**
     * PagoHogar constructor.
     */
    public function __construct($numeroCuenta, $numeroFactura, $fechaLimitePago, $nombreCliente, $nombreUsuario, $medioPago, $valorTotal, $subTotal, $iva, $origenPago, $tipoTrans, $estadoSRV, $fechaVencimiento, $fechaInicio, $numeroIntentos)
    {
        parent::__construct($numeroCuenta, $numeroFactura, $fechaLimitePago, $nombreCliente, $nombreUsuario, $medioPago, $valorTotal, $subTotal, $iva, $origenPago, $tipoTrans, $estadoSRV, $fechaVencimiento, $fechaInicio, $numeroIntentos);
        $this->descripcion = "Pago Factura Hogar";
    }

}
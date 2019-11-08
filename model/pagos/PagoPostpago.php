<?php

class PagoPostpago extends Pago
{

    private $numeroReferencia;

    /**
     * PagoPostpago constructor.
     */
    public function __construct($numeroCuenta, $numeroFactura, $numeroReferencia, $fechaLimitePago, $nombreCliente,
                                $nombreUsuario, $medioPago, $valorTotal, $subTotal, $iva, $origenPago, $tipoTrans,
                                $estadoSRV, $fechaVencimiento, $fechaInicio, $numeroIntentos)
    {
        parent::__construct($numeroCuenta, $numeroFactura, $fechaLimitePago, $nombreCliente, $nombreUsuario, $medioPago,
            $valorTotal, $subTotal, $iva, $origenPago, $tipoTrans, $estadoSRV, $fechaVencimiento, $fechaInicio,
            $numeroIntentos);
        $this->numeroReferencia = $numeroReferencia;
        $this->descripcion = "Pago Factura Postpago";
    }

    /**
     * @return mixed
     */
    public function getNumeroReferencia()
    {
        return $this->numeroReferencia;
    }

    public function getDatosPago()
    {
        $datosPago = array();

        $datosPago["IDTransaccion"] = $this->getIDTransaccion();
        $datosPago["numeroFactura"] = $this->getNumeroFactura();
        $datosPago["valorTotal"] = $this->getValorTotal();
        $datosPago["iva"] = $this->getIva();
        $datosPago["moneda"] = $this->getMoneda();
        $datosPago["numeroIntentos"] = $this->getNumeroIntentos();
        $datosPago["descripcion"] = $this->getDescripcion();
        $datosPago["origenPago"] = $this->getOrigenPago();
        $datosPago["registroPago"] = $this->getRegistroPago();
        $datosPago["numeroReferencia"] = $this->getNumeroReferencia();
        $datosPago["numeroCuenta"] = $this->getNumeroCuenta();

        return $datosPago;
    }
}
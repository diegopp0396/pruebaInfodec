<?php

class Pago
{
    private $IDTransaccion;
    private $numeroCuenta;
    private $numeroFactura;
    private $fechaLimitePago;
    private $nombreCliente;
    private $nombreUsuario;
    private $medioPago; //private $formaPago;
    private $moneda = "COP";
    private $valorTotal;
    private $subTotal;
    private $iva;
    private $origenPago;
    private $tipoTrans;
    private $estadoSRV;
    private $numeroIntentos;
    private $fechaVencimiento; // Igual a la fecha de vencimiento
    private $fechaInicio; // Cuando inicia el flujo
    private $registroPago;

    public $descripcion;

    /**
     * Pago constructor.
     * @param $numeroCuenta
     * @param $numeroFactura
     * @param $fechaLimitePago
     * @param $nombreCliente
     * @param $nombreUsuario
     * @param $medioPago
     * @param $valorTotal
     * @param $subTotal
     * @param $iva
     * @param $origenPago
     * @param $tipoTrans
     * @param $estadoSRV
     * @param $fechaVencimiento
     * @param $fechaInicio
     * @param $numeroIntentos
     */
    public function __construct($numeroCuenta, $numeroFactura, $fechaLimitePago, $nombreCliente,
                                $nombreUsuario, MedioPago $medioPago, $valorTotal, $subTotal, $iva, $origenPago,
                                $tipoTrans, $estadoSRV, $fechaVencimiento, $fechaInicio, $numeroIntentos)
    {
        $this->numeroCuenta = $numeroCuenta;
        $this->numeroFactura = $numeroFactura;
        $this->fechaLimitePago = $fechaLimitePago;
        $this->nombreCliente = $nombreCliente;
        $this->nombreUsuario = $nombreUsuario;
        $this->medioPago = $medioPago;
        $this->valorTotal = $valorTotal;
        $this->subTotal = $subTotal;
        $this->iva = $iva;
        $this->origenPago = $origenPago;
        $this->tipoTrans = $tipoTrans;
        $this->estadoSRV = $estadoSRV;
        $this->fechaVencimiento = $fechaVencimiento;
        $this->fechaInicio = $fechaInicio;
        $this->numeroIntentos = $numeroIntentos;
    }

    /**
     * @return mixed
     */
    public function getNumeroCuenta()
    {
        return $this->numeroCuenta;
    }

    /**
     * @param mixed $numeroCuenta
     */
    public function setNumeroCuenta($numeroCuenta)
    {
        $this->numeroCuenta = $numeroCuenta;
    }

    /**
     * @return mixed
     */
    public function getNumeroFactura()
    {
        return $this->numeroFactura;
    }

    /**
     * @param mixed $numeroFactura
     */
    public function setNumeroFactura($numeroFactura)
    {
        $this->numeroFactura = $numeroFactura;
    }

    /**
     * @return mixed
     */
    public function getFechaLimitePago()
    {
        return $this->fechaLimitePago;
    }

    /**
     * @param mixed $fechaLimitePago
     */
    public function setFechaLimitePago($fechaLimitePago)
    {
        $this->fechaLimitePago = $fechaLimitePago;
    }

    /**
     * @return mixed
     */
    public function getNombreCliente()
    {
        return $this->nombreCliente;
    }

    /**
     * @param mixed $nombreCliente
     */
    public function setNombreCliente($nombreCliente)
    {
        $this->nombreCliente = $nombreCliente;
    }

    /**
     * @return mixed
     */
    public function getNombreUsuario()
    {
        return $this->nombreUsuario;
    }

    /**
     * @param mixed $nombreUsuario
     */
    public function setNombreUsuario($nombreUsuario)
    {
        $this->nombreUsuario = $nombreUsuario;
    }

    /**
     * @return mixed
     */
    public function getValorTotal()
    {
        return $this->valorTotal;
    }

    /**
     * @param mixed $valorTotal
     */
    public function setValorTotal($valorTotal)
    {
        $this->valorTotal = $valorTotal;
    }

    /**
     * @return mixed
     */
    public function getSubTotal()
    {
        return $this->subTotal;
    }

    /**
     * @param mixed $subTotal
     */
    public function setSubTotal($subTotal)
    {
        $this->subTotal = $subTotal;
    }

    /**
     * @return mixed
     */
    public function getIva()
    {
        return $this->iva;
    }

    /**
     * @param mixed $iva
     */
    public function setIva($iva)
    {
        $this->iva = $iva;
    }

    /**
     * @return mixed
     */
    public function getOrigenPago()
    {
        return $this->origenPago;
    }

    /**
     * @param mixed $origenPago
     */
    public function setOrigenPago($origenPago)
    {
        $this->origenPago = $origenPago;
    }

    /**
     * @return mixed
     */
    public function getTipoTrans()
    {
        return $this->tipoTrans;
    }

    /**
     * @param mixed $tipoTrans
     */
    public function setTipoTrans($tipoTrans)
    {
        $this->tipoTrans = $tipoTrans;
    }

    /**
     * @return mixed
     */
    public function getEstadoSRV()
    {
        return $this->estadoSRV;
    }

    /**
     * @param mixed $estadoSRV
     */
    public function setEstadoSRV($estadoSRV)
    {
        $this->estadoSRV = $estadoSRV;
    }

    /**
     * @return mixed
     */
    public function getFechaVencimiento()
    {
        return $this->fechaVencimiento;
    }

    /**
     * @param mixed $fechaVencimiento
     */
    public function setFechaVencimiento($fechaVencimiento)
    {
        $this->fechaVencimiento = $fechaVencimiento;
    }

    /**
     * @return mixed
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * @param mixed $fechaInicio
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
    }

    /**
     * @return mixed
     */
    public function getIDTransaccion()
    {
        return $this->IDTransaccion;
    }

    /**
     * @param mixed $IDTransaccion
     */
    public function setIDTransaccion($IDTransaccion)
    {
        $this->IDTransaccion = $IDTransaccion;
    }

    /**
     * @return string
     */
    public function getMoneda()
    {
        return $this->moneda;
    }

    /**
     * @param string $moneda
     */
    public function setMoneda($moneda)
    {
        $this->moneda = $moneda;
    }

    /**
     * @return mixed
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * @return mixed
     */
    public function getNumeroIntentos()
    {
        return $this->numeroIntentos;
    }

    /**
     * @param mixed $numeroIntentos
     */
    public function setNumeroIntentos($numeroIntentos)
    {
        $this->numeroIntentos = $numeroIntentos;
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

        return $datosPago;
    }

    /**
     * @return mixed
     */
    public function getMedioPago()
    {
        return $this->medioPago;
    }

    /**
     * @param mixed $medioPago
     */
    public function setMedioPago($medioPago)
    {
        $this->medioPago = $medioPago;
    }

    public function getFormaPago()
    {

    }

    /**
     * @return mixed
     */
    public function getRegistroPago()
    {
        return $this->registroPago;
    }

    /**
     * @param mixed $registroPago
     */
    public function setRegistroPago($registroPago)
    {
        $this->registroPago = $registroPago;
    }


}
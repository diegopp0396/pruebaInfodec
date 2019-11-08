<?php

class TarjetaCredito extends MedioPago
{
    private $nombreTarjetaHabiente;
    private $emailTarjetaHabiente;
    private $telefonoTarjetaHabiente;
    private $cuotas;
    private $numero;
    private $CVV;
    private $mesVencimiento;
    private $anoVencimiento;
    private $franquicia;

    /**
     * TarjetaCredito constructor.
     * @param $nombreTarjetaHabiente
     * @param $emailTarjetaHabiente
     * @param $telefonoTarjetaHabiente
     * @param $tipoDocumento
     * @param $numeroDocumento
     * @param $cuotas
     * @param $numero
     * @param $CVV
     * @param $mesVencimiento
     * @param $anoVencimiento
     * @param $franquicia
     */
    public function __construct($nombreTarjetaHabiente, $emailTarjetaHabiente, $telefonoTarjetaHabiente, $tipoDocumento, $numeroDocumento, $cuotas, $numero, $CVV, $mesVencimiento, $anoVencimiento, $franquicia, $formaPago, $nombreUsuario)
    {
        parent::__construct($formaPago, $nombreUsuario, $tipoDocumento, $numeroDocumento);
        $this->nombreTarjetaHabiente = $nombreTarjetaHabiente;
        $this->emailTarjetaHabiente = $emailTarjetaHabiente;
        $this->telefonoTarjetaHabiente = $telefonoTarjetaHabiente;
        $this->cuotas = $cuotas;
        $this->numero = $numero;
        $this->CVV = $CVV;
        $this->mesVencimiento = $mesVencimiento;
        $this->anoVencimiento = $anoVencimiento;
        $this->franquicia = $franquicia;
        $this->pasarela = new PayU();
    }

    public function getDatosMedioPago()
    {
        $datosTarjeta = array();

        $datosTarjeta["nombre"] = $this->getNombreTarjetaHabiente();
        $datosTarjeta["email"] = $this->getEmailTarjetaHabiente();
        $datosTarjeta["telefono"] = $this->getTelefonoTarjetaHabiente();
        $datosTarjeta["tipoDocumento"] = $this->getTipoDocumento();
        $datosTarjeta["numeroDocumento"] = $this->getNumeroDocumento();
        $datosTarjeta["cuotas"] = $this->getCuotas();
        $datosTarjeta["numero"] = $this->getNumero();
        $datosTarjeta["CVV"] = $this->getCVV();
        $datosTarjeta["mesVencimiento"] = $this->getMesVencimiento();
        $datosTarjeta["anoVencimiento"] = $this->getAnoVencimiento();
        $datosTarjeta["franquicia"] = $this->getFranquicia();
        $datosTarjeta["formaPago"] = $this->getFormaPago();

        return $datosTarjeta;
    }

    public function pagar($datosPago)
    {
        $this->pasarela->setAccount($datosPago["formaPago"], $datosPago["idObjeto"]);
        return $this->pasarela->payuTransaccion($datosPago, $this->getDatosMedioPago());
    }

    public function getNumeroIntentos($numeroFactura)
    {
        return $this->pasarela->payuIntentos($numeroFactura);
    }

    /**
     * @return mixed
     */
    public function getNombreTarjetaHabiente()
    {
        return $this->nombreTarjetaHabiente;
    }

    /**
     * @return mixed
     */
    public function getEmailTarjetaHabiente()
    {
        return $this->emailTarjetaHabiente;
    }

    /**
     * @return mixed
     */
    public function getTelefonoTarjetaHabiente()
    {
        return $this->telefonoTarjetaHabiente;
    }

    /**
     * @return mixed
     */
    public function getCuotas()
    {
        return $this->cuotas;
    }

    /**
     * @return mixed
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @return mixed
     */
    public function getCVV()
    {
        return $this->CVV;
    }

    /**
     * @return mixed
     */
    public function getMesVencimiento()
    {
        return $this->mesVencimiento;
    }

    /**
     * @return mixed
     */
    public function getAnoVencimiento()
    {
        return $this->anoVencimiento;
    }

    /**
     * @return mixed
     */
    public function getFranquicia()
    {
        return $this->franquicia;
    }


}
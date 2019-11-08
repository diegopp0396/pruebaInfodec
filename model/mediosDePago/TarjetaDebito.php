<?php

class TarjetaDebito extends MedioPago
{
    private $numeroBanco;
    private $nombreCliente;
    private $telefonoCliente;
    private $emailCliente;
    private $tipoCliente;

    /**
     * TarjetaDebito constructor.
     * @param $numeroBanco
     * @param $nombreCliente
     * @param $telefonoCliente
     * @param $emailCliente
     * @param $tipoCliente
     */
    public function __construct($numeroBanco, $nombreCliente, $telefonoCliente, $emailCliente, $tipoCliente,
                                $formaPago, $email, $tipoDocumento, $numeroDocumento)
    {
        parent::__construct($formaPago, $email, $tipoDocumento, $numeroDocumento);
        $this->numeroBanco = $numeroBanco;
        $this->nombreCliente = $nombreCliente;
        $this->telefonoCliente = $telefonoCliente;
        $this->emailCliente = $emailCliente;
        $this->tipoCliente = $tipoCliente;
        $this->pasarela = new PayU();
    }


    public function getDatosMedioPago()
    {
        $datosTarjeta = array();

        $datosTarjeta["nombre"] = $this->getNombreCliente();
        $datosTarjeta["email"] = $this->getEmailCliente();
        $datosTarjeta["telefono"] = $this->getTelefonoCliente();
        $datosTarjeta["tipoDocumento"] = $this->getTipoDocumento();
        $datosTarjeta["numeroDocumento"] = $this->getNumeroDocumento();
        $datosTarjeta["tipoCliente"] = $this->getTipoCliente();
        $datosTarjeta["banco"] = $this->getNumeroBanco();
        $datosTarjeta["formaPago"] = $this->getFormaPago();
        // Se adiciona el nombreUsuario para recuperarlo en la ventana de confirmación el email que llega desde APP
        $datosTarjeta["nombreUsuario"] = $this->getEmail();

        return $datosTarjeta;
    }

    public function pagar($datosPago)
    {
        $this->pasarela->setAccount();
        return $this->pasarela->payuTransaccion($datosPago, $this->getDatosMedioPago());
    }

    public function getNumeroIntentos($numeroFactura)
    {
        return $this->pasarela->payuIntentos($numeroFactura);
    }

    /**
     * @return mixed
     */
    public function getNumeroBanco()
    {
        return $this->numeroBanco;
    }

    /**
     * @param mixed $numeroBanco
     */
    public function setNumeroBanco($numeroBanco)
    {
        $this->numeroBanco = $numeroBanco;
    }

    /**
     * @return mixed
     */
    public function getTipoCliente()
    {
        return $this->tipoCliente;
    }

    /**
     * @param mixed $tipoCliente
     */
    public function setTipoCliente($tipoCliente)
    {
        $this->tipoCliente = $tipoCliente;
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
    public function getTelefonoCliente()
    {
        return $this->telefonoCliente;
    }

    /**
     * @param mixed $telefonoCliente
     */
    public function setTelefonoCliente($telefonoCliente)
    {
        $this->telefonoCliente = $telefonoCliente;
    }

    /**
     * @return mixed
     */
    public function getEmailCliente()
    {
        return $this->emailCliente;
    }

    /**
     * @param mixed $emailCliente
     */
    public function setEmailCliente($emailCliente)
    {
        $this->emailCliente = $emailCliente;
    }

}
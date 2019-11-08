<?php

class MedioPago
{
    private $formaPago; // ID forma pago
    private $email;
    private $tipoDocumento;
    private $numeroDocumento;
    protected $pasarela;

    /**
     * MedioPago constructor.
     * @param $formaPago
     * @param $email
     */
    public function __construct($formaPago, $email, $tipoDocumento, $numeroDocumento)
    {
        $this->formaPago = $formaPago;
        $this->email = $email;
        $this->tipoDocumento = $tipoDocumento;
        $this->numeroDocumento = $numeroDocumento;
    }

    /**
     * @return mixed
     */
    public function getFormaPago()
    {
        return $this->formaPago;
    }

    /**
     * @param mixed $formaPago
     */
    public function setFormaPago($formaPago)
    {
        $this->formaPago = $formaPago;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }



    public function getDatosMedioPago()
    {

    }

    public function pagar($datosPago)
    {

    }

    /**
     * @return mixed
     */
    public function getTipoDocumento()
    {
        return $this->tipoDocumento;
    }

    /**
     * @param mixed $tipoDocumento
     */
    public function setTipoDocumento($tipoDocumento)
    {
        $this->tipoDocumento = $tipoDocumento;
    }

    /**
     * @return mixed
     */
    public function getNumeroDocumento()
    {
        return $this->numeroDocumento;
    }

    /**
     * @param mixed $numeroDocumento
     */
    public function setNumeroDocumento($numeroDocumento)
    {
        $this->numeroDocumento = $numeroDocumento;
    }


}
<?php
/**
 * Created by Infodec S.A.S.
 * Project: claro
 * User: Usuario
 * Date: 11 de enero de 2019
 * Time: 10:23 AM
 */

class PayUTeFia extends MedioPago
{
    private $nombreCliente;
    private $telefonoCliente;

    public function __construct($formaPago, $email, $tipoDocumento, $numeroDocumento)
    {
        parent::__construct($formaPago, $email, $tipoDocumento, $numeroDocumento);
        $this->pasarela = new PayU();
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

    public function validarCliente($datosCliente)
    {
        return $this->pasarela->payuValidacionPayuTeFia($datosCliente);
    }
}
<?php

class TarjetaTokenizada extends MedioPago
{
    private $tokeNumero;
    private $tipoToken;
    private $nombreCliente;
    private $celularRef;
    private $numeroCuenta;
    private $direccionInst;
    private $tipoReferencia;
    private $referencia;
    private $metodoPago;
    private $tarjetaHabiente;
    private $numeroDocumentoTarjetaHabiente;
    private $celularSMS;
    private $numeroTarjeta;
    private $franquicia;
    private $fechaVencimiento;
    private $tipoTrans;
    private $canal;
    private $token;
    private $tkEstado;
    private $aceptaTerminos;
    private $payerID;
    private $fechaRegistro;
    private $fechaDomicilia;
    private $fechaInactiva;
    private $estadoDomicilia;
    private $tipoPeticionBSCS;
    private $estadoBSCS;
    private $descripcionErrorBSCS;
    private $tipoPeticionRR;
    private $estadoRR;
    private $descripcionErrorRR;
    private $tipoPeticionPayUTK;
    private $estadoPayUTK;
    private $descripcionErrorPayUTK;
    private $usuarioNumero;
    private $eliminado;
    private $tipoCliente;
    private $cuotas;
    private $fechaCorte;
    private $smsNotificacionEncolar;
    private $correoNotificacionEncolar;
    private $fechaCorteNotificar;
    private $smsNotificacionRechazcoTK;
    private $correoNotificacionRechazoTK;
    private $asociarClaroPayDomiciliacion;
    private $fechaRegistroToken;
    private $CVV;

    /**
     * TarjetaTokenizada constructor.
     */
    public function __construct($formaPago, $email, $tipoDocumento, $numeroDocumento)
    {
        parent::__construct($formaPago, $email, $tipoDocumento, $numeroDocumento);
        $this->CVV = '';

        $this->pasarela = new PayU();
    }

    /**
     * @return mixed
     */
    public function getTokeNumero()
    {
        return $this->tokeNumero;
    }

    /**
     * @param mixed $tokeNumero
     */
    public function setTokeNumero($tokeNumero)
    {
        $this->tokeNumero = $tokeNumero;
    }

    /**
     * @return mixed
     */
    public function getTipoToken()
    {
        return $this->tipoToken;
    }

    /**
     * @param mixed $tipoToken
     */
    public function setTipoToken($tipoToken)
    {
        $this->tipoToken = $tipoToken;
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
    public function getCelularRef()
    {
        return $this->celularRef;
    }

    /**
     * @param mixed $celularRef
     */
    public function setCelularRef($celularRef)
    {
        $this->celularRef = $celularRef;
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
    public function getDireccionInst()
    {
        return $this->direccionInst;
    }

    /**
     * @param mixed $direccionInst
     */
    public function setDireccionInst($direccionInst)
    {
        $this->direccionInst = $direccionInst;
    }

    /**
     * @return mixed
     */
    public function getTipoReferencia()
    {
        return $this->tipoReferencia;
    }

    /**
     * @param mixed $tipoReferencia
     */
    public function setTipoReferencia($tipoReferencia)
    {
        $this->tipoReferencia = $tipoReferencia;
    }

    /**
     * @return mixed
     */
    public function getReferencia()
    {
        return $this->referencia;
    }

    /**
     * @param mixed $referencia
     */
    public function setReferencia($referencia)
    {
        $this->referencia = $referencia;
    }

    /**
     * @return mixed
     */
    public function getMetodoPago()
    {
        return $this->metodoPago;
    }

    /**
     * @param mixed $metodoPago
     */
    public function setMetodoPago($metodoPago)
    {
        $this->metodoPago = $metodoPago;
    }

    /**
     * @return mixed
     */
    public function getTarjetaHabiente()
    {
        return $this->tarjetaHabiente;
    }

    /**
     * @param mixed $tarjetaHabiente
     */
    public function setTarjetaHabiente($tarjetaHabiente)
    {
        $this->tarjetaHabiente = $tarjetaHabiente;
    }

    /**
     * @return mixed
     */
    public function getNumeroDocumentoTarjetaHabiente()
    {
        return $this->numeroDocumentoTarjetaHabiente;
    }

    /**
     * @param mixed $numeroDocumentoTarjetaHabiente
     */
    public function setNumeroDocumentoTarjetaHabiente($numeroDocumentoTarjetaHabiente)
    {
        $this->numeroDocumentoTarjetaHabiente = $numeroDocumentoTarjetaHabiente;
    }

    /**
     * @return mixed
     */
    public function getCelularSMS()
    {
        return $this->celularSMS;
    }

    /**
     * @param mixed $celularSMS
     */
    public function setCelularSMS($celularSMS)
    {
        $this->celularSMS = $celularSMS;
    }

    /**
     * @return mixed
     */
    public function getNumeroTarjeta()
    {
        return $this->numeroTarjeta;
    }

    /**
     * @param mixed $numeroTarjeta
     */
    public function setNumeroTarjeta($numeroTarjeta)
    {
        $this->numeroTarjeta = $numeroTarjeta;
    }

    /**
     * @return mixed
     */
    public function getFranquicia()
    {
        return $this->franquicia;
    }

    /**
     * @param mixed $franquicia
     */
    public function setFranquicia($franquicia)
    {
        $this->franquicia = $franquicia;
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
    public function getCanal()
    {
        return $this->canal;
    }

    /**
     * @param mixed $canal
     */
    public function setCanal($canal)
    {
        $this->canal = $canal;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getTkEstado()
    {
        return $this->tkEstado;
    }

    /**
     * @param mixed $tkEstado
     */
    public function setTkEstado($tkEstado)
    {
        $this->tkEstado = $tkEstado;
    }

    /**
     * @return mixed
     */
    public function getAceptaTerminos()
    {
        return $this->aceptaTerminos;
    }

    /**
     * @param mixed $aceptaTerminos
     */
    public function setAceptaTerminos($aceptaTerminos)
    {
        $this->aceptaTerminos = $aceptaTerminos;
    }

    /**
     * @return mixed
     */
    public function getPayerID()
    {
        return $this->payerID;
    }

    /**
     * @param mixed $payerID
     */
    public function setPayerID($payerID)
    {
        $this->payerID = $payerID;
    }

    /**
     * @return mixed
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * @param mixed $fechaRegistro
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    }

    /**
     * @return mixed
     */
    public function getFechaDomicilia()
    {
        return $this->fechaDomicilia;
    }

    /**
     * @param mixed $fechaDomicilia
     */
    public function setFechaDomicilia($fechaDomicilia)
    {
        $this->fechaDomicilia = $fechaDomicilia;
    }

    /**
     * @return mixed
     */
    public function getFechaInactiva()
    {
        return $this->fechaInactiva;
    }

    /**
     * @param mixed $fechaInactiva
     */
    public function setFechaInactiva($fechaInactiva)
    {
        $this->fechaInactiva = $fechaInactiva;
    }

    /**
     * @return mixed
     */
    public function getEstadoDomicilia()
    {
        return $this->estadoDomicilia;
    }

    /**
     * @param mixed $estadoDomicilia
     */
    public function setEstadoDomicilia($estadoDomicilia)
    {
        $this->estadoDomicilia = $estadoDomicilia;
    }

    /**
     * @return mixed
     */
    public function getTipoPeticionBSCS()
    {
        return $this->tipoPeticionBSCS;
    }

    /**
     * @param mixed $tipoPeticionBSCS
     */
    public function setTipoPeticionBSCS($tipoPeticionBSCS)
    {
        $this->tipoPeticionBSCS = $tipoPeticionBSCS;
    }

    /**
     * @return mixed
     */
    public function getEstadoBSCS()
    {
        return $this->estadoBSCS;
    }

    /**
     * @param mixed $estadoBSCS
     */
    public function setEstadoBSCS($estadoBSCS)
    {
        $this->estadoBSCS = $estadoBSCS;
    }

    /**
     * @return mixed
     */
    public function getDescripcionErrorBSCS()
    {
        return $this->descripcionErrorBSCS;
    }

    /**
     * @param mixed $descripcionErrorBSCS
     */
    public function setDescripcionErrorBSCS($descripcionErrorBSCS)
    {
        $this->descripcionErrorBSCS = $descripcionErrorBSCS;
    }

    /**
     * @return mixed
     */
    public function getTipoPeticionRR()
    {
        return $this->tipoPeticionRR;
    }

    /**
     * @param mixed $tipoPeticionRR
     */
    public function setTipoPeticionRR($tipoPeticionRR)
    {
        $this->tipoPeticionRR = $tipoPeticionRR;
    }

    /**
     * @return mixed
     */
    public function getEstadoRR()
    {
        return $this->estadoRR;
    }

    /**
     * @param mixed $estadoRR
     */
    public function setEstadoRR($estadoRR)
    {
        $this->estadoRR = $estadoRR;
    }

    /**
     * @return mixed
     */
    public function getDescripcionErrorRR()
    {
        return $this->descripcionErrorRR;
    }

    /**
     * @param mixed $descripcionErrorRR
     */
    public function setDescripcionErrorRR($descripcionErrorRR)
    {
        $this->descripcionErrorRR = $descripcionErrorRR;
    }

    /**
     * @return mixed
     */
    public function getTipoPeticionPayUTK()
    {
        return $this->tipoPeticionPayUTK;
    }

    /**
     * @param mixed $tipoPeticionPayUTK
     */
    public function setTipoPeticionPayUTK($tipoPeticionPayUTK)
    {
        $this->tipoPeticionPayUTK = $tipoPeticionPayUTK;
    }

    /**
     * @return mixed
     */
    public function getEstadoPayUTK()
    {
        return $this->estadoPayUTK;
    }

    /**
     * @param mixed $estadoPayUTK
     */
    public function setEstadoPayUTK($estadoPayUTK)
    {
        $this->estadoPayUTK = $estadoPayUTK;
    }

    /**
     * @return mixed
     */
    public function getDescripcionErrorPayUTK()
    {
        return $this->descripcionErrorPayUTK;
    }

    /**
     * @param mixed $descripcionErrorPayUTK
     */
    public function setDescripcionErrorPayUTK($descripcionErrorPayUTK)
    {
        $this->descripcionErrorPayUTK = $descripcionErrorPayUTK;
    }

    /**
     * @return mixed
     */
    public function getUsuarioNumero()
    {
        return $this->usuarioNumero;
    }

    /**
     * @param mixed $usuarioNumero
     */
    public function setUsuarioNumero($usuarioNumero)
    {
        $this->usuarioNumero = $usuarioNumero;
    }

    /**
     * @return mixed
     */
    public function getEliminado()
    {
        return $this->eliminado;
    }

    /**
     * @param mixed $eliminado
     */
    public function setEliminado($eliminado)
    {
        $this->eliminado = $eliminado;
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
    public function getCuotas()
    {
        return $this->cuotas;
    }

    /**
     * @param mixed $cuotas
     */
    public function setCuotas($cuotas)
    {
        $this->cuotas = $cuotas;
    }

    /**
     * @return mixed
     */
    public function getFechaCorte()
    {
        return $this->fechaCorte;
    }

    /**
     * @param mixed $fechaCorte
     */
    public function setFechaCorte($fechaCorte)
    {
        $this->fechaCorte = $fechaCorte;
    }

    /**
     * @return mixed
     */
    public function getSmsNotificacionEncolar()
    {
        return $this->smsNotificacionEncolar;
    }

    /**
     * @param mixed $smsNotificacionEncolar
     */
    public function setSmsNotificacionEncolar($smsNotificacionEncolar)
    {
        $this->smsNotificacionEncolar = $smsNotificacionEncolar;
    }

    /**
     * @return mixed
     */
    public function getCorreoNotificacionEncolar()
    {
        return $this->correoNotificacionEncolar;
    }

    /**
     * @param mixed $correoNotificacionEncolar
     */
    public function setCorreoNotificacionEncolar($correoNotificacionEncolar)
    {
        $this->correoNotificacionEncolar = $correoNotificacionEncolar;
    }

    /**
     * @return mixed
     */
    public function getFechaCorteNotificar()
    {
        return $this->fechaCorteNotificar;
    }

    /**
     * @param mixed $fechaCorteNotificar
     */
    public function setFechaCorteNotificar($fechaCorteNotificar)
    {
        $this->fechaCorteNotificar = $fechaCorteNotificar;
    }

    /**
     * @return mixed
     */
    public function getSmsNotificacionRechazcoTK()
    {
        return $this->smsNotificacionRechazcoTK;
    }

    /**
     * @param mixed $smsNotificacionRechazcoTK
     */
    public function setSmsNotificacionRechazcoTK($smsNotificacionRechazcoTK)
    {
        $this->smsNotificacionRechazcoTK = $smsNotificacionRechazcoTK;
    }

    /**
     * @return mixed
     */
    public function getCorreoNotificacionRechazoTK()
    {
        return $this->correoNotificacionRechazoTK;
    }

    /**
     * @param mixed $correoNotificacionRechazoTK
     */
    public function setCorreoNotificacionRechazoTK($correoNotificacionRechazoTK)
    {
        $this->correoNotificacionRechazoTK = $correoNotificacionRechazoTK;
    }

    /**
     * @return mixed
     */
    public function getAsociarClaroPayDomiciliacion()
    {
        return $this->asociarClaroPayDomiciliacion;
    }

    /**
     * @param mixed $asociarClaroPayDomiciliacion
     */
    public function setAsociarClaroPayDomiciliacion($asociarClaroPayDomiciliacion)
    {
        $this->asociarClaroPayDomiciliacion = $asociarClaroPayDomiciliacion;
    }

    /**
     * @return mixed
     */
    public function getFechaRegistroToken()
    {
        return $this->fechaRegistroToken;
    }

    /**
     * @param mixed $fechaRegistroToken
     */
    public function setFechaRegistroToken($fechaRegistroToken)
    {
        $this->fechaRegistroToken = $fechaRegistroToken;
    }

    /**
     * @return mixed
     */
    public function getCVV()
    {
        return $this->CVV;
    }

    /**
     * @param mixed $CVV
     */
    public function setCVV($CVV)
    {
        $this->CVV = $CVV;
    }

    public function getDatosMedioPago()
    {
        $datosTarjeta = array();

        $datosTarjeta["nombre"] = $this->getTarjetaHabiente();
        $datosTarjeta["email"] = $this->getEmail();
        $datosTarjeta["telefono"] = $this->getCelularSMS();
        $datosTarjeta["tipoDocumento"] = $this->getTipoDocumento();
        $datosTarjeta["numeroDocumento"] = $this->getNumeroDocumento();
        $datosTarjeta["cuotas"] = $this->getCuotas();
        $datosTarjeta["CVV"] = $this->getCVV();
        $datosTarjeta["franquicia"] = $this->getFranquicia();
        $datosTarjeta["formaPago"] = $this->getFormaPago();
        $datosTarjeta["token"] = $this->getToken();

        return $datosTarjeta;
    }

    public function pagar($datosPago)
    {
        $this->pasarela->setAccount($datosPago["formaPago"], $datosPago["idObjeto"]);
        return $this->pasarela->payuTransaccionToken($datosPago, $this->getDatosMedioPago());
    }

    public function getNumeroIntentos($numeroFactura)
    {
        return $this->pasarela->payuIntentos($numeroFactura);
    }

}
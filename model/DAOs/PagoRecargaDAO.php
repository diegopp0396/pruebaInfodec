<?php
/**
 * Created by Infodec S.A.S.
 * Project: claro
 * User: Usuario
 * Date: 10 de enero de 2019
 * Time: 10:32 AM
 */

class PagoRecargaDAO extends APagosClaro
{

    public function insertPago(Pago $pagoRecarga)
    {
        $consulta = new Consulta();

        ##INSERT INTO CL_PAGOSCLARO (DESCRIPTOR_COMPRA ,ValorTotal ,FORMA_PAGO ,NRO_CUENTA ,CVV ,
        #DESCRIPCION ,Subtotal ,Iva ,TIPO_TRANS ,FECHA_INICIO ,FechaHora ,CodigoCliente ,NumeroFactura ,
        #TIPO_DOCUMENTO ,NumeroIdentificacion ,PRIMER_NOMBRE ,SEGUNDO_NOMBRE ,OrigenPago ,PRIMER_APELLIDO ,
        #SEGUNDO_APELLIDO ,FIJO ,NUMEROCELULAR ,EMAIL ,BASEDEVOLUCION ,IDCIUDAD ,GENERO ,NACIONALIDAD ,
        #DIRECCION ,DC_PRIMERNOMBRE ,DC_SEGUNDONOMBRE ,DC_PRIMERAPELLIDO ,DC_SEGUNDOAPELLIDO ,DC_PAIS ,
        #DC_CIUDAD ,DC_DIRECCION ,DC_EMAIL ,DC_CELULAR ,DC_FIJO ,COD_MONEDA ,CLAVE ,IPTRANSACCION ,
        #ID ,USRIO_NUMERO ,TIPO_SELECCION ,ID_PAQUETE ,TIPO_COMPRA ,TIPO_PAQUETE ,VALOR_PAQUETE ,
        #TIPO_LINEA ,VALOR ,FECHA_TOKEN ,INSPIRA ,DIA_RECURRENCIA_COMPRA )
        # VALUES ('Recarga en linea',6000,'2',NULL,NULL,'Compra de Recargas',6000,0,'9','2019-01-09 16:08:13',
        #'2019-01-09 16:08:13',NULL,'3700008033090119160803','-1','19173923',NULL,NULL,'4',NULL,NULL,NULL,'3700008033',
        #'leon_ct@hotmail.com',NULL,'-1','-1','-1',NULL,NULL,NULL,NULL,NULL,'-1','-1',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1
        #,'R',10400,'1','-1',NULL,'PREPAGO','$6.000','29-06-2018 19:48:19','-1','-1')

        $queryPago = "INSERT INTO CL_PAGOSCLARO (DESCRIPTOR_COMPRA, ValorTotal, FORMA_PAGO, NRO_CUENTA, CVV, ";
        $queryPago .= "DESCRIPCION, Subtotal, Iva, TIPO_TRANS, FECHA_INICIO, FechaHora, CodigoCliente, NumeroFactura, ";
        $queryPago .= "TIPO_DOCUMENTO, NumeroIdentificacion, OrigenPago, ";
        $queryPago .= "NUMEROCELULAR, EMAIL, BASEDEVOLUCION, ";
        $queryPago .= "ID, USRIO_NUMERO, TIPO_SELECCION, ";
        $queryPago .= "TIPO_LINEA, VALOR, FECHA_TOKEN, DIA_RECURRENCIA_COMPRA, AUDIT_NAVEGADOR) ";
        $queryPago .= "VALUES ('Recarga en linea', " . $pagoRecarga->getValorTotal() . ", '" . $pagoRecarga->getMedioPago()->getFormaPago();
        $queryPago .= "', '" . $pagoRecarga->getNumeroCuenta() ."', NULL, 'Compra de Recargas', " . $pagoRecarga->getValorTotal() . ", " . $pagoRecarga->getIva() .", '";
        $queryPago .= $pagoRecarga->getTipoTrans() . "', '" . $pagoRecarga->getFechaInicio() . "', '" .  $pagoRecarga->getFechaInicio();
        $queryPago .= "', NULL, '" . $pagoRecarga->getNumeroFactura() . "', '-1', '" . $pagoRecarga->getNumeroDocumento();
        $queryPago .= "', '" . $pagoRecarga->getOrigenPago() . "', '" . $pagoRecarga->getNumeroLinea() . "', '";
        $queryPago .= $pagoRecarga->getNombreUsuario() . "', NULL, ";
        $queryPago .= "NULL, 1, 'R', 'PREPAGO', '" . $pagoRecarga->getValorTotal() . "', '" . $pagoRecarga->getFechaInicio() . "', ";
        $queryPago .= "'" . $pagoRecarga->getDiaRecurrencia() . "', '" . Utils::getNavegadorOS() . "')";

        $consulta->setConsulta($queryPago);

        $consulta->ejecutarConsulta();

        $resultadoInsert = $consulta->getResultado();

        $consulta->desconectar();

        unset($consulta);

        if ($resultadoInsert == 2) {
            return array("ID" => $this->getLastID($pagoRecarga->getNumeroFactura(), $pagoRecarga->getTipoTrans()));
        } else {
            return array("error" => true, "mensaje" => "Error al insertar en CL_PAGOSCLARO");
        }
    }
}
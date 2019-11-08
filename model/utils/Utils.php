<?php

class Utils
{
    /**
     * Funcion que devuelve un array con los valores:
     *    os => sistema operativo
     *    browser => navegador
     *    version => version del navegador
     */
    function getNavegadorOS()
    {

        $browser = array("IE", "OPERA", "MOZILLA", "NETSCAPE", "FIREFOX", "SAFARI", "CHROME");
        $os = array("WIN", "MAC", "LINUX");

        # definimos unos valores por defecto para el navegador y el sistema operativo
        $info['browser'] = "OTHER";
        $info['os'] = "OTHER";

        # buscamos el navegador con su sistema operativo
        foreach ($browser as $parent) {
            $s = strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), $parent);
            $f = $s + strlen($parent);
            $version = substr($_SERVER['HTTP_USER_AGENT'], $f, 15);
            $version = preg_replace('/[^0-9,.]/', '', $version);
            if ($s) {
                $info['browser'] = $parent;
                $info['version'] = $version;
            }
        }

        # obtenemos el sistema operativo
        foreach ($os as $val) {
            if (strpos(strtoupper($_SERVER['HTTP_USER_AGENT']), $val) !== false)
                $info['os'] = $val;
        }

        # devolvemos el array de valores
        return $info['browser'] . "//" . $info['version'] . "//" . $info['os'];
    }

    /**
     * Envío de SMS al confirmar el pago de factura Postpago
     * @return
     */
    function smsPospago($nro_factura, $nro_cel, $codigo_cliente = "")
    {
        $consulta = new Consulta;

        $select_var  = "SELECT NOMBRE, VALOR FROM CP_VARIABLES WHERE NOMBRE IN ('VAR_SMSPOSPAGO', 'VAR_SMSMENSAJE', ";
        $select_var .= "'VAR_SMSUSUARIO', 'VAR_SMSPASS') AND " . $consulta->getLink()->SQLDate("Y-m-d") . " BETWEEN ";
        $select_var .= $consulta->getLink()->SQLDate('Y-m-d', 'FECHA_INICIO') . " AND ";
        $select_var .= $consulta->getLink()->SQLDate('Y-m-d', $consulta->getLink()->ifNull("FECHA_FIN", "'3000-01-01'"));
        $select_var .= " AND ELIMINADO = -1 AND ACTIVO = '1'";

        $consulta->setConsulta($select_var);
        $info_sms = $consulta->ejecutarConsulta();

        $val_sms = array();
        foreach ($info_sms as $key => $val) {
            $val_sms[$val["NOMBRE"]] = $val["VALOR"];
        }

        list($ip, $puerto, $wsdl, $origen) = explode(";", $val_sms['VAR_SMSPOSPAGO']);

        $val_sms['VAR_SMSMENSAJE'] = str_replace("#NROFACTURA#", $codigo_cliente, $val_sms['VAR_SMSMENSAJE']);
        $val_sms['VAR_SMSMENSAJE'] = str_replace("#NROTELEFONO#", $nro_cel, $val_sms['VAR_SMSMENSAJE']);

        $param_envio = array('origen' => $origen, 'destido' => $nro_cel,
            'msj' => utf8_encode($val_sms['VAR_SMSMENSAJE']) . " Pago desde Gateway APP v1.", 'port_out' => $puerto);
        $soap_auth = array('login' => $val_sms['VAR_SMSUSUARIO'], 'password' => $val_sms['VAR_SMSPASS']);
        $parametros = array('xmlString' => $param_envio);

        $soap_client = new SoapClient($wsdl, $soap_auth);

        try {
            $result = $soap_client->__SoapCall('sendSMS', $parametros);
            //print_r ($result);
            //echo '<br/>';
            //print_r ($result->sendSMS);
            return 1;
        } catch (Exception $e) {
            //echo 'Error: ' . $e->getMessage();
            echo '' . $e->getMessage();
        }
    }
}
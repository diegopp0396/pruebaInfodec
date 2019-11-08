<?php

class TarjetaTokenizadaDAO implements ITarjetaTokenizada
{

    public function selectTarjetasTokenizadas()
    {
        $consulta = new Consulta();

        $consulta->setConsulta("SELECT TOP 1 * FROM CL_TOKENIZACION WITH (NOLOCK) ORDER BY 1 DESC");

        return json_encode($consulta->ejecutarConsulta());
    }

    public function searchTarjetaByEmail($email)
    {
        $consulta = new Consulta();

        $queryTarjetas  = "SELECT TOP 20 CONCAT('TK_', A.TOKE_NUMERO) AS TOKE_NUMERO, A.TIPO_DOCUMENTO, B.VALOR_ES AS FRANQUICIA, ";
        $queryTarjetas .= "A.NUMERO_TARJETA AS #TARJETA FROM CL_TOKENIZACION A, GT_VALORES B WITH (NOLOCK) ";
        $queryTarjetas .= "WHERE A.ELIMINADO = -1 AND A.TIPO_TOKEN = '3' AND A.TKESTADO = '1' AND A.EMAIL = '";
        $queryTarjetas .= $email . "' AND A.FRANQUICIA = B.CODIGO AND B.LIST_NUMERO = 35 ORDER BY A.TOKE_NUMERO DESC";

        $consulta->setConsulta($queryTarjetas);

        $tarjetas = $consulta->ejecutarConsulta();

        if ($consulta->numeroFilas() > 0) {
            $retorno["error"] = false;
            $retorno["tarjetas"] = $tarjetas;
        } else {
            $retorno["error"] = true;
            $retorno["tarjetas"] = "No tiene tarjetas registradas";
        }

        $consulta->desconectar();

        unset($consulta);

        return ($retorno);
    }

    public function searchTarjetaById($id)
    {
        $consulta = new Consulta();

        $queryTarjeta  = "SELECT C.TARJETA_HABIENTE AS NOMBRE, C.EMAIL, CASE WHEN C.CELULAR_SMS IS NULL THEN C.CELULAR_REF ";
        $queryTarjeta .= "ELSE C.CELULAR_SMS END TELEFONO, C.NUMERO_DOCTARH AS #DOCUMENTO, C.TOKEN, C.TIPO_DOCUMENTO, ";
        $queryTarjeta .= "C.FRANQUICIA FROM CL_TOKENIZACION C WITH (NOLOCK) WHERE C.TOKE_NUMERO = '" . $id . "'";

        $consulta->setConsulta($queryTarjeta);

        $tarjeta = $consulta->ejecutarConsulta();

        if ($consulta->numeroFilas() > 0) {
            $retorno = $tarjeta[0];
        } else {
            $retorno = "No se encontró tarjeta con ese TokeNumero";
        }

        $consulta->desconectar();

        unset($consulta);

        return $retorno;
    }

    public function insertTarjetaTokenizada()
    {
        // TODO: Implement insertTarjetaTokenizada() method.
    }
}
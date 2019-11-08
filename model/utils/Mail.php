<?php

class Mail
{

    private function crearEmail($email, $destinatario, $remitente = CORREOFROM)
    {
        $consulta = new Consulta();

        $fechaRegistro = $consulta->getLink()->DBtimestamp(date('Y-m-d')." ".date("H").":".date("i").":".date("s"));

        $queryEmail  = "INSERT INTO GT_MAIL (REMITENTE, DESTINO, ASUNTO, DESCRIPCION, ENVIADO, FECHA_REGISTRO_CORREO) ";
        $queryEmail .= "VALUES ('" . $remitente . "', '" . $destinatario . "', '" . $email["ASUNTO"] . "', '" . $email["PLANTILLA"] . "'";
        $queryEmail .=  ", 2, " . $fechaRegistro . ")";

        //return $queryEmail;

        $consulta->setConsulta($queryEmail);

        $consulta->ejecutarConsulta();

        if ($consulta->getResultado() == 2) {
            return true;
        } else {
            return false;
        }
    }

    private function getPlantillaCorreoElectronico($idPlantilla)
    {
        $consulta = new Consulta;

        $queryPlantillaCorreo  = "SELECT PLANTILLA, ASUNTO FROM GT_OBJETOS_PLANTILLA WHERE PLTA_NUMERO = " . $idPlantilla;
        $queryPlantillaCorreo .= " AND ELIMINADO = -1";

        $consulta->setConsulta($queryPlantillaCorreo);

        return $consulta->ejecutarConsulta();
    }

    public function notificarPagoFacturaHogar($idPlantilla, $datos, $destinatario)
    {
        //USUARIO, ESTADO_PAGO, FACTURA, VALOR, FECHA_PAGO, TELEFONO, EMAIL
        $plantilla = $this->getPlantillaCorreoElectronico($idPlantilla);

        $asunto = $plantilla[0]['ASUNTO'];

        $asunto = str_replace("#FACTURA#", $datos["FACTURA"], $asunto);

        $contenido = $plantilla[0]['PLANTILLA'];

        //AQUI VA EL CONTENIDO DINAMICO DE LA PLANTILLA
        foreach ($datos as $k => $val) {
            $contenido = str_replace("#" . $k . "#", $val, $contenido);
        }

        $plantilla[0]['ASUNTO'] = $asunto;
        $plantilla[0]['PLANTILLA'] = $contenido;

        return $this->crearEmail($plantilla[0], $destinatario);
    }

}
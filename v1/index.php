<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With");
header('Content-Type: application/json; charset=utf-8');
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

@ini_set('display_errors', 'off');

// Se incluye archivo de configuración
include ('../config.inc.php');

// Se adiciona la librería Slim y se crea el objeto para trabajar
require '../libs/Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

//Disable debugging
$app->config('debug', true);

/** Rutas para recibir las peticiones desde la APP */

$app->group('/app', 'authenticate', function () use ($app) {

    /*$app->post('/hogar', function () use ($app) {

        $data = json_decode($app->request->getBody(), true);
        $data = $app->request->post();

        //fecha, valor, numeroCuenta, numeroReferencia, FechaLimitePago, token

        $fecha = $data["fecha"]; //YYYYMMDDHHMMSS
        $valor = $data["valor"]; //Sin signos
        $numeroCuenta = $data["numeroCuenta"];
        $numeroReferencia = $data["numeroReferencia"];
        $fechaLimitePago = $data["FechaLimitePago"]; //YYYYMMDD
        $token = $data["token"];
        $nombreUsuario = $data["nombreUsuario"];
        $nombreCliente = $data["nombreCliente"];

        //OrigenPago=4&fecha=201811190233&numeroCuenta=88782820&valor=68000&referencia=88567561&fechalimitepago=20181208&token=1232434&nombreUsuario=leon_ct@hotmail.com&nombreCliente=hola

        $app->redirect("https://pruebasclaro.maxgp.com.co:4443/NodeJS/inicioFlujoHogar?OrigenPago=4&fecha=".$fecha.
            "&numeroCuenta=".$numeroCuenta."&valor=".$valor."&referencia=".$numeroReferencia."&fechalimitepago=".$fechaLimitePago.
            "&token=".$token."&nombreUsuario=".$nombreUsuario."&nombreCliente=".$nombreCliente);
    });*/

    $app->post('/postpago', function () use ($app) {

        $data = $app->request->post();

        //fecha, valor, numeroCuenta, numeroReferencia, FechaLimitePago, token

        $fecha = $data["fecha"]; //YYYYMMDDHHMMSS
        $valor = $data["valor"]; //Sin signos
        $numeroCelular = $data["numeroCelular"];
        $numeroFactura = $data["numeroFactura"];
        $numeroReferencia = $data["numeroReferencia"];
        $fechaLimitePago = $data["FechaLimitePago"]; //YYYYMMDD
        $token = $data["token"];
        $nombreUsuario = $data["nombreUsuario"];
        $nombreCliente = $data["nombreCliente"];

        //OrigenPago=4&fecha=201811190233&numeroCuenta=88782820&valor=68000&referencia=88567561&fechalimitepago=20181208&token=1232434&nombreUsuario=leon_ct@hotmail.com&nombreCliente=hola

        $app->redirect("https://pruebasclaro.maxgp.com.co:4443/NodeJS/inicioFlujoMovil?OrigenPago=4&fecha=".$fecha.
            "&numeroCelular=".$numeroCelular."&valor=".$valor."&numeroFactura=".$numeroFactura .
            "&referencia=".$numeroReferencia."&fechalimitepago=".$fechaLimitePago.
            "&token=".$token."&nombreUsuario=".$nombreUsuario."&nombreCliente=".$nombreCliente);
    });

});

/** Rutas para manejar peticiones internas del front-end */

/**
 * Grupo destinado a recibir las peticiones referentes a Tarjetas Tokenizadas
 */

$app->group('/tarjetasTokenizadas', function () use ($app) {

    $app->get('/', function () use ($app) {

        $nombreUsuario = $app->request->get("nombreUsuario");

        if (isset($nombreUsuario)) {

            $tarjetasDAO = new TarjetaTokenizadaDAO();
            $respuestaTarjetas = $tarjetasDAO->searchTarjetaByEmail($nombreUsuario);

            $response["tarjetas"] = $respuestaTarjetas["tarjetas"];
            $response["error"] = $respuestaTarjetas["error"];
        } else {
            $response["error"] = true;
            $response["mensaje"] = "Parámetros vacios";
        }

        echoResponse(200, $response);
    });
});

/**
 * Grupo destinado a recibir las peticiones referentes a Medios de Pago
 */

$app->group('/medioPago', function () use ($app) {

    $app->get('/payUTeFia', function () use ($app) {

        // idObjeto, valorTotal, nombreUsuario
        $data = $app->request->get();

        if (isset($data["idObjeto"]) || isset($data["nombreUsuario"]) || isset($data["valorTotal"])) {
            $data["nombreUsuario"] = strtolower($data["nombreUsuario"]);
            $data["formaPago"] = 9;#Se configura 9 porque es el ID de este medio de pago

            $payUTeFia = new PayUTeFia($data["formaPago"], $data["nombreUsuario"], "", "");

            $validacion = $payUTeFia->validarCliente($data);

            $response["error"] = $validacion["error"];
            $response["mensaje"] = $validacion["mensaje"];
            if ($response["error"] == false)
                $response["signature"] = $validacion["respuesta"]["paymentMethod"]["paymentMethodAttributes"]["signature"];
        } else {
            $response["error"] = true;
            $response["mensaje"] = "Parámetros vacios";
        }

        echoResponse(200, $response);
    });
});

/**
 * Grupo destinado a recibir las peticiones referentes a los pagos en CL_PAGOSCLARO
 */

$app->group('/pagos', function () use ($app) {

    $app->get('/hogar', function () use ($app) {

        if ($app->request->get("id")) {
            $id = $app->request->get("id");

            $pagosDAO = new PagoHogarDAO();

            $response["error"] = false;
            $response["pago"] = $pagosDAO->searchPagoById($id);
        } else {
            $response["error"] = true;
            $response["mensaje"] = "Parámetros vacios";
        }

        echoResponse(200, $response);
    });

    $app->post('/hogar', function () use ($app) {

        $data = json_decode($app->request->getBody(), true);

        $numeroFactura = $data["numeroFactura"];

        $pagosDAO = new PagoHogarDAO();

        $existePagoRealizado = $pagosDAO->searchEstadoPagoByFactura($numeroFactura);

        if (is_bool($existePagoRealizado)) {

            $numeroCuenta = $data["numeroCuenta"];
            $fechaLimitePago = $data["fechaLimitePago"];
            $nombreCliente = $data["nombreCliente"];
            $nombreUsuario = $data["nombreUsuario"];
            $formaPago = $data["formaPago"];
            $valorTotal = $data["valorTotal"];
            $subTotal = $data["subtotal"];
            $iva = 0;//$app->request->post("iva");
            $origenPago = $data["origenPago"];
            $tipoTrans = $data["tipoTrans"];
            $estadoSRV = -1;//$app->request->post("estadoSRV");
            $fechaVencimiento = date("Y-m-d", strtotime($data["fechaLimitePago"]));
            $fechaInicio = date("Y-m-d H:m:s", strtotime($data["fechaInicio"]));
            $numeroIntentos = $data["numeroIntentos"];

            $metodoPago = new TarjetaCredito("", "", "",
                "", "", "", "", "", "", "",
                "", $formaPago, $nombreUsuario);

            $pagoHogar = new PagoHogar($numeroCuenta, $numeroFactura, $fechaLimitePago, $nombreCliente, $nombreUsuario,
                $metodoPago, $valorTotal, $subTotal, $iva, $origenPago, $tipoTrans, $estadoSRV, $fechaVencimiento,
                $fechaInicio, $numeroIntentos);

            $insertClPagos = $pagosDAO->insertPago($pagoHogar);

            if (isset($insertClPagos["ID"])) {
                $response["error"] = false;
                $response["id"] = $insertClPagos["ID"];
            } else {
                $response["error"] = true;
                $response["mensaje"] = $insertClPagos["mensaje"];
            }
        } else {
            $response["error"] = true;
            $response["mensaje"] = $existePagoRealizado["estado"];
        }

        echoResponse(200, $response);
    });

    $app->post('/postpago', function () use ($app) {

        $data = json_decode($app->request->getBody(), true);

        $numeroFactura = $data["numeroFactura"];

        $pagosDAO = new PagoMovilDAO();

        $existePagoRealizado = $pagosDAO->searchEstadoPagoByFactura($numeroFactura);

        if (is_bool($existePagoRealizado)) {

            $numeroCuenta = $data["numeroCelular"];
            $numeroReferencia = $data["numeroReferencia"];
            $fechaLimitePago = $data["fechaLimitePago"];
            $nombreCliente = $data["nombreCliente"];
            $nombreUsuario = $data["nombreUsuario"];
            $formaPago = $data["formaPago"];
            $valorTotal = $data["valorTotal"];
            $subTotal = $data["subtotal"];
            $iva = 0;//$app->request->post("iva");
            $origenPago = $data["origenPago"];
            $tipoTrans = $data["tipoTrans"];
            $estadoSRV = -1;//$app->request->post("estadoSRV");
            $fechaVencimiento = date("Y-m-d", strtotime($data["fechaLimitePago"]));
            $fechaInicio = date("Y-m-d H:m:s", strtotime($data["fechaInicio"]));
            $numeroIntentos = $data["numeroIntentos"];

            $metodoPago = new TarjetaCredito("", "", "",
                "", "", "", "", "", "", "",
                "", $formaPago, $nombreUsuario);

            $pagoHogar = new PagoPostpago($numeroCuenta, $numeroFactura, $numeroReferencia, $fechaLimitePago, $nombreCliente, $nombreUsuario,
                $metodoPago, $valorTotal, $subTotal, $iva, $origenPago, $tipoTrans, $estadoSRV, $fechaVencimiento,
                $fechaInicio, $numeroIntentos);

            $insertClPagos = $pagosDAO->insertPago($pagoHogar);

            if (isset($insertClPagos["ID"])) {
                $response["error"] = false;
                $response["id"] = $insertClPagos["ID"];
            } else {
                $response["error"] = true;
                $response["mensaje"] = $insertClPagos["mensaje"];
            }
        } else {
            $response["error"] = true;
            $response["mensaje"] = $existePagoRealizado["estado"];
        }

        echoResponse(200, $response);
    });

    $app->post('/actualizar', function () use ($app) {
        $data = json_decode($app->request->getBody(), true);

        $idTransaccion = $data["idTransaccion"];
        $estadoPago = $data["estadoPago"];
        $fechaTransaccion = $data["fechaTransaccion"];

        $pagosDAO = new PagoHogarDAO();

        $error = $pagosDAO->updateEstadoPago($idTransaccion, $estadoPago, $fechaTransaccion);

        $response["error"] = !$error;

        if ($estadoPago == "APPROVED") {

            $pagosDAO = new PagoDAO();
            $datosPago = $pagosDAO->searchPagoTransaccionById($idTransaccion);

            if (is_array($datosPago)) {

                if ($datosPago["TIPO_TRANS"] == 3) {

                    // Se debe enviar el correo electrónico informando el pago exitoso
                    $datosEmail = array("USUARIO" => $datosPago["NombreCliente"], "ESTADO_PAGO" => $estadoPago,
                        "FACTURA" => $datosPago["numeroFactura"],
                        "VALOR" => "$ " . number_format($datosPago["valorTotal"], 0, ',', '.'),
                        "FECHA_PAGO" => date("d/m/Y"),
                        "TELEFONO" => $datosPago["TELEFONO"], "EMAIL" => $datosPago["EMAIL"]);

                    $mail = new Mail();

                    $email = $mail->notificarPagoFacturaHogar(3, $datosEmail, $datosPago["EMAIL"]);

                    $response["mail"] = $email;
                } else {
                    $envioSMS = new Utils();

                    $r = $envioSMS->smsPospago($datosPago["numeroFactura"], $datosPago["NumeroCelular"],
                        $datosPago["CodigoCliente"]);

                    $response["mail"] = $r;
                }
            } else {
                $response["error"] = true;
                $response["mensaje"] = $datosPago;
            }
        }

        echoResponse(200, $response);
    });
});

/**
 * Grupo destinado a recibir las peticiones referentes a los pagos en GT_PAGO_PASARELA
 */

$app->group('/transaccion', function () use ($app) {

    $app->get('/debito', function () use ($app) {

        $datosRecibidos = $app->request->get();

        $estado = $datosRecibidos["transactionState"];

        if ($estado == 4) {
            $estadoPSE = 'APPROVED';
        } elseif ($estado == 5) {
            $estadoPSE = 'EXPIRED';
        } elseif ($estado == 6) {
            $estadoPSE = 'DECLINED';
        } else {
            $estadoPSE = 'PENDING';
        }

        $datosUpdate = array("estado" => $estadoPSE, "CUS" => $datosRecibidos["cus"]);

        $pagosDAO = new PagoHogarDAO();
        $result = $pagosDAO->updateTransaccionDebito($datosRecibidos["valor_llave"], $datosUpdate);

        $app->redirect("https://pruebasclaro.maxgp.com.co:4443/NodeJS/confirmacionpago?idTransaccion="
            .$datosRecibidos["IDTransaccion"]."&formaPago=".$datosRecibidos["formaPago"]
            ."&nombreUsuario=".$datosRecibidos["nombreUsuario"]."&tipoTrans=" . $datosRecibidos["tipoTrans"]);

        echoResponse(200, $datosUpdate);
    });

    $app->post('/debito', function () use ($app) {

        session_start();

        $data = json_decode($app->request->getBody(), true);

        if (isset($data["IDTransaccion"])) {
            // Se recupera el ID Transaccion del pago
            $idTransaccion = $data["IDTransaccion"];

            // Validar si el pago existe en la tabla CL_PAGOSCLARO
            $pagosDAO = new PagoDAO();
            $existePago = $pagosDAO->searchPagoById($idTransaccion);
            /*$pagosDAO = new PagoHogarDAO();
            $existePago = $pagosDAO->searchPagoById($idTransaccion);*/

            if (is_array($existePago)) {
                $nombreUsuario = $data["nombreUsuario"];
                $formaPago = $existePago["FORMA_PAGO"];

                // Datos de la Tarjeta Débito
                // ID Banco, NombreTitular, TipoCliente (N,J), TipoDocumento, NumeroDocumento, email, #telefono
                $numeroBanco = $data["IDBanco"];
                $nombreClientePSE = $data["nombreTitular"];//$nombreCliente;//
                $telefonoClientePSE = $data["telefonoCliente"];//$telefonoCliente;//
                $emailClientePSE = $data["emailCliente"];//$emailCliente;//
                $tipoCliente = $data["tipoCliente"];//$tipoCliente;//
                $numeroDocumento = $data["numeroDocumentoCliente"];//$numeroDocumento;//
                $tipoDocumento = $data["tipoDocumentoCliente"];//$tipoDocumento;//

                $tarjetaDebito = new TarjetaDebito($numeroBanco, $nombreClientePSE, $telefonoClientePSE, $emailClientePSE,
                    $tipoCliente, $formaPago, $nombreUsuario, $tipoDocumento, $numeroDocumento);

                $existePago["numeroIntentos"] = $tarjetaDebito->getNumeroIntentos($existePago["numeroFactura"]);
                $existePago["IDTransaccion"] = $idTransaccion;
                $existePago["descripcion"] = (isset($data["descripcionCompra"])) ? $data["descripcionCompra"] : "Pago Gateway APP";

                /*$pagoHogar = new PagoHogar($numeroCuenta, $numeroFactura, $fechaLimitePago, $nombreCliente,
                    $nombreUsuario, $tarjetaDebito, $valorTotal, $subTotal, $iva, $origenPago, $tipoTrans, $estadoSRV,
                    $fechaVencimiento, $fechaInicio, $tarjetaDebito->getNumeroIntentos($numeroFactura));

                $pagoHogar->setIDTransaccion($idTransaccion);*/

                $insercionGT = $pagosDAO->insertTransaccionDebito($existePago, $tarjetaDebito);

                if ($insercionGT == true) {
                    $existePago["registroPago"] = "exito";
                    //$pagoHogar->setRegistroPago("exito");
                    //$response["datos"] = $pagoHogar->getDatosPago();
                    $responsePayU = $tarjetaDebito->pagar($existePago);

                    $response["error"] = $responsePayU["error"];

                    if ($responsePayU["error"] == false) {
                        // Se completo la transacción sin errores
                        $response["transaccion"] = $responsePayU["transaccion"];
                        $response["respuesta"] = $responsePayU["respuesta"];
                        // CUS, Empresa, NIT, IP, Fecha, Estado, # Transacción

                        $urlBanco = $responsePayU["respuesta"]["BANK_URL"];

                        //$app->redirect($urlBanco);
                    } else {
                        // Hubo errores al completar la transacción
                        $response["mensaje"] = $responsePayU["mensaje"];
                        $response["resultado"] = $responsePayU["transaccion"];
                    }
                } else {
                    // No se completó el registro de la transacción en GT_PAGO_PASARELA
                    //$pagoHogar->setRegistroPago("fracaso");
                    $existePago["registroPago"] = "fracaso";
                    $response["error"] = true;
                    $response["mensaje"] = "Problema al procesar la transacción";
                }
            } else {
                $response["error"] = true;
                $response["mensaje"] = $existePago;
            }
        } else {
            $response["error"] = true;
            $response["mensaje"] = "ID no válido para el pago";
        }

        session_destroy();

        echoResponse(200, $response);
    });

    $app->post('/credito', function () use ($app) {

        session_start();

        $data = json_decode($app->request->getBody(), true);

        if (isset($data["IDTransaccion"])) {
            // Se recupera el ID Transaccion del pago
            $idTransaccion = $data["IDTransaccion"];

            // Validar si el pago existe en la tabla CL_PAGOSCLARO
            $pagosDAO = new PagoDAO();
            $existePago = $pagosDAO->searchPagoById($idTransaccion);

            if (is_array($existePago)) {

                // Datos de la Tarjeta Crédito
                $formaPago = $existePago["FORMA_PAGO"];
                $nombreTarjetaHabiente = $data["nombreTarjeta"];//$nombreCliente;//
                $emailTarjetaHabiente = $data["emailTarjeta"];//$nombreUsuario;//
                $telefonoTarjetaHabiente = $data["telefonoTarjeta"];//"8907676";//
                $tipoDocumento = $data["tipoDocumentoTarjeta"];//"CC";//
                $numeroDocumento = $data["numeroDocumentoTarjeta"];//"101010";//
                $cuotas = $data["cuotas"];//"8";//
                $numero = $data["numeroTarjeta"];//"4111111111111111";//
                $CVV = $data["CVVTarjeta"];//"9012";//
                $mesVencimiento = $data["mesVencimientoTarjeta"];//"09";//
                $anoVencimiento = $data["anoVencimientoTarjeta"];//"2020";//
                $franquicia = $data["franquiciaTarjeta"];//"VISA";//
                $nombreUsuario = $data["nombreUsuario"];

                $tarjetaCredito = new TarjetaCredito($nombreTarjetaHabiente, $emailTarjetaHabiente, $telefonoTarjetaHabiente,
                    $tipoDocumento, $numeroDocumento, $cuotas, $numero, $CVV, $mesVencimiento, $anoVencimiento, $franquicia,
                    $formaPago, $nombreUsuario);

                $existePago["numeroIntentos"] = $tarjetaCredito->getNumeroIntentos($existePago["numeroFactura"]);
                $existePago["IDTransaccion"] = $idTransaccion;
                $existePago["descripcion"] = (isset($data["descripcionCompra"])) ? $data["descripcionCompra"] : "Pago Gateway APP";

                $insercionGT = $pagosDAO->insertTransaccionCredito($existePago, $tarjetaCredito);

                if ($insercionGT == true) {
                    $existePago["registroPago"] = "exito";
                    $existePago["formaPago"] = $formaPago;
                    $existePago["idObjeto"] = $data["idObjeto"];

                    $responsePayU = $tarjetaCredito->pagar($existePago);

                    $response["error"] = $responsePayU["error"];

                    if ($responsePayU["error"] == false) {
                        // Se completo la transacción sin errores
                        $response["transaccion"] = $responsePayU["transaccion"];
                        $response["respuesta"] = $responsePayU["respuesta"];
                        // CUS, Empresa, NIT, IP, Fecha, Estado, # Transacción

                    } else {
                        // Hubo errores al completar la transacción
                        $response["mensaje"] = $responsePayU["mensaje"];
                        $response["resultado"] = $responsePayU["transaccion"];
                    }
                } else {
                    // No se completó el registro de la transacción en GT_PAGO_PASARELA
                    //$pagoHogar->setRegistroPago("fracaso");
                    $response["error"] = true;
                    $response["mensaje"] = "Problema al procesar la transacción";
                }
            } else {
                $response["error"] = true;
                $response["mensaje"] = $existePago;
            }
        } else {
            $response["error"] = true;
            $response["mensaje"] = "ID no válido para el pago";
        }

        session_destroy();

        echoResponse(200, $response);
    });

    $app->post('/tokenizadas', function () use ($app) {

        session_start();

        $data = json_decode($app->request->getBody(), true);

        if (isset($data["IDTransaccion"])) {
            // Se recupera el ID Transaccion del pago
            $idTransaccion = $data["IDTransaccion"];

            // Validar si el pago existe en la tabla CL_PAGOSCLARO
            $pagosDAO = new PagoDAO();
            $existePago = $pagosDAO->searchPagoById($idTransaccion);
            /*$pagosDAO = new PagoHogarDAO();
            $existePago = $pagosDAO->searchPagoById($idTransaccion);*/

            if (is_array($existePago)) {
                // Validar la tarjeta Tokenizada seleccionada
                $tokeNumero = str_replace("TK_","", $data["tokeNumero"]);

                $tarjetasTKDAO = new TarjetaTokenizadaDAO();
                $existeTarjetaTK = $tarjetasTKDAO->searchTarjetaById($tokeNumero);

                if (is_array($existeTarjetaTK)) {

                    $formaPago = $existePago["FORMA_PAGO"];

                    // Extraer los datos de la tarjeta seleccionada
                    /*
                    $nombreTarjetaHabiente = $existeTarjetaTK["NOMBRE"];
                    $emailTarjetaHabiente = $existeTarjetaTK["EMAIL"];
                    $telefonoTarjetaHabiente = $existeTarjetaTK["EMAIL"];
                    $tipoDocumento = $existeTarjetaTK["TIPO_DOCUMENTO"];
                    $numeroDocumento = $existeTarjetaTK["#DOCUMENTO"];
                    $cuotas = $data["cuotas"];
                    $franquicia = $existeTarjetaTK["FRANQUICIA"];*/

                    $tarjetaTokenizada = new TarjetaTokenizada($formaPago, $existeTarjetaTK["EMAIL"],
                        $existeTarjetaTK["TIPO_DOCUMENTO"], $existeTarjetaTK["#DOCUMENTO"]);

                    $tarjetaTokenizada->setTarjetaHabiente($existeTarjetaTK["NOMBRE"]);
                    $tarjetaTokenizada->setCelularSMS($existeTarjetaTK["TELEFONO"]);
                    $tarjetaTokenizada->setCelularRef($existeTarjetaTK["TELEFONO"]);
                    $tarjetaTokenizada->setCuotas($data["cuotas"]);
                    $tarjetaTokenizada->setFranquicia($existeTarjetaTK["FRANQUICIA"]);
                    $tarjetaTokenizada->setToken($existeTarjetaTK["TOKEN"]);

                    if (strpos($existeTarjetaTK["FRANQUICIA"] ,"CODENSA") !== false) {
                        $tarjetaTokenizada->setCVV($data["CVV"]);
                    }

                    $existePago["numeroIntentos"] = $tarjetaTokenizada->getNumeroIntentos($existePago["numeroFactura"]);
                    $existePago["IDTransaccion"] = $idTransaccion;
                    $existePago["descripcion"] = (isset($data["descripcionCompra"])) ? $data["descripcionCompra"] : "Pago Gateway APP";

                    /*$pagoHogar = new PagoHogar($numeroCuenta, $numeroFactura, $fechaLimitePago, $nombreCliente,
                        $nombreUsuario, $tarjetaTokenizada, $valorTotal, $subTotal, $iva, $origenPago, $tipoTrans, $estadoSRV,
                        $fechaVencimiento, $fechaInicio, $tarjetaTokenizada->getNumeroIntentos($numeroFactura));

                    $pagoHogar->setIDTransaccion($idTransaccion);*/

                    $insercionGT = $pagosDAO->insertTransaccionTokenizada($existePago, $tarjetaTokenizada);
                    //$insercionGT = $pagosDAO->insertTransaccionTokenizada($pagoHogar, $tarjetaTokenizada);

                    if ($insercionGT == true) {
                        $existePago["registroPago"] = "exito";
                        $existePago["formaPago"] = $data["formaPago"];
                        $existePago["idObjeto"] = $data["idObjeto"];
                        //$pagoHogar->setRegistroPago("exito");
                        //$response["datos"] = $pagoHogar->getDatosPago();
                        $responsePayU = $tarjetaTokenizada->pagar($existePago);

                        $response["error"] = $responsePayU["error"];

                        if ($responsePayU["error"] == false) {
                            // Se completo la transacción sin errores
                            $response["transaccion"] = $responsePayU["transaccion"];
                            $response["respuesta"] = $responsePayU["respuesta"];
                        } else {
                            // Hubo errores al completar la transacción
                            $response["mensaje"] = $responsePayU["mensaje"];
                        }
                    } else {
                        // No se completó el registro de la transacción en GT_PAGO_PASARELA
                        //$pagoHogar->setRegistroPago("fracaso");
                        $existePago["registroPago"] = "fracaso";
                        $response["error"] = true;
                        $response["mensaje"] = "Problema al procesar la transacción";
                    }
                } else {
                    $response["error"] = true;
                    $response["mensaje"] = $existeTarjetaTK;
                }

            } else {
                $response["error"] = true;
                $response["error"] = $existePago;
            }
        } else {
            $response["error"] = true;
            $response["mensaje"] = "ID no válido para el pago";
        }

        session_destroy();

        echoResponse(200, $response);
    });

    $app->post('/payUTeFia', function () use ($app) {

        session_start();

        $data = json_decode($app->request->getBody(), true);

        if (isset($data["IDTransaccion"]) || isset($data["idObjeto"]) || isset($data["nombreUsuario"])
            || isset($data["nombreUsuario"]) || isset($data["nombreCliente"])) {
            // Se recupera el ID Transaccion del pago
            // idObjeto, nombreUsuario, NombreCliente
            $idTransaccion = $data["IDTransaccion"];

            // Validar si el pago existe en la tabla CL_PAGOSCLARO
            $pagosDAO = new PagoDAO();
            $existePago = $pagosDAO->searchPagoById($idTransaccion);

            if (is_array($existePago)) {
                $nombreUsuario = $data["nombreUsuario"];
                $formaPago = $existePago["FORMA_PAGO"];
                $signature = $data["signature"];

                // Datos de la Tarjeta Débito
                // ID Banco, NombreTitular, TipoCliente (N,J), TipoDocumento, NumeroDocumento, email, #telefono
                $numeroBanco = $data["IDBanco"];
                $nombreClientePSE = $data["nombreTitular"];//$nombreCliente;//
                $telefonoClientePSE = $data["telefonoCliente"];//$telefonoCliente;//
                $emailClientePSE = $data["emailCliente"];//$emailCliente;//
                $tipoCliente = $data["tipoCliente"];//$tipoCliente;//
                $numeroDocumento = $data["numeroDocumentoCliente"];//$numeroDocumento;//
                $tipoDocumento = $data["tipoDocumentoCliente"];//$tipoDocumento;//

                $tarjetaDebito = new TarjetaDebito($numeroBanco, $nombreClientePSE, $telefonoClientePSE, $emailClientePSE,
                    $tipoCliente, $formaPago, $nombreUsuario, $tipoDocumento, $numeroDocumento);

                $payUTeFia = new PayUTeFia($formaPago, $nombreUsuario, $tipoDocumento, $numeroDocumento);

                $existePago["numeroIntentos"] = $payUTeFia->getNumeroIntentos($existePago["numeroFactura"]);
                $existePago["IDTransaccion"] = $idTransaccion;
                $existePago["descripcion"] = (isset($data["descripcionCompra"])) ? $data["descripcionCompra"] : "Pago Gateway APP";

                $insercionGT = $pagosDAO->insertTransaccionDebito($existePago, $tarjetaDebito);

                if ($insercionGT == true) {
                    $existePago["registroPago"] = "exito";
                    $responsePayU = $payUTeFia->pagar($existePago);

                    $response["error"] = $responsePayU["error"];

                    if ($responsePayU["error"] == false) {
                        // Se completó la transacción sin errores
                        $response["transaccion"] = $responsePayU["transaccion"];
                        $response["respuesta"] = $responsePayU["respuesta"];
                        // CUS, Empresa, NIT, IP, Fecha, Estado, # Transacción

                        # Se retorna la URL para completar el pago
                        $urlPayUTEeFia = $responsePayU["respuesta"]["BANK_URL"];

                        //$app->redirect($urlBanco);
                    } else {
                        // Hubo errores al completar la transacción
                        $response["mensaje"] = $responsePayU["mensaje"];
                        $response["resultado"] = $responsePayU["transaccion"];
                    }
                } else {
                    // No se completó el registro de la transacción en GT_PAGO_PASARELA
                    $existePago["registroPago"] = "fracaso";
                    $response["error"] = true;
                    $response["mensaje"] = "Problema al procesar la transacción";
                }
            } else {
                $response["error"] = true;
                $response["mensaje"] = $existePago;
            }
        } else {
            $response["error"] = true;
            $response["mensaje"] = "ID no válido para el pago";
        }

        session_destroy();

        echoResponse(200, $response);
    });

    /**
     * Servicios que retornar la información del pago en la vista de confirmación dependiendo el flujo
     */

    $app->get('/hogar', function () use ($app) {

        if ($app->request->get("idTransaccion")) {
            $id = $app->request->get("idTransaccion");

            $pagosDAO = new PagoHogarDAO();

            $response["error"] = false;
            $response["pago"] = $pagosDAO->searchTransaccionById($id);
        } else {
            $response["error"] = true;
            $response["mensaje"] = "Parámetros vacios";
        }

        echoResponse(200, $response);
    });

    $app->get('/postpago', function () use ($app) {

        if ($app->request->get("idTransaccion")) {
            $id = $app->request->get("idTransaccion");

            $pagosDAO = new PagoMovilDAO();

            $response["error"] = false;
            $response["pago"] = $pagosDAO->searchTransaccionById($id);
        } else {
            $response["error"] = true;
            $response["mensaje"] = "Parámetros vacios";
        }

        echoResponse(200, $response);
    });
});

$app->run();

/********** Funciones Utilitarias para procesar peticiones **********/

function echoResponse($status_code, $response)
{
    $app = \Slim\Slim::getInstance();
    // Código de la respuesta http
    $app->status($status_code);

    // Se retorna la respuesta en formato JSON
    echo json_encode($response);
}

/**
 * Validación de Token enviado en la petición
 * @param \Slim\Route $route
 * @throws \Slim\Exception\Stop
 */

function authenticate(\Slim\Route $route)
{
    // Obteniendo cabeceras de la peticion
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verificando la cabecera con la Autorizacion
    if (isset($headers['Token'])) {
        $token = $headers['Token'];

        $data = json_decode($app->request->getBody(), true);

        // Validando KEY
        if (!($token == hash('sha256', $data["valor"] . $data["numeroReferencia"]) . $data["fecha"])) {
            // Caso negativo
            $response['error'] = true;
            $response['mensaje'] = "Acceso denegado. Token inválido";
            echoResponse(401, $response);

            $app->stop();
        } else {
            // Se ejecuta la petición
        }
    } else {
        // No viene Token en la petición
        $response['error'] = true;
        $response['mensaje'] = "Falta token de autorización";
        echoResponse(400, $response);

        $app->stop();
    }
}
?>
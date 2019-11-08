<?php

//referencia generado con MD5(uniqid(&lt;some_string&gt;, true))
define('API_KEY','3d524a53c110e4c22463b10ed32cef9d');

// HOST
define('NOMBRE_HOST', 'https://pruebasclaro.maxgp.com.co:4443/APIGateway/v1/');

// PATH
define ('PATH_CONSULTAS','../src/consultas/');

//-----------------------------------------------------------------------------------
// BASE DE DATOS
//-----------------------------------------------------------------------------------
// Definicion de base de datos  en la cual se instalo el producto ORACLE, MYSQL, MSSQL (SQL SERVER), POSTGRESS.
define ('TIPO_DB','MSSQL');
define ('TIPO_DB_REPORTE','ODBC');

// Nombre de la base de datos con tipo de licenciamiento ASP o PERPETUIDAD, no aplica para Oracle.
define ('DB', 'rgtechex_gateway');

// Usuario que tendra aaceso a los objetos del producto en lo referente a BD.
define ('USUARIO','desa_gateway');

// Contraseña o Password del usuario anterior.
define ('CLAVE','G1nfdesa2018**');

// Puerto.
define ('PUERTO','');

// IP o nombre de la maquina en la que se instalo el producto en lo referente a BD.
define ('SERVIDOR','10.75.95.3, 3143');
define ('SERVIDOR_REPORTE','Driver={SQL Server Native Client 11.0};Server='.SERVIDOR.';Database='.DB.';');

// Instancia de la base de datos, exclusivo para Oracle.
define ('INSTANCIA','');

define ('ASP_SERVICE', 1);

define ('SQL_GENERAL', 2);

//define('API_KEY','3d524a53c110e4c22463b10ed32cef9d');

//------------------------------------------------------------------------------------
// CONFIGURACI�N DE CUENTA PARA PAYU
//------------------------------------------------------------------------------------

// Api key
// PRODUCCION define ('PAYU_APIKEY','');
define ('PAYU_APIKEY','4Vj8eK4rloUd272L48hsrarnUA');
define ('PAYU_APIKEY_PSE','4Vj8eK4rloUd272L48hsrarnUA');

// Api login
// PRODUCCION define ('PAYU_APILOGIN','');
define ('PAYU_APILOGIN','pRRXKOl8ikMmt9u');
define ('PAYU_APILOGIN_PSE','pRRXKOl8ikMmt9u');

// Account Id
// PRODUCCION define ('PAYU_ACCOUNTID','');
define ('PAYU_ACCOUNTID','512321');
define ('PAYU_ACCOUNTID_PSE','512321');

// Merchant Id
// PRODUCCION define ('PAYU_MERCHANTID','');
define ('PAYU_MERCHANTID','508029');
define ('PAYU_MERCHANTID_PSE','508029');

// URL
//define ('PAYU_URL',"https://api.payulatam.com/payments-api/4.0/service.cgi");
define ('PAYU_URL',"https://sandbox.api.payulatam.com/payments-api/4.0/service.cgi");

// URL API Reports
//define ('PAYU_URL_REPORTS', "https://api.payulatam.com/reports-api/4.0/service.cgi");
define ('PAYU_URL_REPORTS',"https://sandbox.api.payulatam.com/reports-api/4.0/service.cgi");

// URL API Payments
//define ('PAYU_URL_PAYMENTS',"https://api.payulatam.com/payments-api/4.0/service.cgi");
define ('PAYU_URL_PAYMENTS',"https://sandbox.api.payulatam.com/payments-api/4.0/service.cgi");

// URL API Subscriptions
//define ('PAYU_URL_SUBSCRIPTIONS',"https://api.payulatam.com/payments-api/rest/v4.3/");
define ('PAYU_URL_SUBSCRIPTIONS',"https://sandbox.api.payulatam.com/payments-api/rest/v4.3/");

// URL Validacion PayUTeFia
//define('PAYU_URL_VALIDACION_PAYUTEFIA', 'https://api.payulatam.com/payments-api/rest/v4.3/paymentMethods/LENDING');
define('PAYU_URL_VALIDACION_PAYUTEFIA', 'https://sandbox.api.payulatam.com/payments-api/rest/v4.3/paymentMethods/LENDING');

// Is a test
//define ('PAYU_TEST','0');
define ('PAYU_TEST','1');

// Consulta el valor de la transaccion
define ('PAYU_VALOR','claro::consultaValor');

// Error de integraci�n PayU
define ('MSG_ERROR_PAYU',"Se�or usuario en este momento no podemos finalizar tu pago, tu transacci�n no fue procesada, revisa que el valor no fue descontado de tu cuenta o tarjeta e intenta nuevamente.");
define ('MSG_ERROR_EXCE',"El monto de la transaccion excede los l�mites establecidos en PSE para la <b>Claro Colombia</b>, por favor comuniquese con Claro Colombia a nuestras l�neas de atenci�n al cliente al tel�fono #TELEFONO# o al correo electr�nico #EMAIL#.");
define ('MSG_ERROR_UNRE',"La entidad financiera no puede ser contactada para iniciar la transaccion, por favor selecciones otra o intente mas tarde.");
define ('MSG_ERROR_INTE',"No se pudo crear la transacci�n, por favor intente mas tarde o comuniquese con la <b>Claro Colombia</b> a nuestras l�neas de atenci�n al cliente al tel�fono #TELEFONO# o al correo electr�nico #EMAIL#.");

//---------------------------------------------------------------
// Definicion de configuraciones para ACH
//---------------------------------------------------------------

// Direcci�n URL en donde el Sistema PSE expone los Web Services a la Empresa
define('ACH_URL', 'http://172.24.43.50:8080/Servicio/Services/MainServices1.asmx?wsdl');

// URL que la Empresa puede usar como pagina de retorno (URL a la que el usuario ser� redireccionado cuando el usuario haya hecho el d�bito en el Banco)
define('ACH_PPE_URL', 'https://pruebasclaro.maxgp.com.co/');

// Nit de la Empresa, con el cual fue registrado en el Sistema PSE
define('ACH_PPE_CODE', "8001539937");

//
define('ACH_SERVICE_CODE', "1000211");

// opcion 1 test si opcion 2 test no
define('ACH_TEST', '2');

// URL de la operacion del soap action
define('ACH_SOAP_URIACTION', "http://tempuri.org");

// namespace para limpar la rspuesta xml del soap
define('ACH_SOAP_NAMESPACES', "soap:,wsu:,xsi:,xsd:,wsa:,wsse:,wsu:");

//-----------------------------------------------------------------------------------
// PARAMETROS SERVIDOR DE CORREO
//-----------------------------------------------------------------------------------
// Puerto
define ('PUERTOCORREO',25);

// Tipo de servidor de correo ("mail", "sendmail", or "smtp").
define ('MAILER','smtp');

// Direccion del servidor de correo
define ('HOSTCORREO','172.22.85.125');

// Conecion con autenticacion o no (true o false)
define ('AUTH', false);

// Usuario de aceso al correo
define ('USRCORREO','Informacion@claro.com.co');

// Password de acceso al correo
define ('PASSCORREO','Inf0dec2017*');

// Correo de del remitente
define ('CORREOFROM','Informacion@claro.com.co');

// NOmbre del remitente
define ('NOMBREFROM','Administrador Claro');

// Tiempo de espera en segundos
define ('TIMEOUT',120);

//-----------------------------------------------------------------------------------
// PARAMETROS LOG EN ARCHIVOS
//-----------------------------------------------------------------------------------
define('LOG_FILE', 0);
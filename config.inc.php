<?php

include_once ('libs/adodb5/adodb.inc.php');
include_once ('libs/adodb5/adodb-pager.inc.php');

include_once ('include/Consulta.php');

include_once ('model/pasarelas/Pasarela.php');
include_once ('model/pasarelas/PayU.php');

include_once('model/mediosDePago/MedioPago.php');
include_once('model/mediosDePago/TarjetaDebito.php');
include_once('model/mediosDePago/TarjetaCredito.php');
include_once('model/mediosDePago/TarjetaTokenizada.php');
include_once('model/mediosDePago/PayUTeFia.php');

include_once ('model/ITarjetaTokenizada.php');
include_once ('model/TarjetaTokenizadaDAO.php');

include_once ('model/pagos/Pago.php');
include_once ('model/pagos/PagoHogar.php');
include_once ('model/pagos/PagoPostpago.php');

#include_once ('model/IClPagosClaro.php');
include_once ('model/ITransaccionPago.php');
include_once ('model/APagosClaro.php');
include_once ('model/PagoDAO.php');
include_once ('model/PagoHogarDAO.php');
include_once ('model/PagoMovilDAO.php');

include_once ('model/utils/Mail.php');
include_once ('model/utils/Utils.php');

include_once ('include/Config.var.php');

?>
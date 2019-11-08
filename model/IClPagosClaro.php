<?php

interface IClPagosClaro
{
    public function searchPagoById($id);

    public function updatePago($id);

    public function updateEstadoPago($id, $estadoPago, $fechaTransaccion);

    public function insertPago(Pago $pago);

    public function searchEstadoPagoByFactura($numeroFactura);
}
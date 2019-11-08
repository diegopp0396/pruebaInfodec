<?php

interface ITransaccionPago
{
    public function insertTransaccionDebito(Array $pago, TarjetaDebito $medioPago);

    public function updateTransaccionDebito($id, $datosUpdate);

    public function insertTransaccionCredito(Array $pago, TarjetaCredito $medioPago);

    public function insertTransaccionTokenizada(Array $pago, TarjetaTokenizada $medioPago);

    public function insertTransaccionPayUTeFia(Array $pago, PayUTeFia $medioPago);

    public function searchTransaccionById($idTransaccion);
}
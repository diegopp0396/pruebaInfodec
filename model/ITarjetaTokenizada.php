<?php

interface ITarjetaTokenizada
{
    public function selectTarjetasTokenizadas();
    public function searchTarjetaByEmail($email);
    public function searchTarjetaById($id);
    public function insertTarjetaTokenizada();

}
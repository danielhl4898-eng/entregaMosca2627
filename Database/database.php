<?php

require_once __DIR__ . '/parametros.php';

// =================== DATABASE ===================
class Database {

public function connect() {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        return new mysqli(
            Parametros::$host,
            Parametros::$usuario,
            Parametros::$clave,
            Parametros::$bd
        );

    } catch (mysqli_sql_exception $e) {
        echo 'Error MySQL: ' . $e->getMessage() . '<br>';
        echo 'Código: ' . $e->getCode() . '<br>';
        throw $e;
    }
}
}

<?php
/*el publico se registrará al juego de la mosca, cada usuario tiene 1 tablero activo

hay que juntar el CRUDusuarios con el juego de la mosca

para acceder a tu tablero, te tienes que loggear primero

se puede crear un tablero, solo si el tablero que tiene no está activo

AÑADIR roles jugador//administrador
jugador: puede jugar (darManotazo(), rendirse())
administrador: SOLO puede acceder al CRUD: eliminar, actualizar usuarios... no puede acceder al juego

al registrarse un jugador nuevo, tiene que ser como jugador

necesitamos tabla usuarios (idUsu, idRol, nombre, etc) tabla roles(idRol, nombre, desc) tabla tableros(idTablero, tabl, activo, idUsu)
*/
header("Content-Type: application/json");
require_once("auxiliar.php");
require_once("controllers/controller.php");
require_once("Database/database.php");
require_once("Database/DAO.php");
require_once("controllers/controladorJuego.php");
$db = new Database();
$dao = new DAO($db);
$contr = new controller($dao);
$contrJ = new controladorJuego($dao);
$verbo = $_SERVER["REQUEST_METHOD"];
$url = $_SERVER["REQUEST_URI"];
$jsonpar= file_get_contents("php://input");
$parametros= json_decode($jsonpar, true);

if($verbo !="POST"&&$verbo !="PATCH"&&$verbo !="GET"&&$verbo !="DELETE"){
    auxiliar::metodoNP();//done
}else{
    $opciones = explode("/", $url);
    unset($opciones[0]);
    switch($verbo){
        case "GET":
            $contr->casoGET($opciones[1], $parametros);
            break;
        case "DELETE":
            $contr->casoDELETE($opciones, $parametros);
            break;
        case "POST":
            $contr-> casoPOST($opciones, $parametros, $contrJ);
            break;
    }
}

?>
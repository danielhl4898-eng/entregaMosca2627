<?php
class Auxiliar{
    static function metodoNP(){
        http_response_code(405);
        echo json_encode([
        "ok"=>false,
        "error"=>"Método no permitido"
        ]);
    }
    static function datosVacios(){
        http_response_code(400);
        echo json_encode([
        "ok" => false,
        "error"=> "No se han recibido datos"
        ]);
    }
    static function datosInvalidos(){
        http_response_code(400);
        echo json_encode([
        "error" => "Parámetros incorrectos",
        "ok" =>false
        ]);
    }
    static function opcionEq(){
        http_response_code(400);
        echo json_encode([
            "error" => "Opción equivocada",
            "ok" => false
        ]);
   }
   static function usuBorr(){
    http_response_code(200);
        echo json_encode([
            "ok"=> true,
            "Se ha eliminado el usuario correctamente" => true
        ]);
    }
   static function error(){
    http_response_code(400);
    echo json_encode([
        "ok" => false,
        "Se ha producido un error" => "¿maybe usuario no existe?"
    ]);
   }
   static function hecho(){
    http_response_code(200);
    echo json_encode([
        "ok"=> true,
        "mensaje"=>"Se ha completado la solicitud correctamente"
    ]);
   }
   static function sinPartidas(){
    http_response_code(404);
    echo json_encode([
        "ok"=> false,
        "mensaje"=>"No hay partidas activas"
    ]);
   }
   static function adminNoJuega(){
    http_response_code(403);
    echo json_encode([
        "ok"=>false,
        "mensaje"=> "Un administrador no puede jugar al juego"
    ]);
   }
   static function loginIncorrecto(){
    http_response_code(401);
    echo json_encode([
        "ok"=>false,
        "mensaje"=> "Algún parametro incorrecto"
    ]);
   }
   static function yaHayPartidas(){
    http_response_code(409);
    echo json_encode([
        "ok"=>false,
        "mensaje"=> "Este usuario ya tiene una partida creada"
    ]);
   }
   static function noExistenUsers(){
    http_response_code(404);
    echo json_encode([
        "ok"=>false,
        "mensaje"=> "No existe ningún usuario (¿cómo has llegado a este punto?)"
    ]);
   }
   static function soloAdmin(){
    http_response_code(403);
    echo json_encode([
        "ok"=>false,
        "mensaje"=>"Solo pueden borrar usuarios los administradores"
    ]);
   }
   static function creada(){
    http_response_code(201);
    echo json_encode([
        "ok"=> true,
        "mensaje"=> "Se ha creado correctamente la partida"
    ]);
   }
   static function cambioCorrecto(){
    http_response_code(200);
    echo json_encode([
        "ok"=> true,
        "mensaje"=> "Se ha completado el cambio correctamente"
    ]);
   }
   static function yaExisteUser(){
    http_response_code(409);
    echo json_encode([
        "ok"=> false,
        "mensaje"=> "Ya existe un usuario con ese nombre"
    ]);
   }
   static function errorCorreoInvalido(){
    http_response_code(401);
    echo json_encode([
        "ok"=>false,
        "mensaje"=> "Correo inválido"
    ]);
   }
   static function errorCorreo(){
    http_response_code(400);
    echo json_encode([
        "ok"=>false,
        "mensaje"=> "Ha sucedido un error al enviar el correo"
    ]);
   }
   static function correoEnviado(){
    http_response_code(200);
    echo json_encode([
        "ok"=>true,
        "mensaje"=> "Se ha enviado el correo correctamente"
    ]);
   }
   static function contraCambiada(){
    http_response_code(201);
    echo json_encode([
        "ok"=>true,
        "mensaje"=> "Se ha cambiado la contraseña correctamente"
    ]);
   }
   static function emailCambiado(){
    http_response_code(201);
    echo json_encode([
        "ok"=>true,
        "mensaje"=> "Se ha cambiado la contraseña correctamente"
    ]);
   }
}

?>
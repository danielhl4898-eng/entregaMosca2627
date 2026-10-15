<?php
include_once("tablero.php");

class controladorJuego{
    public $dao;
    public $t;
    public function __construct($dao){
        $this->dao = $dao;
    }

    public function jugar($partida, $parametros){
        $tableroPartidaActual = new Tablero();
        $tableroPartidaActual->tablero = array_map("intval", str_split($partida["tablero"]));
        //array_map convierte valores a un array, y str_split va carácter a carácter y guardándolo en la siguiente posición del array
        $tirada = $parametros["casilla"];
        $partidaAct=$tableroPartidaActual->comprobarMosca($tirada,$partida);
        $partidaAct["tablero"] = implode("", $tableroPartidaActual->tablero);
        $this->dao->actualizarPartida($partidaAct);

    }

/*
    static function juego($parametros, $link, $verbo, $moscaDAO){
        $opcion=explode("/",$link);
        $rendicion=false;
        $creando=false;

        if($verbo == "PUT"){
            $t = new Tablero(10,5);
            $t -> colocarMosca();
            unset($opcion[0]);
            if($opcion[1] == "rendirse"){
                self::rendirse($t);
                $rendicion=true;
            }else{
                auxiliar::opcionEq();
            }
        }else if($verbo == "POST" && $opcion[1] !=""){
            if($opcion[1] =="crear"){


                $t = new Tablero($parametros["tam"],$parametros["intentos"]);
                $t -> colocarMosca();
                $dao -> crearPartida($parametros["tam"], $parametros["intentos"], $t->tablero);
                $creando=true;
            }else{
                auxiliar::opcionEq();
            }
        }else{
            $t = new Tablero(10, 5);
            $t->colocarMosca();
        }
        if(!$rendicion && !$creando){
            $t -> comprobarMosca($parametros);
        }
    }
*/
    public static function rendirse($partida){
        $tableroPartidaActual = new Tablero();
        $tableroPartidaActual->tablero = array_map("intval", str_split($partida["tablero"]));
        $posiMosca=$tableroPartidaActual->devolverPosicionMosca();
        echo json_encode([
                "ok"=> "true",
                "Partida"=> "rendida",
                "posicion de la mosca final"=>$posiMosca
            ]);
   }

   


}














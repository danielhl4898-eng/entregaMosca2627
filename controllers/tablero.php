<?php

class Tablero{
    public $id;
    public $tablero;
    public $tamano=10;
    public $intentos=5;

    public function __construct($tamano=10,$intentos= 5){
        $this->tamano = $tamano;
        $this->intentos = $intentos;
        $this->tablero = array_fill(0, $this->tamano, 0);
    }
    public function colocarMosca(){
        
        $pos = rand(0, $this->tamano -1);
        $this->tablero[$pos]=2;
        if($pos-1 != -1){
            $this->tablero[$pos-1]=1;
        }
        if($pos+1 != $this->tamano){
            $this->tablero[$pos+1]=1;
        }
    }

    public function comprobarMosca($posicion, $partida){
        http_response_code(200);
        
        $posiAComprobar = $posicion-1;
        $partida["intentos"] -=1;
        if($partida["intentos"] == 0){
            $partida["activo"] = 0;
            echo json_encode([
                "ok"=> "true",
                "Partida"=> "acabada",
                "posicion de la mosca final"=>$this->devolverPosicionMosca()
            ]);
        }else{
            if($this->tablero[($posiAComprobar)] == 2){
                $partida["activo"] =0;
                echo json_encode([
                    "Menudo bimbazo!!1!" => "Le has dado a la mosca",
                    "Intentos sobrantes" => $partida["intentos"]
                ]);
            }else if($this->tablero[$posiAComprobar] == 1){
                $this->tablero = array_fill(0, $this->tamano, 0);
                $this -> colocarMosca();
                
                echo json_encode([
                    "vaya panoli" => "casi le das, se ha movido",
                    "intentos restantes" => $partida["intentos"]
                ]);
            }else{
                
                echo json_encode([
                    "buah" => "ni cerca",
                    "Intentos restantes"=>$partida["intentos"]
                ]);
            }
        }
        return $partida;
    }

    public function mostrarTablero(){
        for($i = 0; $i < count($this -> tablero); $i++){
            echo($this->tablero[$i]);
        }
    }

    public function devolverPosicionMosca(){
        $posiMosca;
        for($i = 0; $i < count($this -> tablero); $i++){
            if($this->tablero[$i] == 2){
                $posiMosca = $i;
            }
        }
        return $posiMosca + 1;
    }
}

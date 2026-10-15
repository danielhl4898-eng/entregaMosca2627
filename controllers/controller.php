<?php
require_once(__DIR__."/../auxiliar.php");
require_once(__DIR__."/controladorJuego.php");
class controller{
    private $dao;

    public function __construct($dao){
        $this->dao=$dao;
    }

    public function casoPOST($opciones, $parametros, $contrJ){
        switch($opciones[1]){
            case "register":
                $hecho=$this->dao->registrarUsuario($parametros);
                if(!$hecho){
                    auxiliar::datosInvalidos();
                }else{
                    auxiliar::hecho();
                }
                break;
            case "login":
                $loggeao = $this->dao->comprobarLogin($parametros);//compruebo que existe el usuario
                if($loggeao){

                    $idUsuarioLogged = $this->dao->devolverIDUsuario($parametros);//consigo el ID del usuario que se ha loggeado
                    $esAdmin=$this->dao->esAdmin($parametros);//si es admin no puede jugar xd

                    if(!$esAdmin){

                        $partida=$this->dao->tienePartidas($idUsuarioLogged);//meto el ID del usuario para ver su partida activa
                        if($partida!=null && $opciones[2] == "jugar"){
                            $contrJ->jugar($partida, $parametros);//en teoría entra la fila de la tabla tableros entera, pero aún tengo que comprobarlo
                        }else if($opciones[2]!= "jugar"){
                            auxiliar::opcionEq(); 
                        }else if($partida == null){
                            auxiliar::sinPartidas();
                        }
                    }
                }else{
                    auxiliar::adminNoJuega();
                }
                break;
            case "crear":
                $loggeao = $this->dao->comprobarLogin($parametros);//compruebo que existe el usuario con el que estará creado el tablero
                if($loggeao){
                    $idUsuarioLoggeado = $this->dao->devolverIDUsuario($parametros);//consigo el ID del usuario para pasarlo como parámetro y añadirlo a la tabla tableros
                    $esAdmin =$this->dao->esAdmin($parametros);//compruebo si es admin
                    $tienePartidas = $this->dao->tienePartidas($idUsuarioLoggeado);//compruebo si tiene alguna partida activa ya
                    if(!$esAdmin && !$tienePartidas){
                        $this->dao->crearPartida($idUsuarioLoggeado, $parametros);//creo el tablero
                        auxiliar::creada();
                    }else if($tienePartidas){
                        auxiliar::yaHayPartidas();  //tengo que hacer mensaje personalizado de que ya tiene partidas activas
                    }
                    else{
                        auxiliar::adminNoJuega();  
                    }
                }else{
                    auxiliar::loginIncorrecto();      
                }

                break;
            case "rendirse":
                $loggeao = $this->dao->comprobarLogin($parametros);
                if($loggeao){
                    $idUsuarioLoggeado = $this->dao->devolverIDUsuario($parametros);
                    $tienePartidas = $this->dao->tienePartidas($idUsuarioLoggeado);
                    if($tienePartidas){
                        $contrJ->rendirse($tienePartidas);
                        $this->dao->rendirse($tienePartidas);
                    }else{
                        auxiliar::sinPartidas();
                    }
                }else{
                    auxiliar::loginIncorrecto();
                }
                break;
            case "cambiarRol":
                $esAdmin = $this->dao->esAdmin($parametros);
                if($esAdmin){
                    
                    if($esAdmin && $opciones[2] == "admin"){
                        $this->dao->cambiarAAdmin($opciones[3]);
                        auxiliar::cambioCorrecto();
                    }else if($esAdmin && $opciones[2] == "jugador"){
                        $this->dao->cambiarAJugador($opciones[3]);
                        auxiliar::cambioCorrecto();
                    }
                    else{
                        auxiliar::opcionEq();
                    }
                }else{
                    auxiliar::loginIncorrecto();
                }
                break;
            }
        
    }

    public function casoGET($opcion, $parametros){
        if($opcion == "todosUsu"){
            $this->mostrarUsuarios();
        }else if(is_numeric($opcion)){
            $this->mostrarInfoUsuario($opcion);
        }else if($opcion == "consultar"){
            $this->mostrarTableroActivo($parametros);
        }else{
            auxiliar::datosInvalidos();
        }
    }

    public function casoDELETE($opciones, $parametros){
        $this->eliminarUsu($opciones[1], $parametros);
    }

    public function mostrarTableroActivo($parametros){
        $usuario = $this->dao->devolverIDUsuario($parametros);
        if($usuario !=null){
            $partidaAMostrar=$this->dao->mostrarPartidaActiva($usuario);
            if($partidaAMostrar!=null){
                http_response_code(200);
                echo json_encode($partidaAMostrar);
            }else{
                auxiliar::sinPartidas();
            }
        }else{
            auxiliar::error();
        }
        
    }

    public function mostrarUsuarios(){
        $usuarios = $this->dao->selectUsers();
        if($usuarios == null){
            auxiliar::noExistenUsers();
        }else{
            echo json_encode($usuarios);
        }
    }

    public function eliminarUsu($idUsuAEliminar, $parametros){
        $esAdmin=$this->dao->esAdmin($parametros);
        if($esAdmin){
            $this->dao->quitarUsuario($idUsuAEliminar);
            auxiliar::usuBorr();
        }else{
            auxiliar::soloAdmin();
        }
    }

    public function mostrarInfoUsuario($idUsuAMostrar){
        $usuario=$this->dao->mostrarUsuarioPorID($idUsuAMostrar);
        if($usuario == null){
            Auxiliar::error(); //este se queda así xd
        }else{
            echo json_encode($usuario);
        }
        
    }

}




?>
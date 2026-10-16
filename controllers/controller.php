<?php
require_once(__DIR__."/../auxiliar.php");
require_once(__DIR__."/controladorJuego.php");
class controller{
    private $dao;
    public function __construct($dao){
        $this->dao=$dao;
    }
    public function casoPOST($opciones, $parametros, $contrJ, $mailer){
        switch($opciones[1]){
            case "register":
                //Para registrar usuario, primero compruebo que no exista ningún usuario con el mismo nombre, y si ya hay uno con el mismo campo user, salta el aviso
                $yaExiste = $this->dao->comprobarLoginNombreUsu($parametros);
                if($yaExiste){
                    auxiliar::yaExisteUser();
                }else{
                    $hecho=$this->dao->registrarUsuario($parametros);
                    if(!$hecho){
                        auxiliar::datosInvalidos();
                    }else{
                        auxiliar::hecho();
                    }
                }
                break;
            case "login":
                $loggeao = $this->dao->comprobarLogin($parametros);//compruebo que existe el usuario
                if($loggeao){
                    $idUsuarioLogged = $this->dao->devolverIDUsuario($parametros);//consigo el ID del usuario que se ha loggeado
                    $esAdmin=$this->dao->esAdmin($parametros);//si es admin no puede jugar

                    if(!$esAdmin){

                        $partida=$this->dao->tienePartidas($idUsuarioLogged);//meto el ID del usuario para ver su partida activa
                        if($partida!=null && $opciones[2] == "jugar"){
                            $contrJ->jugar($partida, $parametros);//entra la fila de la tabla tableros entera
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
                //comprueba si el login es correcto, y si lo es, busca las partidas que tenga activas, si tiene partidas, las termina y le muestra la posicion de la mosca
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
            case "cambiarRol"://la URL tiene que tener POST: localhost:8080/cambiarRol/(admin||jugador)/{idUsuarioAdmin}
                $esAdmin = $this->dao->esAdmin($parametros);
                if($esAdmin){
                    if($opciones[2] == "admin"){
                        $this->dao->cambiarAAdmin($opciones[3]);
                        auxiliar::cambioCorrecto();
                    }else if($opciones[2] == "jugador"){
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
            case "olvido"://la URL tiene que tener POST: localhost:8080/olvido/{id}
                $idUsuarioEnviar= $opciones[2];
                $emailUsuario=$this->dao->devolverEmailUsuarioPorId($idUsuarioEnviar);
                $contraseniaNuevaArray= "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
                $contraseniaNueva=substr(str_shuffle($contraseniaNuevaArray), 0, 12);
                if($emailUsuario!=null){
                    $this->enviarCorreo($mailer, $emailUsuario, $contraseniaNueva);
                    $this->dao->cambiarContrasenia($idUsuarioEnviar, $contraseniaNueva);
                }else{
                    auxiliar::errorCorreoInvalido();
                }
                break;
            }
        
    }
    public function enviarCorreo($mailer, $emailUsuario, $contraseniaNueva){
        $mailer->isSMTP();
        $mailer->Host= "smtp.gmail.com";
        $mailer->SMTPAuth= true;
        $mailer->Username=getenv("MAIL_PROPIO");
        $mailer->Password= getenv("SMTP_PASSWORD");
        $mailer->SMTPSecure=PHPMailer::ENCRYPTION_SMTPS;
        $mailer->Port = 465;

        $mailer->setFrom(getenv("MAIL_PROPIO"), "Mosca Daniel Hergueta");
        $mailer->addAddress($emailUsuario);
        $mailer->isHTML(true);
        $mailer->Subject='Cambio de contraseña';
        $mailer->Body='Tu contraseña <b>nueva</b> es: <br>'. $contraseniaNueva;
        $mailer->AltBody='Tu contraseña nueva es: '. $contraseniaNueva;
        $enviado=$mailer->send();
        if($enviado){
            auxiliar::correoEnviado();
        }else{
            auxiliar::errorCorreo();
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
    public function casoPATCH($opciones, $parametros){
        switch($opciones[1]){
            case "pass":
                $loginCorrecto=$this->dao->comprobarLogin($parametros);
                if($loginCorrecto){
                    $idUsu = $this->dao->devolverIDUsuario($parametros);
                    if($idUsu == null){
                        auxiliar::error();
                    }else{
                        $this->dao->cambiarPass($idUsu, $parametros["passNueva"]);
                        auxiliar::contraCambiada();
                    }
                }
                else{
                    auxiliar::loginIncorrecto();
                }
                break;
            case "email":
                $loginCorrecto=$this->dao->comprobarLogin($parametros);
                if($loginCorrecto){
                    $idUsu = $this->dao->devolverIDUsuario($parametros);
                    if($idUsu == null){
                        auxiliar::error();
                    }else{
                        $this->dao->cambiarEmail($idUsu, $parametros["emailNuevo"]);
                        auxiliar::emailCambiado();
                    }
                }else{
                    auxiliar::loginIncorrecto();
                }
                break;
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
            Auxiliar::error();
        }else{
            echo json_encode($usuario);
        }  
    }
}
?>
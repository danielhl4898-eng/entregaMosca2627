<?php
require_once(__DIR__."/../controllers/tablero.php");
class DAO{
    private $db;
    public function __construct($db){
        $this->db = $db;
    }
    public function registrarUsuario($parametros){
        $conexion = $this -> db ->connect();
        $stmt = $conexion -> prepare("INSERT INTO usuarios (user, edad, email, pass) VALUES (?,?,?,?)");
        $resultado = $stmt -> execute([$parametros["user"], $parametros["edad"], $parametros["email"], $parametros["pass"]]);
        $conexion -> close();
        return $resultado;
    }
    public function esAdmin($parametros){
        $conexion = $this->db->connect();
        $stmt = $conexion->prepare("SELECT idRol FROM usuarios WHERE user LIKE UPPER(?) AND pass LIKE UPPER(?)");
        $stmt->execute([$parametros["user"], $parametros["pass"]]);
        $fila = $stmt->get_result()->fetch_assoc();
        $idRol = $fila["idRol"] ?? null;
        $conexion->close();
        if ($idRol == 2) {
            return true;
        } else {
            return false;
        }
        return false;
    }
    public function quitarUsuario($id){
        $conexion = $this -> db ->connect();
        $stmt= $conexion->prepare("DELETE FROM usuarios WHERE id = (?)");
        $stmt->execute([$id]);
        $conexion->close();
    }
    public function selectUsers(){
        $conexion = $this->db->connect();
        $stmt = $conexion->prepare("SELECT * FROM usuarios");
        $stmt->execute();
        $usuarios=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $conexion -> close();
        return $usuarios;
    }
    public function mostrarUsuarioPorID($id){
        $conexion = $this->db->connect();
        $stmt = $conexion->prepare("SELECT user, email FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->get_result()->fetch_assoc();
        $conexion->close();
        return $usuario;
    }
    public function comprobarLogin($parametros){
        $conexion = $this->db->connect();
        $stmt = $conexion->prepare("SELECT * FROM usuarios 
        WHERE UPPER(user) LIKE ? AND UPPER(pass) LIKE ?;");
        $existe = $stmt->execute([$parametros["user"], $parametros["pass"]]);
        $usuarioL = $stmt->get_result()->fetch_assoc();
        $conexion->close();
        if($existe && $usuarioL["idRol"] !=2){ //comprueba que no sea admin
            return true;
        }else{
            return false;
        }
    }
    public function devolverIDUsuario($parametros){
        $conexion = $this->db->connect();
        $stmt = $conexion->prepare("SELECT id FROM usuarios 
        WHERE UPPER(user) LIKE ? AND UPPER(pass) LIKE ?; ");
        $stmt->execute([$parametros["user"], $parametros["pass"]]);
        $idUsu=$stmt->get_result()->fetch_assoc();
        $conexion->close();
        return $idUsu["id"];
    }
    public function tienePartidas($idUsuario){
        $conexion = $this-> db-> connect();
        $stmt = $conexion->prepare("SELECT * FROM tableros WHERE id_usuario= ? AND activo=1;");
        $stmt->execute([$idUsuario]);
        $partida = $stmt->get_result()->fetch_assoc();
        $conexion->close();
        return $partida;

    }
    public function crearPartida($idUsuario, $parametros){
        $tableroAniadido= new Tablero(10, $parametros["intentos"]);
        $tableroAniadido->colocarMosca();
        $tableroParaAniadir= implode("",$tableroAniadido->tablero);
        $conexion = $this->db->connect();
        $stmt = $conexion->prepare("INSERT INTO tableros (tablero, activo, id_usuario, intentos) VALUES (? , 1 , ?, ?);");
        $stmt->execute([$tableroParaAniadir, $idUsuario, $parametros["intentos"]]);
        $conexion->close();
    }

    public function rendirse($partida){
        $conexion = $this->db->connect();
        $stmt = $conexion->prepare("UPDATE tableros SET activo = 0 WHERE id_tablero = ?;");
        $stmt->execute([$partida["id_tablero"]]);
        $conexion->close();
    }

    public function actualizarPartida($partidaAAct){
        $conexion = $this->db->connect();
        $stmt = $conexion->prepare("UPDATE tableros SET tablero = ? , activo = ?, intentos = ? WHERE id_tablero = ?;");
        $stmt->execute([$partidaAAct["tablero"],$partidaAAct["activo"], $partidaAAct["intentos"], $partidaAAct["id_tablero"]]);
        $conexion->close();
    }

    public function mostrarPartidaActiva($idUsu){
        $conexion = $this->db->connect();
        $stmt = $conexion->prepare("SELECT id_tablero, intentos FROM tableros WHERE id_usuario = ? AND activo = 1");
        $stmt->execute([$idUsu]);
        $partida = $stmt->get_result()->fetch_assoc();
        $conexion->close();
        return $partida;
    }

    public function cambiarAAdmin($idUsu){
        $conexion = $this->db->connect();
        $stmt= $conexion->prepare("UPDATE usuarios SET idRol= 2 WHERE id = ?");
        $stmt->execute([$idUsu]);
        $conexion->close();
    }
    public function cambiarAJugador($idUsu){
        $conexion = $this->db->connect();
        $stmt= $conexion->prepare("UPDATE usuarios SET idRol= 1 WHERE id = ?");
        $stmt->execute([$idUsu]);
        $conexion->close();
    }

}

?>
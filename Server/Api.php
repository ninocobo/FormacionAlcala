<?php
    require_once("Rest.php");
    require_once ("Conexion.php");

class Api extends Rest {
    private $_conn = NULL;
    private $_metodo;
    private $_argumentos;

    //Constructor de la clase
    public function __construct() {
        parent::__construct();
        $this->_conn=Conexion::getConexion();
    }

    //Función para devolver los mensajes de error
    private function devolverError($id) {
        $errores = array(
            array('estado' => "error", "msg" => "petición no encontrada"),
            array('estado' => "error", "msg" => "petición no aceptada"),
            array('estado' => "error", "msg" => "petición sin contenido"),
            array('estado' => "error", "msg" => "Error datos incorrectos"),
            array('estado' => "error", "msg" => "Error borrando el contacto"),
            array('estado' => "error", "msg" => "Error actualizando los datos del contacto"),
            array('estado' => "error", "msg" => "Error buscando contactos"),
            array('estado' => "error", "msg" => "Error al crear el contacto"),
            array('estado' => "error", "msg" => "Error, el contacto ya existe")
        );
        return $errores[$id];
    }

    public function procesarLlamada() {
        if (isset($_REQUEST['url'])) {
            $url = explode('/', trim($_REQUEST['url']));
            //con array_filter() filtramos elementos de un array pasando función callback.
            //si no le pasamos función callback, los elementos false o vacios del array serán borrados
            //por lo tanto entre la anterior función (explode) y esta eliminamos los '/' sobrantes de la URL
            $url = array_filter($url);
            $this->_metodo = strtolower(array_shift($url));
            $this->_argumentos = $url;
            $func = $this->_metodo;
            if ((int) method_exists($this, $func) > 0) {
                if (count($this->_argumentos) > 0) {
                    call_user_func_array(array($this, $this->_metodo), $this->_argumentos);
                } else {//si no lo llamamos sin argumentos, al metodo del controlador
                    call_user_func(array($this, $this->_metodo));
                }
            } else {
                $this->mostrarRespuesta($this->convertirJson($this->devolverError(0)), 404);
            }
        }
        $this->mostrarRespuesta($this->convertirJson($this->devolverError(0)), 404);
    }

    //Codifica los datos a json
    private function convertirJson($data) {
        return json_encode($data);
    }

    //Función para añadir un nuevo contacto
    private function crearContacto() {
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }
        if (isset($this->datosPeticion['nombre'], $this->datosPeticion['apellido'], $this->datosPeticion['dni'])) {
            $nombre = $this->datosPeticion['nombre'];
            $apellido = $this->datosPeticion['apellido'];
            $dni = $this->datosPeticion['dni'];
            if (!$this->existeUsuario($dni)) {
                $query = $this->_conn->prepare("INSERT into contactos (nombre,apellido,dni) VALUES (:nombre,:apellido, :dni)");
                $query->bindValue(":nombre", $nombre);
                $query->bindValue(":apellido", $apellido);
                $query->bindValue(":dni", $dni);
                $query->execute();
                if ($query->rowCount() == 1) {
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'Contacto creado correctamente';
                    $this->mostrarRespuesta($this->convertirJson($respuesta), 200);
                } else {
                    $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);
                }
            } else {
                $this->mostrarRespuesta($this->convertirJson($this->devolverError(8)), 400);
            }
        } else {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(7)), 400);
        }
    }

    //Función para comprobar si existe el contacto en la base de datos
    private function existeUsuario($dni) {
        $query = $this->_conn->prepare("SELECT dni from contactos WHERE dni = :dni");
        $query->bindValue(":dni", $dni);
        $query->execute();
        if ($query->fetch(PDO::FETCH_ASSOC)) {
            return true;
        } else {
            return false;
        }
    }

    //Función que selecciona todos los contactos de la base de datos
    private function mostrarContactos() {
        if ($_SERVER['REQUEST_METHOD'] != "GET") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }
        $query = $this->_conn->query("SELECT id, nombre, apellido, dni FROM contactos");
        if($query->rowCount() > 0){
            while($filas = $query->fetch(PDO::FETCH_ASSOC)){
                $respuesta[] = $filas;
            }
            $this->mostrarRespuesta($this->convertirJson($respuesta), 200);
        }
        $this->mostrarRespuesta($this->devolverError(2), 204);
    }

    //Función para actualizar el nombre y apellidos del contacto en el base de datos
    private function actualizarContacto() {
        if ($_SERVER['REQUEST_METHOD'] != "PUT") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }
        if (isset($this->datosPeticion['nombre']) && isset($this->datosPeticion['id']) && isset($this->datosPeticion['apellido'])) {
            $nombre = $this->datosPeticion['nombre'];
            $apellido = $this->datosPeticion['apellido'];
            $id=$this->datosPeticion['id'];
            if (!empty($nombre) && !empty($apellido) && $id > 0) {
                $query = $this->_conn->prepare("update contactos set nombre=:nombre, apellido=:apellido WHERE id =:id");
                $query->bindValue(":nombre", $nombre);
                $query->bindValue(":apellido", $apellido);
                $query->bindValue(":id", $id);
                $query->execute();
                $filasActualizadas = $query->rowCount();
                if ($filasActualizadas == 1) {
                    $resp = array('estado' => "correcto", "msg" => "Contacto actualizado correctamente.");
                    $this->mostrarRespuesta($this->convertirJson($resp), 200);
                } else {
                    $this->mostrarRespuesta($this->convertirJson($this->devolverError(5)), 400);
                }
            }
        }
        $this->mostrarRespuesta($this->convertirJson($this->devolverError(5)), 400);
    }

    //Función para borrar el contacto de la base de datos
    private function borrarUsuario() {
        if ($_SERVER['REQUEST_METHOD'] != "DELETE") {
            $this->mostrarRespuesta($this->convertirJson($this->devolverError(1)), 405);
        }
        if (isset($this->datosPeticion['id'])) {
            $id=$this->datosPeticion['id'];
            if ($id >= 0) {
                $query = $this->_conn->prepare("delete from contactos WHERE id =:id");
                $query->bindValue(":id", $id);
                $query->execute();
                if ($query) {
                    $resp = array('estado' => "correcto", "msg" => "Contacto borrado correctamente.");
                    $this->mostrarRespuesta($this->convertirJson($resp), 200);
                } else {
                    $this->mostrarRespuesta($this->convertirJson($this->devolverError(4)), 400);
                }
            }
        }
        $this->mostrarRespuesta($this->convertirJson($this->devolverError(4)), 400);
    }
}
    //Se crea la api y se procesa la llamada
    $api = new Api();
    $api->procesarLlamada();

<?php
    require_once "Usuario.php";
    session_start();

    if(isset($_SESSION['mensaje'])){
        echo $_SESSION['mensaje'];
        unset($_SESSION['mensaje']);
    }
?>
<html>
<head>
    <title>Registrar Usuario</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
<div class="container col-md-5" >
    <form id="formulario" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" >
        <br>
        <h3>Crear Contacto</h3>
        <div class="form-row">
            <div class="form-group col-md-5">
                <label for="nombre">Nombre</label>
                <input type="text" class="form-control form-control-sm" id="nombre" name="nombre" placeholder="Introduce el nombre..." maxlength="100">
            </div>
            <div class="form-group col-md-5">
                <label for="apellido">Apellidos</label>
                <input type="text" class="form-control form-control-sm" id="apellido" name="apellido" placeholder="Introduce los apellidos..." maxlength="100">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-5">
                <label for="dni">DNI</label>
                <input type="text" class="form-control form-control-sm" id="dni" name="dni" placeholder="Introduce el DNI..." maxlength="20">
            </div>
        </div>
        <button name="registrar" class="btn btn-primary">Registrar</button>
        <button type="reset" class="btn btn-primary" value="Borrar" id="borrar" name="borrar">Borrar datos</button>
        <input type="button" class="btn btn-primary"onclick="location='contactos.php'" value="Volver"/>
    </form>
</div>

<?php
    if(isset($_REQUEST['registrar'])){
        if(isset($_REQUEST['nombre']) && !empty($_REQUEST['nombre'])
            && isset($_REQUEST['apellido']) && !empty($_REQUEST['apellido'])
            && isset($_REQUEST['dni']) && !empty($_REQUEST['dni'])
          ){
            $porData=array();

            if(validarDni($_REQUEST['dni'])){
                $usuario=nuevoUsuario($_REQUEST['nombre'], $_REQUEST['apellido'], $_REQUEST['dni']);
                $porData['nombre']=$usuario->getNombre();
                $porData['apellido']=$usuario->getApellidos();
                $porData['dni']=$usuario->getDni();
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "localhost/api/Server/crearContacto");
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($porData));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $data = curl_exec($ch);
                print_r($data);//mensaje de información
                curl_close($ch);
            }else{
                echo "El dni no es válido";
            }
        }else{
            echo "Error, rellena todos los datos";
        }
    }

    //Función para crear un nuevo usuario
    function nuevoUsuario($nombre,$apellido, $dni){
        $newUsu= new Usuario(0,$nombre,$apellido,$dni);
        return $newUsu;
    }

    //Función para validar el dni
    function validarDni($dni){
        $letra = substr($dni, -1);
        $numeros = substr($dni, 0, -1);
        $valido=false;
        if (substr("TRWAGMYFPDXBNJZSQVHLCKEtrwagmyfpdxbnjzsqvhlcke", $numeros%23, 1) == $letra && strlen($letra) == 1 && strlen ($numeros) == 8 ){
            $valido=true;
        }else{
            $valido=false;
        }
        return $valido;
    }
?>
</body>
</html>

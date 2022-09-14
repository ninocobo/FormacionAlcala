<?php
    session_start();
?>
<html>
<head>
    <title>Contactos Formación Alcalá</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
    <br>
    <div class="container">
    <h4>Formación Alcalá</h4>
        <br>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
        <button class="btn btn-primary" name="crear">Añadir Contacto</button>
    </form>
    </div>
</body>
</html>

<?php
    if(isset($_SESSION['mensaje'])){//si se ha guardado algún mensaje en la variable de sesión
        print_r($_SESSION['mensaje']);
        unset($_SESSION['mensaje']);
    }

    if(isset($_REQUEST['crear'])){//si se pulsa sobre crear
        header("Location: registrar.php");
    }elseif (isset($_REQUEST['borrar'])){//si se pulsa sobre borrar
            $postBorrar = array();
            $postBorrar['id'] = $_REQUEST['borrar'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "localhost/api/Server/borrarUsuario");
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postBorrar));
            $data = curl_exec($ch);
            $_SESSION['mensaje']=$data;
            curl_close($ch);
            header("Location: contactos.php");
    }elseif (isset($_REQUEST['actu'])){//si se pulsa en actualizar
        $id=$_REQUEST['actu'];
        if(isset($_REQUEST['nombre'.$id])&& !empty($_REQUEST['nombre'.$id])){
            $postData = array();
            $postData['id'] = $_REQUEST['actu'];
            $postData['nombre'] = $_REQUEST['nombre'.$id];
            $postData['apellido'] = $_REQUEST['apellido'.$id];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "localhost/api/Server/actualizarContacto");
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            $data = curl_exec($ch);
            $_SESSION['mensaje']=$data;
            curl_close($ch);
        }
        header("Location: contactos.php");
    }else{//se muestra la tabla con todos los usuarios
        tabla();
    }

    //Función que muestra la tabla con todos los contactos
    function tabla(){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "localhost/api/Server/mostrarContactos");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = json_decode(curl_exec($ch),true);
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
            <div class="container">
                <h4 class='text-center'>CONTACTOS</h4><br>
                <table id="tabla" class="table table-striped table-dark table-bordered table-hover table-responsive-md">
                    <tr>
                        <td>Id</td><td>Nombre</td><td>Apellidos</td><td>DNI</td><td></td><td></td>
                    </tr>
                    <?php
                        for($i=0;$i<count($data);$i++){
                            echo "<tr>";
                            echo "<td>".$data[$i]['id']."</td>
                                  <td contenteditable='true'><input class='form-control' type='text' name='nombre".$data[$i]['id']."' value='".$data[$i]['nombre']."' maxlength='100'></td>
                                  <td contenteditable='true'><input class='form-control' type='text' name='apellido".$data[$i]['id']."' value='".$data[$i]['apellido']."' maxlength='100'></td>
                                  <td><input class='form-control' type='text' name='dni".$data[$i]['id']."' value='".$data[$i]['dni']."' readonly></td>
                                  <td><button class='btn btn-danger'  name='borrar' value='".$data[$i]['id']."'>Borrar Usuario</button></td>
                                  <td><button class='btn btn-success'  name='actu' value='".$data[$i]['id']."'>Modificar nombre</button></td>";
                            echo "</tr>";
                        }
                    ?>
                </table>
                <div id="respuesta"></div>
            </div>
        </form>
        <?php
        curl_close($ch);
    }
?>

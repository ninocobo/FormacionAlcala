<?php

class Usuario{
    private $id;
    private $nombre;
    private $apellido;
    private $dni;

    //Constructor de la clase
    public function __construct($id, $nombre, $apellido, $dni){
        $this->id = $id;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->dni = $dni;
    }

    //Getters y Setters
    public function getDni(){
        return $this->dni;
    }

    public function setDni($dni): void{
        $this->dni = $dni;
    }

    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getNombre(){
        return $this->nombre;
    }

    public function setNombre($nombre){
        $this->nombre = $nombre;
    }

    public function getApellidos(){
        return $this->apellido;
    }

    public function setApellidos($apellido): void{
        $this->apellido = $apellido;
    }
}

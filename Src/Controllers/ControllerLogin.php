<?php
    namespace BetelCreativa\Controllers;

    use BetelCreativa\Domain\UserModel;
    
if (!empty($_POST["btnLogin"])) {
    if (empty($_POST["username"]) or empty($_POST["password"])) {

        echo "Los Campos estan vacio";
    } else {
        #Mensaje de Campos Vacio
    }
}

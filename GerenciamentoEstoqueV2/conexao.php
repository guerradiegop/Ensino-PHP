<?php
// Conexao.php - Conexão com o banco de dados

$host   = "localhost";
$usuario = "root";
$senha  = "";
$banco  = "sistema";

$mysqli = new mysqli($host, $usuario, $senha, $banco);

if ($mysqli->connect_errno) {
    echo "Falha ao conectar ao MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit();
}
?>
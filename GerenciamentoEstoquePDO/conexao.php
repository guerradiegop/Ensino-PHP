<?php
// conexao.php - Conexão com o banco de dados utilizando PDO

$host    = "localhost";
$porta   = 3306;
$usuario = "root";
$senha   = "";
$banco   = "sistema";

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$porta;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $erro) {
    echo "Falha ao conectar ao MySQL: " . $erro->getMessage();
    exit();
}
?>
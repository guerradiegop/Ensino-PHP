<?php
// painel.php - Gerenciador de Estoque (CRUD de Produtos) utilizando PDO

include 'protect.php';
include 'conexao.php';

$nomeUsuario = $_SESSION['nome'] ?? 'Usuário';
$mensagem = "";
$tipo_msg = "";
$produto_editar = null;

// ── Código SQL ──────────────────────────
/*CREATE DATABASE IF NOT EXISTS sistema;
USE sistema;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    quantidade INT NOT NULL DEFAULT 0,
    valor DECIMAL(10,2) NOT NULL DEFAULT 0.00
);*/

// ── Ação: EXCLUIR ────────────────────────────────────────
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $mensagem = "Produto excluído com sucesso.";
        $tipo_msg = "sucesso";
    } catch (PDOException $erro) {
        $mensagem = "Erro ao excluir: " . $erro->getMessage();
        $tipo_msg = "erro";
    }
}

// ── Ação: CARREGAR DADOS PARA EDITAR ────────────────────
if (isset($_GET['acao']) && $_GET['acao'] === 'editar' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $produto_editar = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// ── Ação: SALVAR (novo ou atualização) ──────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $quantidade = (int) ($_POST['quantidade'] ?? 0);
    $valor = number_format((float) str_replace(',', '.', $_POST['valor'] ?? '0'), 2, '.', '');
    $id_post = (int) ($_POST['id'] ?? 0);

    if (empty($nome)) {
        $mensagem = "O nome do produto é obrigatório.";
        $tipo_msg = "erro";
    } elseif ($quantidade < 0) {
        $mensagem = "A quantidade não pode ser negativa.";
        $tipo_msg = "erro";
    } elseif ($valor < 0) {
        $mensagem = "O valor não pode ser negativo.";
        $tipo_msg = "erro";
    } else {
        try {
            if ($id_post > 0) {
                $sql = "UPDATE produtos
                        SET nome = :nome, quantidade = :quantidade, valor = :valor
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'nome' => $nome,
                    'quantidade' => $quantidade,
                    'valor' => $valor,
                    'id' => $id_post
                ]);
                $mensagem = "Produto atualizado com sucesso.";
            } else {
                $sql = "INSERT INTO produtos (nome, quantidade, valor)
                        VALUES (:nome, :quantidade, :valor)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'nome' => $nome,
                    'quantidade' => $quantidade,
                    'valor' => $valor
                ]);
                $mensagem = "Produto cadastrado com sucesso.";
            }

            $tipo_msg = "sucesso";
            $produto_editar = null;
        } catch (PDOException $erro) {
            $mensagem = "Erro ao salvar: " . $erro->getMessage();
            $tipo_msg = "erro";
        }
    }
}

// ── LISTAR todos os produtos ─────────────────────────────
$stmt_lista = $pdo->query("SELECT * FROM produtos ORDER BY id ASC");
$lista = $stmt_lista->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel - Gerenciador de Estoque PDO</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="pagina-painel">

    <header class="topbar">
        <span class="topbar-titulo">&#128230; Gerenciador de Estoque - PDO</span>
        <div class="topbar-usuario">
            Usuário: <strong><?= htmlspecialchars($nomeUsuario) ?></strong>
            &nbsp;|&nbsp;
            <a href="logout.php">Sair</a>
        </div>
    </header>

    <main class="container painel-conteudo">

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem <?= htmlspecialchars($tipo_msg) ?>">
                <?= htmlspecialchars($mensagem) ?>
            </p>
        <?php endif; ?>

        <section class="card-form">
            <h2><?= $produto_editar ? 'Editar Produto' : 'Cadastrar Produto' ?></h2>

            <form action="painel.php" method="POST">
                <?php if ($produto_editar): ?>
                    <input type="hidden" name="id" value="<?= (int) $produto_editar['id'] ?>">
                <?php endif; ?>

                <div class="form-linha">
                    <div class="campo">
                        <label for="nome">Nome do produto</label>
                        <input type="text" id="nome" name="nome" required
                               value="<?= htmlspecialchars($produto_editar['nome'] ?? '') ?>">
                    </div>
                    <div class="campo campo-pequeno">
                        <label for="quantidade">Quantidade</label>
                        <input type="number" id="quantidade" name="quantidade" min="0" required
                               value="<?= htmlspecialchars($produto_editar['quantidade'] ?? '0') ?>">
                    </div>
                    <div class="campo campo-pequeno">
                        <label for="valor">Valor (R$)</label>
                        <input type="number" id="valor" name="valor" min="0" step="0.01" required
                               value="<?= htmlspecialchars($produto_editar['valor'] ?? '0.00') ?>">
                    </div>
                </div>

                <div class="form-acoes">
                    <button type="submit" class="btn btn-principal">
                        <?= $produto_editar ? 'Salvar alterações' : 'Cadastrar produto' ?>
                    </button>
                    <?php if ($produto_editar): ?>
                        <a href="painel.php" class="btn btn-cancelar">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="card-tabela">
            <h2>Produtos cadastrados</h2>

            <?php if (count($lista) > 0): ?>
                <table class="tabela-produtos">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>Quantidade</th>
                            <th>Valor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lista as $p): ?>
                            <tr>
                                <td><?= (int) $p['id'] ?></td>
                                <td><?= htmlspecialchars($p['nome']) ?></td>
                                <td><?= (int) $p['quantidade'] ?></td>
                                <td>R$ <?= number_format((float) $p['valor'], 2, ',', '.') ?></td>
                                <td class="acoes">
                                    <a href="painel.php?acao=editar&id=<?= (int) $p['id'] ?>"
                                       class="btn-acao btn-editar">Editar</a>
                                    <a href="painel.php?acao=excluir&id=<?= (int) $p['id'] ?>"
                                       class="btn-acao btn-excluir"
                                       onclick="return confirm('Excluir este produto?')">
                                       Excluir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="sem-registros">Nenhum produto cadastrado ainda.</p>
            <?php endif; ?>
        </section>

    </main>
</body>
</html>
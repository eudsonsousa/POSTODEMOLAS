<?php
session_start();
// Conectar ao MySQL sem selecionar um banco específico
$conn = new mysqli("localhost", "root", "");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Criar o banco de dados se não existir
$conn->query("CREATE DATABASE IF NOT EXISTS posto_molas");
// Selecionar o banco de dados
$conn->select_db("posto_molas");

// Criar tabelas
$conn->query("CREATE TABLE IF NOT EXISTS funcionarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
)");

$conn->query("CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
)");

$conn->query("CREATE TABLE IF NOT EXISTS servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL
)");

$conn->query("CREATE TABLE IF NOT EXISTS lancamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_hora DATETIME NOT NULL,
    id_funcionario INT,
    id_cliente INT,
    id_servico INT,
    FOREIGN KEY (id_funcionario) REFERENCES funcionarios(id),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id),
    FOREIGN KEY (id_servico) REFERENCES servicos(id)
)");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Posto de Molas Nossa Senhora do Perpétuo Socorro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #212529;
            color: #f8f9fa;
        }
        .navbar, .card, .table {
            background-color: #343a40;
            color: #f8f9fa;
        }
        .table th, .table td {
            border-color: #495057;
        }
        .btn-primary {
            background-color: #495057;
            border-color: #495057;
        }
        .btn-primary:hover {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .header-report {
            text-align: center;
            margin-bottom: 20px;
        }
        .servico-col {
            width: 40%;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Posto de Molas</a>
            <div class="navbar-nav">
                <a class="nav-link" href="?page=cadastro_funcionario">Cadastrar Funcionário</a>
                <a class="nav-link" href="?page=cadastro_cliente">Cadastrar Cliente</a>
                <a class="nav-link" href="?page=cadastro_servico">Cadastrar Serviço</a>
                <a class="nav-link" href="?page=lancamento">Lançar Serviço</a>
                <a class="nav-link" href="?page=relatorio">Relatório</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php
        $page = isset($_GET['page']) ? $_GET['page'] : 'home';
        switch ($page) {
            case 'cadastro_funcionario':
                ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Cadastrar Funcionário</h5>
                        <form method="POST" action="?page=salvar_funcionario">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </form>
                    </div>
                </div>
                <?php
                break;

            case 'salvar_funcionario':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $nome = $_POST['nome'];
                    $conn->query("INSERT INTO funcionarios (nome) VALUES ('$nome')");
                    echo "<div class='alert alert-success'>Funcionário cadastrado com sucesso!</div>";
                }
                break;

            case 'cadastro_cliente':
                ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Cadastrar Cliente</h5>
                        <form method="POST" action="?page=salvar_cliente">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </form>
                    </div>
                </div>
                <?php
                break;

            case 'salvar_cliente':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $nome = $_POST['nome'];
                    $conn->query("INSERT INTO clientes (nome) VALUES ('$nome')");
                    echo "<div class='alert alert-success'>Cliente cadastrado com sucesso!</div>";
                }
                break;

            case 'cadastro_servico':
                ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Cadastrar Serviço</h5>
                        <form method="POST" action="?page=salvar_servico">
                            <div class="mb-3">
                                <label for="descricao" class="form-label">Descrição</label>
                                <input type="text" class="form-control" id="descricao" name="descricao" required>
                            </div>
                            <div class="mb-3">
                                <label for="valor" class="form-label">Valor (R$)</label>
                                <input type="number" step="0.01" class="form-control" id="valor" name="valor" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </form>
                    </div>
                </div>
                <?php
                break;

            case 'salvar_servico':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $descricao = $_POST['descricao'];
                    $valor = $_POST['valor'];
                    $conn->query("INSERT INTO servicos (descricao, valor) VALUES ('$descricao', $valor)");
                    echo "<div class='alert alert-success'>Serviço cadastrado com sucesso!</div>";
                }
                break;

            case 'lancamento':
                ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Lançar Serviço</h5>
                        <form method="POST" action="?page=salvar_lancamento">
                            <div class="mb-3">
                                <label for="id_funcionario" class="form-label">Funcionário</label>
                                <select class="form-select" id="id_funcionario" name="id_funcionario" required>
                                    <option value="">Selecione</option>
                                    <?php
                                    $result = $conn->query("SELECT * FROM funcionarios");
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['id']}'>{$row['nome']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="id_cliente" class="form-label">Cliente</label>
                                <select class="form-select" id="id_cliente" name="id_cliente" required>
                                    <option value="">Selecione</option>
                                    <?php
                                    $result = $conn->query("SELECT * FROM clientes");
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['id']}'>{$row['nome']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="id_servico" class="form-label">Serviço</label>
                                <select class="form-select" id="id_servico" name="id_servico" required>
                                    <option value="">Selecione</option>
                                    <?php
                                    $result = $conn->query("SELECT * FROM servicos");
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['id']}'>{$row['descricao']} (R$ {$row['valor']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="data_hora" class="form-label">Data e Hora</label>
                                <input type="datetime-local" class="form-control" id="data_hora" name="data_hora" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </form>
                    </div>
                </div>
                <?php
                break;

            case 'salvar_lancamento':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $id_funcionario = $_POST['id_funcionario'];
                    $id_cliente = $_POST['id_cliente'];
                    $id_servico = $_POST['id_servico'];
                    $data_hora = $_POST['data_hora'];
                    $conn->query("INSERT INTO lancamentos (data_hora, id_funcionario, id_cliente, id_servico) 
                                  VALUES ('$data_hora', $id_funcionario, $id_cliente, $id_servico)");
                    echo "<div class='alert alert-success'>Lançamento salvo com sucesso!</div>";
                }
                break;

            case 'relatorio':
                ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Relatório de Serviços</h5>
                        <form method="GET" action="relatorio.php" target="_blank">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="id_funcionario" class="form-label">Funcionário</label>
                                    <select class="form-select" id="id_funcionario" name="id_funcionario" required>
                                        <option value="">Selecione</option>
                                        <?php
                                        $result = $conn->query("SELECT * FROM funcionarios");
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='{$row['id']}'>{$row['nome']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="data_inicio" class="form-label">Data Início</label>
                                    <input type="date" class="form-control" id="data_inicio" name="data_inicio" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="data_fim" class="form-label">Data Fim</label>
                                    <input type="date" class="form-control" id="data_fim" name="data_fim" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Gerar Relatório</button>
                        </form>
                    </div>
                </div>
                <?php
                break;

            default:
                echo "<div class='card'><div class='card-body'><h5 class='card-title'>Bem-vindo ao Sistema</h5><p>Selecione uma opção no menu acima.</p></div></div>";
                break;
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
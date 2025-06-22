<?php
$conn = new mysqli("localhost", "root", "", "posto_molas");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['id_funcionario']) || !isset($_GET['data_inicio']) || !isset($_GET['data_fim'])) {
    die("Parâmetros inválidos.");
}

$id_funcionario = $_GET['id_funcionario'];
$data_inicio = $_GET['data_inicio'];
$data_fim = $_GET['data_fim'];

$result = $conn->query("SELECT f.nome AS funcionario, l.data_hora, c.nome AS cliente, s.descricao, s.valor 
                        FROM lancamentos l
                        JOIN funcionarios f ON l.id_funcionario = f.id
                        JOIN clientes c ON l.id_cliente = c.id
                        JOIN servicos s ON l.id_servico = s.id
                        WHERE l.id_funcionario = $id_funcionario 
                        AND l.data_hora BETWEEN '$data_inicio 00:00:00' AND '$data_fim 23:59:59'");
$funcionario = $conn->query("SELECT nome FROM funcionarios WHERE id = $id_funcionario")->fetch_assoc()['nome'];

// Variável para calcular o total
$total_valor = 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Serviços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header-report {
            text-align: center;
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .servico-col {
            width: 40%;
        }
        .total-row {
            font-weight: bold;
            margin-top: 10px;
            text-align: right;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
                width: 210mm;
                height: 297mm;
            }
            .container {
                width: 190mm;
                margin: 10mm auto;
            }
            .no-print {
                display: none;
            }
            .table th, .table td {
                font-size: 12pt;
            }
            .total-row {
                font-size: 12pt;
            }
        }
        @page {
            size: A4;
            margin: 10mm;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="container">
        <div class="header-report">
            <h4>POSTO DE MOLAS NOSSA SENHORA DO PERPÉTUO SOCORRO</h4>
            <p>Rua Governador Belarmino Neves Galvão, 106 - Bairro: Airton Rocha</p>
            <p>CNPJ: 45.028.366/0001-07 Contato: (95) 99143-9476</p>
            <p>Funcionário: <?php echo htmlspecialchars($funcionario); ?></p>
            <p>Período: <?php echo date('d/m/Y', strtotime($data_inicio)) . ' a ' . date('d/m/Y', strtotime($data_fim)); ?></p>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Data do Serviço</th>
                    <th>Cliente</th>
                    <th class="servico-col">Serviços</th>
                    <th>Valor (R$)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = $result->fetch_assoc()) {
                    $total_valor += $row['valor']; // Somar o valor
                    echo "<tr>
                        <td>" . date('d/m/Y H:i', strtotime($row['data_hora'])) . "</td>
                        <td>" . htmlspecialchars($row['cliente']) . "</td>
                        <td>" . htmlspecialchars($row['descricao']) . "</td>
                        <td>" . number_format($row['valor'], 2, ',', '.') . "</td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="total-row">
            Total Geral: R$ <?php echo number_format($total_valor, 2, ',', '.'); ?>
        </div>
        <button class="btn btn-primary mt-3 no-print" onclick="window.print()">Imprimir</button>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
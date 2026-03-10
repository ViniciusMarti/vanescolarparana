<?php
require_once __DIR__ . '/config/db.php';
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "TABELAS:\n";
    foreach ($tables as $t) {
        echo "- $t\n";
        $stmt2 = $pdo->query("SHOW COLUMNS FROM `$t` LIKE 'bairro_referencia'");
        if ($stmt2->fetch()) {
            echo "  (TEM bairro_referencia)\n";
            // Vamos ver um exemplo de dado
            $stmt3 = $pdo->query("SELECT bairro_referencia FROM `$t` LIMIT 1");
            $row = $stmt3->fetch();
            echo "  Exemplo: " . ($row['bairro_referencia'] ?? 'NULL') . "\n";
        }
    }
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>

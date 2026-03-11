<?php
require_once __DIR__ . '/config/db_escolas.php';
if (!$pdo_escolas) {
    die("Conexão falhou");
}
$stmt = $pdo_escolas->query("DESCRIBE escolas");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($columns);
echo "</pre>";

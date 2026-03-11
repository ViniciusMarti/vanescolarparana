<?php
require_once __DIR__ . '/config/db_escolas.php';
if (!$pdo_escolas) die("Falha");
$stmt = $pdo_escolas->query("DESCRIBE escolas");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
foreach($cols as $col) {
    echo $col . "\n";
}

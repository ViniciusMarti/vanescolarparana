<?php
require_once __DIR__ . '/config/db_escolas.php';
$stmt = $pdo_escolas->prepare("SELECT * FROM escolas LIMIT 5");
$stmt->execute();
echo "<pre>";
print_r($stmt->fetchAll());
echo "</pre>";

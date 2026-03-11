<?php
// Configuração do banco de dados de escolas
$db_host_escolas = 'localhost';
$db_name_escolas = 'u582732852_escolas_parana';
$db_user_escolas = 'u582732852_escolas_parana';
$db_pass_escolas = 'qPMwBp#WW*BN6k';

try {
    $pdo_escolas = new PDO("mysql:host=$db_host_escolas;dbname=$db_name_escolas;charset=utf8mb4", $db_user_escolas, $db_pass_escolas);
    $pdo_escolas->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_escolas->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Se falhar a conexão, registrar o erro mas não necessariamente derrubar o site todo se for chamado isoladamente.
    error_log("Erro na conexão com o banco de escolas: " . $e->getMessage());
    $pdo_escolas = null;
}
?>

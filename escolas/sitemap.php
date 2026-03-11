<?php
header("Content-Type: application/xml; charset=utf-8");
require_once __DIR__ . '/../config/db_escolas.php';

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

$base_url = "https://www.vanescolarparana.com";

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Home e Cidades
echo "<url><loc>$base_url/escolas</loc><priority>1.0</priority></url>";
echo "<url><loc>$base_url/escolas/cidades</loc><priority>0.8</priority></url>";

// Cidades
$stmt = $pdo_escolas->query("SELECT DISTINCT nome_municipio FROM escolas");
while ($cidade = $stmt->fetchColumn()) {
    $cidade_url = "$base_url/escolas/cidade/" . urlencode($cidade);
    echo "<url><loc>$cidade_url</loc><priority>0.7</priority></url>";
    
    // Bairros da Cidade
    $stmt2 = $pdo_escolas->prepare("SELECT DISTINCT bairro FROM escolas WHERE nome_municipio = ?");
    $stmt2->execute([$cidade]);
    while ($bairro = $stmt2->fetchColumn()) {
        if (!$bairro) continue;
        $bairro_url = $cidade_url . "/" . urlencode($bairro);
        echo "<url><loc>$bairro_url</loc><priority>0.6</priority></url>";
    }
}

// Escolas
$stmt = $pdo_escolas->query("SELECT id_escola, nome_escola FROM escolas");
while ($escola = $stmt->fetch()) {
    $escola_url = "$base_url/escolas/escola/" . $escola['id_escola'] . "-" . slugify($escola['nome_escola']);
    echo "<url><loc>$escola_url</loc><priority>0.5</priority></url>";
}

echo '</urlset>';
?>

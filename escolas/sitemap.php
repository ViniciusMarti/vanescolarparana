<?php
header("Content-Type: application/xml; charset=utf-8");
require_once __DIR__ . '/../config/db_escolas.php';

$base_url = "https://www.vanescolarparana.com";

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Home e Cidades
echo "<url><loc>$base_url/escolas</loc><priority>1.0</priority></url>";
echo "<url><loc>$base_url/escolas/cidades</loc><priority>0.8</priority></url>";

// Helper to slugify (copied for standalone execution)
function slugify($text) {
    if (!$text) return "";
    $text = mb_strtolower($text, 'UTF-8');
    $map = [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n'
    ];
    $text = strtr($text, $map);
    $text = preg_replace('~[^\w\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return empty($text) ? 'n-a' : $text;
}

// 2. Cidades
$stmt = $pdo_escolas->query("SELECT DISTINCT nome_municipio FROM escolas");
while ($row = $stmt->fetch()) {
    $cidade = slugify($row['nome_municipio']);
    echo "<url><loc>{$base_url}/escolas/cidade/{$cidade}</loc><changefreq>weekly</changefreq><priority>0.7</priority></url>\n";
}

// 4. Escolas Individuais
$stmt = $pdo_escolas->query("SELECT id_escola, nome_escola FROM escolas");
while ($row = $stmt->fetch()) {
    $slug = slugify($row['nome_escola']);
    echo "<url><loc>{$base_url}/escolas/escola/{$row['id_escola']}-{$slug}</loc><changefreq>monthly</changefreq><priority>0.8</priority></url>\n";
}
echo '</urlset>';
?>

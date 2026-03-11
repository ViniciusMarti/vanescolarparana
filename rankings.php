<?php
require_once __DIR__ . '/config/db_escolas.php';

if (!$pdo_escolas) {
    die("Desculpe, o sistema de rankings está temporariamente fora do ar por problemas de conexão com o banco de dados.");
}

// SIMPLE CACHE SYSTEM
function getCachePath($key) {
    if (!is_dir(__DIR__ . '/escolas/cache')) mkdir(__DIR__ . '/escolas/cache', 0777, true);
    return __DIR__ . '/escolas/cache/' . md5($key) . '.html';
}

function getCache($key, $expiry = 86400) {
    $path = getCachePath($key);
    if (file_exists($path) && (time() - filemtime($path)) < $expiry) {
        return file_get_contents($path);
    }
    return false;
}

function setCache($key, $content) {
    file_put_contents(getCachePath($key), $content);
}

function slugify($text) {
    if (!$text) return "";
    if (function_exists('mb_strtolower')) {
        $text = mb_strtolower($text, 'UTF-8');
    } else {
        $text = strtolower($text);
    }
    $map = [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n',
        'Á' => 'a', 'À' => 'a', 'Ã' => 'a', 'Â' => 'a', 'Ä' => 'a',
        'É' => 'e', 'È' => 'e', 'Ê' => 'e', 'Ë' => 'e',
        'Í' => 'i', 'Ì' => 'i', 'Î' => 'i', 'Ï' => 'i',
        'Ó' => 'o', 'Ò' => 'o', 'Õ' => 'o', 'Ô' => 'o', 'Ö' => 'o',
        'Ú' => 'u', 'Ù' => 'u', 'Û' => 'u', 'Ü' => 'u',
        'Ç' => 'c', 'Ñ' => 'n'
    ];
    $text = strtr($text, $map);
    $text = preg_replace('~[^\w\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    return empty($text) ? 'n-a' : $text;
}

function formatBinary($val) {
    return $val ? 'Sim' : 'Não';
}

$slug = $_GET['slug'] ?? '';
$cache_key = "rankings_v2_" . ($slug ?: 'index');

$cached_content = getCache($cache_key);
if ($cached_content && !isset($_GET['nocache'])) {
    echo $cached_content;
    exit;
}

ob_start();
// Configuration of Rankings templates
$rankings_config = [
    'melhores-escolas' => [
        'title' => 'Top {limit} Melhores Escolas em {local} (Mais Equipadas)',
        'description' => 'Confira o ranking das {limit} escolas mais bem equipadas em {local}. Lista baseada em infraestrutura como laboratórios, bibliotecas e quadras.',
        'sql_order' => '(biblioteca + laboratorio_informatica + quadra_esportes + parque_infantil + internet_alunos + acessibilidade_rampas + banheiro_pne) DESC, total_alunos DESC',
        'text' => 'Este ranking apresenta as instituições de ensino em {local} que possuem a infraestrutura mais completa, considerando itens essenciais para o desenvolvimento pedagógico e bem-estar dos alunos.'
    ],
    'escolas-com-laboratorio' => [
        'title' => 'Top {limit} Escolas com Laboratório de Informática em {local}',
        'description' => 'Veja as melhores escolas que oferecem laboratório de informática em {local}. Rankings atualizados para SEO.',
        'sql_where' => 'laboratorio_informatica = 1',
        'sql_order' => 'total_alunos DESC',
        'text' => 'O acesso à tecnologia é fundamental na educação moderna. Abaixo, listamos as escolas em {local} que disponibilizam laboratórios de informática equipados para seus estudantes.'
    ],
    'escolas-com-biblioteca' => [
        'title' => 'Top {limit} Escolas com Biblioteca em {local}',
        'description' => 'Lista das {limit} melhores escolas de {local} que possuem biblioteca e incentivo à leitura.',
        'sql_where' => 'biblioteca = 1',
        'sql_order' => 'total_alunos DESC',
        'text' => 'A biblioteca é o coração de uma escola. Conheça as instituições de {local} que investem em acervo literário e espaços de estudo para os alunos.'
    ],
    'escolas-com-quadra-esportiva' => [
        'title' => 'Top {limit} Escolas com Quadra Esportiva em {local}',
        'description' => 'Ranking das escolas de {local} com melhor infraestrutura para esportes e quadras poliesportivas.',
        'sql_where' => 'quadra_esportes = 1',
        'sql_order' => 'total_alunos DESC',
        'text' => 'A prática de esportes auxilia no desenvolvimento físico e social. Confira as escolas em {local} que possuem quadras esportivas em suas dependências.'
    ],
    'escolas-com-acessibilidade-pcd' => [
        'title' => 'Top {limit} Escolas com Acessibilidade PCD em {local}',
        'description' => 'Encontre escolas acessíveis com banheiro PNE e rampas em {local}. Guia completo de educação inclusiva.',
        'sql_where' => 'banheiro_pne = 1',
        'sql_order' => 'total_alunos DESC',
        'text' => 'A inclusão é um direito. Este ranking destaca as escolas em {local} que possuem infraestrutura adaptada, como banheiros PNE e acessibilidade para pessoas com deficiência.'
    ],
    'maiores-escolas' => [
        'title' => 'Top {limit} Maiores Escolas de {local} (Por número de alunos)',
        'description' => 'Conheça as maiores instituições de ensino de {local} em número de matrículas e capacidade.',
        'sql_order' => 'total_alunos DESC',
        'text' => 'Instituições de grande porte costumam oferecer maior diversidade de turmas e recursos. Veja quais são as maiores escolas situadas em {local} atualmente.'
    ]
];

$limit = 50;
$current_ranking = null;
$local_name = 'Paraná';
$local_where = '';

if (empty($slug)) {
    $title = "Rankings das Melhores Escolas do Paraná | Van Escolar Paraná";
    $description = "Explore os rankings das melhores e maiores escolas do Paraná. Veja listas por cidade, infraestrutura, acessibilidade e mais.";
} else {
    // Parse slug
    $matched = false;
    foreach ($rankings_config as $key => $config) {
        if (strpos($slug, $key) === 0) {
            $suffix = substr($slug, strlen($key) + 1); // +1 for the dash
            $type = $key;
            $current_ranking = $config;
            
            if ($suffix == 'parana') {
                $local_name = 'Paraná';
                $local_where = '1=1';
                $matched = true;
            } else {
                // Try to find city
                $stmt_cidade = $pdo_escolas->query("SELECT DISTINCT nome_municipio FROM escolas");
                $cidades = $stmt_cidade->fetchAll(PDO::FETCH_COLUMN);
                foreach ($cidades as $c) {
                    if (slugify($c) == $suffix) {
                        $local_name = $c;
                        $local_where = "nome_municipio = " . $pdo_escolas->quote($c);
                        $matched = true;
                        if ($local_name != 'Curitiba' && $local_name != 'Londrina' && $local_name != 'Maringá') {
                            $limit = 20; // smaller cities top 20
                        }
                        break;
                    }
                }
            }
            
            if ($matched) {
                $title = str_replace(['{limit}', '{local}'], [$limit, $local_name], $current_ranking['title']);
                $description = str_replace(['{limit}', '{local}'], [$limit, $local_name], $current_ranking['description']);
                break;
            }
        }
    }
    
    if (!$matched) {
        header("Location: /rankings");
        exit;
    }
}

ob_start();

if (empty($slug)) {
    renderRankingsHome();
} else {
    renderRankingPage($current_ranking, $local_name, $local_where, $limit);
}

$content = ob_get_clean();

// LAYOUT
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title><?php echo $title; ?> | Van Escolar Paraná</title>
    <meta content="<?php echo $description; ?>" name="description" />
    <meta content="#2563eb" name="theme-color" />
    <link href="/icone-favicon.png" rel="icon" type="image/png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root { --primary-gradient: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%); }
        body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .glass-nav { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0, 0, 0, 0.05); }
        .school-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid #e5e7eb; }
        .school-card:hover { transform: translateY(-4px); border-color: #2563eb; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
    </style>
</head>
<body class="bg-[#fcfcfd] text-slate-900">
    <header class="glass-nav sticky top-0 z-[100]">
        <nav class="container mx-auto flex h-20 items-center justify-between px-6 lg:px-12">
            <a class="flex items-center gap-4 group" href="/">
                <img alt="Van Escolar Paraná" class="h-10 md:h-12 w-auto transition-transform duration-300 group-hover:scale-105" src="/logo-comum.png" />
            </a>
            <div class="hidden md:flex items-center gap-8">
                <a class="text-slate-600 hover:text-blue-700 font-semibold transition-colors" href="/sobre/">Sobre</a>
                <a class="text-slate-600 hover:text-blue-700 font-semibold transition-colors" href="/informativos/">Informativos</a>
                <a class="text-slate-600 hover:text-blue-700 font-semibold transition-colors" href="/escolas/">Escolas</a>
                <a class="text-slate-600 hover:text-blue-700 font-semibold transition-colors" href="/rankings/">Rankings</a>
                <a class="relative inline-flex items-center justify-center p-0.5 overflow-hidden text-sm font-bold text-white rounded-full bg-gradient-to-br from-blue-600 to-indigo-700 hover:shadow-lg transition-all active:scale-95" href="/destaque-sua-van/">
                    <span class="px-6 py-2.5 transition-all duration-75 bg-blue-600 rounded-full hover:bg-opacity-0">Destaque sua Van</span>
                </a>
            </div>
            <button onclick="toggleMobileMenu()" class="md:hidden p-2 text-slate-900 hover:bg-slate-100 rounded-xl transition-all focus:outline-none" aria-label="Abrir Menu">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path>
                </svg>
            </button>
        </nav>
        <div id="mobile-menu" class="fixed inset-0 z-[150] opacity-0 pointer-events-none transition-all duration-300 md:hidden">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleMobileMenu()"></div>
            <div id="mobile-menu-content" class="absolute top-0 right-0 w-[280px] h-full bg-white shadow-2xl translate-x-full transition-transform duration-300 flex flex-col p-8">
                <button onclick="toggleMobileMenu()" class="self-end p-2 text-slate-500 hover:text-slate-900 mb-8 rounded-xl hover:bg-slate-50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </button>
                <nav class="flex flex-col gap-4">
                    <a href="/" class="text-xl font-bold text-slate-800 p-4 rounded-2xl hover:bg-slate-50 transition-colors">Início</a>
                    <a href="/sobre/" class="text-xl font-bold text-slate-800 p-4 rounded-2xl hover:bg-slate-50 transition-colors">Sobre</a>
                    <a href="/informativos/" class="text-xl font-bold text-slate-800 p-4 rounded-2xl hover:bg-slate-50 transition-colors">Informativos</a>
                    <a href="/escolas/" class="text-xl font-bold text-slate-800 p-4 rounded-2xl hover:bg-slate-50 transition-colors">Escolas</a>
                    <a href="/rankings/" class="text-xl font-bold text-slate-800 p-4 rounded-2xl hover:bg-slate-50 transition-colors">Rankings</a>
                    <div class="h-px bg-slate-100 my-4"></div>
                    <a href="/destaque-sua-van/" class="bg-blue-600 text-white p-5 rounded-3xl text-center font-black text-lg shadow-xl shadow-blue-100 italic active:scale-95 transition-all">⭐ Destaque sua Van</a>
                </nav>
            </div>
        </div>
    </header>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const content = document.getElementById('mobile-menu-content');
            const isOpening = menu.classList.contains('opacity-0');
            if(isOpening) {
                menu.classList.replace('opacity-0', 'opacity-100');
                menu.classList.remove('pointer-events-none');
                content.classList.replace('translate-x-full', 'translate-x-0');
                document.body.style.overflow = 'hidden';
            } else {
                menu.classList.replace('opacity-100', 'opacity-0');
                menu.classList.add('pointer-events-none');
                content.classList.replace('translate-x-0', 'translate-x-full');
                document.body.style.overflow = '';
            }
        }
    </script>

    <main><?php echo $content; ?></main>

    <footer class="bg-gray-900 text-white py-12 text-center text-sm border-t border-gray-800">
        <div class="container mx-auto px-6">
            <img src="/logo-negativa.png" alt="Van Escolar Paraná" class="h-8 mx-auto mb-6 opacity-80">
            <p>© <?php echo date('Y'); ?> Van Escolar Paraná. Todos os direitos reservados.</p>
            <div class="mt-4 flex justify-center gap-4">
                <a href="/escolas/cidades" class="text-gray-400 hover:text-white transition-colors">Cidades</a>
                <a href="/escolas" class="text-gray-400 hover:text-white transition-colors">Escolas</a>
                <a href="/rankings" class="text-gray-400 hover:text-white transition-colors border-l pl-4 border-gray-700 font-bold">Rankings</a>
                <a href="/" class="text-gray-400 hover:text-white transition-colors">Home</a>
            </div>
        </div>
    </footer>
</body>
</html>
<?php
$final_output = ob_get_clean();
setCache($cache_key, $final_output);
echo $final_output;

function renderRankingsHome() {
    global $rankings_config, $pdo_escolas;
    
    // Get top cities
    $stmt = $pdo_escolas->query("SELECT nome_municipio, COUNT(*) as total FROM escolas GROUP BY nome_municipio ORDER BY total DESC LIMIT 10");
    $top_cities = $stmt->fetchAll();
    ?>
    <section class="py-16 bg-slate-50 border-b">
        <div class="container mx-auto px-6 lg:px-12">
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-6">Rankings de Escolas no Paraná</h1>
            <p class="text-lg text-slate-600 max-w-3xl leading-relaxed">
                Bem-vindo à seção de rankings do <strong>Van Escolar Paraná</strong>. Aqui você encontra listas automáticas e atualizadas das melhores e maiores instituições de ensino do estado, filtradas por infraestrutura, acessibilidade e localização.
            </p>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="container mx-auto px-6 lg:px-12">
            <h2 class="text-2xl font-black text-slate-800 mb-8 uppercase tracking-wider">Rankings Estaduais (Top 50)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">
                <?php foreach ($rankings_config as $key => $config): ?>
                    <a href="/rankings/<?php echo $key; ?>-parana" class="p-8 border border-slate-100 rounded-3xl hover:border-blue-500 hover:shadow-xl transition-all bg-slate-50/50 group">
                        <h3 class="text-xl font-bold text-slate-800 group-hover:text-blue-700 mb-4"><?php echo str_replace(['{limit}', '{local}'], [50, 'Paraná'], $config['title']); ?></h3>
                        <p class="text-sm text-slate-500 line-clamp-2"><?php echo $config['text']; ?></p>
                        <p class="text-[10px] font-black text-blue-600 mt-6 uppercase tracking-widest">Acessar Ranking <i class="fa-solid fa-arrow-right ml-1"></i></p>
                    </a>
                <?php endforeach; ?>
            </div>

            <h2 class="text-2xl font-black text-slate-800 mb-8 uppercase tracking-wider">Rankings por Cidade</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($top_cities as $city): 
                    $c_name = $city['nome_municipio'];
                    $c_slug = slugify($c_name);
                ?>
                    <div class="space-y-4 p-6 border border-slate-100 rounded-3xl bg-white">
                        <h4 class="text-lg font-black text-slate-900 border-b pb-2"><?php echo $c_name; ?></h4>
                        <ul class="space-y-2">
                            <li><a href="/rankings/melhores-escolas-<?php echo $c_slug; ?>" class="text-sm text-slate-600 hover:text-blue-600 transition-colors font-medium"><i class="fa-solid fa-circle text-[6px] mr-2 opacity-40"></i> Melhores Escolas</a></li>
                            <li><a href="/rankings/escolas-com-laboratorio-<?php echo $c_slug; ?>" class="text-sm text-slate-600 hover:text-blue-600 transition-colors font-medium"><i class="fa-solid fa-circle text-[6px] mr-2 opacity-40"></i> Com Laboratório</a></li>
                            <li><a href="/rankings/escolas-com-quadra-esportiva-<?php echo $c_slug; ?>" class="text-sm text-slate-600 hover:text-blue-600 transition-colors font-medium"><i class="fa-solid fa-circle text-[6px] mr-2 opacity-40"></i> Com Quadra</a></li>
                            <li><a href="/rankings/maiores-escolas-<?php echo $c_slug; ?>" class="text-sm text-slate-600 hover:text-blue-600 transition-colors font-medium"><i class="fa-solid fa-circle text-[6px] mr-2 opacity-40"></i> Maiores Escolas</a></li>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-16 text-center">
                <p class="text-slate-500 text-sm font-medium">Explorando dados de mais de 9.500 escolas em 399 municípios.</p>
            </div>
        </div>
    </section>
    <?php
}

function renderRankingPage($config, $local, $local_where, $limit) {
    global $pdo_escolas;
    
    $where = $local_where ?: '1=1';
    $where .= " AND nome_escola IS NOT NULL AND nome_escola != ''";
    
    if (!empty($config['sql_where'])) {
        $where .= " AND " . $config['sql_where'];
    }
    
    $order = $config['sql_order'] ?? 'nome_escola ASC';
    
    $sql = "SELECT id_escola, nome_escola, nome_municipio, rede_escolar, total_alunos, biblioteca, laboratorio_informatica, quadra_esportes, internet_alunos 
            FROM escolas 
            WHERE $where 
            ORDER BY $order 
            LIMIT $limit";
            
    try {
        $stmt = $pdo_escolas->query($sql);
        $schools = $stmt->fetchAll();
    } catch (Exception $e) {
        $schools = [];
    }

    $title_display = str_replace(['{limit}', '{local}'], [$limit, $local], $config['title']);
    $text_display = str_replace(['{limit}', '{local}'], [$limit, $local], $config['text']);
    ?>
    <section class="py-16 bg-slate-50 border-b">
        <div class="container mx-auto px-6 lg:px-12">
            <nav class="flex mb-4 text-xs font-bold uppercase tracking-widest text-slate-400">
                <a href="/rankings" class="hover:text-blue-600">Rankings</a>
                <span class="mx-2">/</span>
                <span class="text-slate-600"><?php echo $local; ?></span>
            </nav>
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-6"><?php echo $title_display; ?></h1>
            <p class="text-lg text-slate-600 max-w-4xl leading-relaxed">
                <?php echo $text_display; ?> No Van Escolar Paraná, facilitamos o acesso a dados públicos para pais, alunos e transportadores.
            </p>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="container mx-auto px-6 lg:px-12">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b-2 border-slate-100">
                            <th class="py-4 px-4 text-xs font-black uppercase text-slate-400">#</th>
                            <th class="py-4 px-4 text-xs font-black uppercase text-slate-400">Escola / Cidade</th>
                            <th class="py-4 px-4 text-xs font-black uppercase text-slate-400 hidden md:table-cell">Rede</th>
                            <th class="py-4 px-4 text-xs font-black uppercase text-slate-400 text-center">Alunos</th>
                            <th class="py-4 px-4 text-xs font-black uppercase text-slate-400 hidden lg:table-cell">Estrutura</th>
                            <th class="py-4 px-4 text-xs font-black uppercase text-slate-400 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php 
                        $rank = 1;
                        foreach ($schools as $esc): 
                        ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-6 px-4 font-black text-slate-300 text-xl">#<?php echo $rank++; ?></td>
                            <td class="py-6 px-4">
                                <div class="font-bold text-slate-800 text-lg mb-1"><?php echo $esc['nome_escola']; ?></div>
                                <div class="text-sm text-slate-500 font-medium"><?php echo $esc['nome_municipio']; ?></div>
                            </td>
                            <td class="py-6 px-4 hidden md:table-cell">
                                <?php 
                                $rede = mb_strtolower($esc['rede_escolar']);
                                $color_class = 'bg-slate-100 text-slate-600';
                                if (strpos($rede, 'privada') !== false) $color_class = 'bg-amber-100 text-amber-700';
                                elseif (strpos($rede, 'estadual') !== false) $color_class = 'bg-indigo-100 text-indigo-700';
                                elseif (strpos($rede, 'municipal') !== false) $color_class = 'bg-emerald-100 text-emerald-700';
                                elseif (strpos($rede, 'federal') !== false) $color_class = 'bg-purple-100 text-purple-700';
                                ?>
                                <span class="px-3 py-1 <?php echo $color_class; ?> rounded-full text-[10px] font-black uppercase"><?php echo $esc['rede_escolar']; ?></span>
                            </td>
                            <td class="py-6 px-4 text-center">
                                <span class="font-bold text-blue-600"><?php echo number_format($esc['total_alunos'], 0, ',', '.'); ?></span>
                            </td>
                            <td class="py-6 px-4 hidden lg:table-cell">
                                <div class="flex gap-3">
                                    <span title="Biblioteca" class="p-1.5 border <?php echo $esc['biblioteca'] ? 'border-blue-200 text-blue-600 bg-blue-50' : 'border-slate-100 text-slate-300'; ?> rounded-lg transition-colors">
                                        <i class="fa-solid fa-book text-xs w-4"></i>
                                    </span>
                                    <span title="Laboratório de Informática" class="p-1.5 border <?php echo $esc['laboratorio_informatica'] ? 'border-blue-200 text-blue-600 bg-blue-50' : 'border-slate-100 text-slate-300'; ?> rounded-lg transition-colors">
                                        <i class="fa-solid fa-display text-xs w-4"></i>
                                    </span>
                                    <span title="Quadra Esportiva" class="p-1.5 border <?php echo $esc['quadra_esportes'] ? 'border-blue-200 text-blue-600 bg-blue-50' : 'border-slate-100 text-slate-300'; ?> rounded-lg transition-colors">
                                        <i class="fa-solid fa-soccer-ball text-xs w-4"></i>
                                    </span>
                                    <span title="Internet para Alunos" class="p-1.5 border <?php echo $esc['internet_alunos'] ? 'border-blue-200 text-blue-600 bg-blue-50' : 'border-slate-100 text-slate-300'; ?> rounded-lg transition-colors">
                                        <i class="fa-solid fa-globe text-xs w-4"></i>
                                    </span>
                                </div>
                            </td>
                            <td class="py-6 px-4 text-right">
                                <a href="/escolas/escola/<?php echo $esc['id_escola']; ?>-<?php echo slugify($esc['nome_escola']); ?>" class="inline-flex items-center justify-center h-10 px-6 font-bold text-white bg-blue-600 rounded-full hover:bg-blue-700 transition-all text-sm shadow-lg shadow-blue-100">Ver Perfil</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($schools)): ?>
                        <tr>
                            <td colspan="6" class="py-20 text-center font-bold text-slate-400">Nenhuma escola encontrada para este ranking no momento.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- SEO FAQ -->
            <div class="mt-32 pt-20 border-t border-slate-100 max-w-4xl mx-auto">
                <h2 class="text-3xl font-black text-slate-900 mb-12 text-center">FAQ sobre o Ranking</h2>
                <div class="space-y-8">
                    <div class="bg-slate-50 p-8 rounded-3xl">
                        <h4 class="font-black text-slate-800 text-lg mb-3">Como são calculados os rankings de melhores escolas?</h4>
                        <p class="text-slate-600 leading-relaxed">Nossos rankings de "melhores escolas" consideram a soma da infraestrutura disponível. Escolas com biblioteca, laboratório de informática, quadra esportiva, internet disponível e acessibilidade completa pontuam mais alto em nossas listas de equipamentos.</p>
                    </div>
                    <div class="bg-slate-50 p-8 rounded-3xl">
                        <h4 class="font-black text-slate-800 text-lg mb-3">De onde vêm os dados das escolas de <?php echo $local; ?>?</h4>
                        <p class="text-slate-600 leading-relaxed">Os dados apresentados são extraídos de bases públicas oficiais da Secretaria da Educação e INEP (Censo Escolar), processados pelo Van Escolar Paraná para oferecer uma visão clara e objetiva para a comunidade.</p>
                    </div>
                    <div class="bg-slate-50 p-8 rounded-3xl">
                        <h4 class="font-black text-slate-800 text-lg mb-3">Este ranking garante a qualidade pedagógica?</h4>
                        <p class="text-slate-600 leading-relaxed">O ranking foca exclusivamente em infraestrutura física e capacidade de atendimento. Embora a estrutura seja um pilar fundamental, recomendamos sempre visitar a instituição e conhecer o projeto pedagógico pessoalmente.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
}
?>

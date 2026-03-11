<?php
require_once __DIR__ . '/../config/db_escolas.php';

// SIMPLE CACHE SYSTEM
function getCachePath($key) {
    return __DIR__ . '/cache/' . md5($key) . '.html';
}

function getCache($key, $expiry = 3600) {
    $path = getCachePath($key);
    if (file_exists($path) && (time() - filemtime($path)) < $expiry) {
        return file_get_contents($path);
    }
    return false;
}

function setCache($key, $content) {
    if (!is_dir(__DIR__ . '/cache')) mkdir(__DIR__ . '/cache', 0777, true);
    file_put_contents(getCachePath($key), $content);
}

// Helper functions for SEO and formatting
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

function formatBinary($val) {
    return $val ? 'Sim' : 'Não';
}

$route = $_GET['route'] ?? 'home';
$cidade_param = $_GET['cidade'] ?? '';
$bairro_param = $_GET['bairro'] ?? '';
$id_slug = $_GET['id_slug'] ?? '';

// Generate Cache Key
$cache_key = "route_{$route}_c_{$cidade_param}_b_{$bairro_param}_id_{$id_slug}";

$cached_content = getCache($cache_key, 86400); // 24h cache
if ($cached_content && !isset($_GET['nocache'])) {
    echo $cached_content;
    exit;
}

// SEO Meta variables
$title = "Escolas no Paraná - Encontre Infraestrutura e Dados | Van Escolar Paraná";
$description = "Encontre dados detalhados de mais de 9.500 escolas no Paraná. Localização, infraestrutura, número de alunos e modalidades de ensino.";
$canonical = "https://www.vanescolarparana.com/escolas" . ($route != 'home' ? "/$route" : "");

// Routing logic
ob_start();

if ($route == 'home') {
    renderHome();
} elseif ($route == 'cidades') {
    renderCidades();
} elseif ($route == 'cidade' && $cidade_param) {
    renderCidade($cidade_param);
} elseif ($route == 'bairro' && $cidade_param && $bairro_param) {
    renderBairro($cidade_param, $bairro_param);
} elseif ($route == 'escola' && $id_slug) {
    renderEscola($id_slug);
} else {
    header("Location: /escolas");
    exit;
}

$content = ob_get_clean();

// Capture output for caching
ob_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <title><?php echo $title; ?></title>
    <meta content="<?php echo $description; ?>" name="description" />
    <meta content="#2563eb" name="theme-color" />
    <link href="<?php echo $canonical; ?>" rel="canonical" />
    <link href="/icone-favicon.png" rel="icon" type="image/png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <style>
        :root { --primary-gradient: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%); }
        body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }
        .glass-nav { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0, 0, 0, 0.05); }
        .hero-glow { background: radial-gradient(circle at center, rgba(37, 99, 235, 0.15) 0%, transparent 70%); }
        .animate-fade-in { animation: fadeIn 0.8s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
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
            
            <!-- Menu Desktop -->
            <div class="hidden md:flex items-center gap-8">
                <a class="text-slate-600 hover:text-blue-700 font-semibold transition-colors" href="/sobre/">Sobre</a>
                <a class="text-slate-600 hover:text-blue-700 font-semibold transition-colors" href="/informativos/">Informativos</a>
                <a class="text-slate-600 hover:text-blue-700 font-semibold transition-colors" href="/escolas/">Escolas</a>
                <a class="relative inline-flex items-center justify-center p-0.5 overflow-hidden text-sm font-bold text-white rounded-full bg-gradient-to-br from-blue-600 to-indigo-700 hover:shadow-lg transition-all active:scale-95" href="/destaque-sua-van/">
                    <span class="px-6 py-2.5 transition-all duration-75 bg-blue-600 rounded-full hover:bg-opacity-0">Destaque sua Van</span>
                </a>
            </div>

            <!-- Botão Menu Mobile -->
            <button onclick="toggleMobileMenu()" class="md:hidden p-2 text-slate-900 hover:bg-slate-100 rounded-xl transition-all focus:outline-none" aria-label="Abrir Menu">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path id="menu-icon-path" d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path>
                </svg>
            </button>
        </nav>

        <!-- Menu Mobile Overlay -->
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

    <main>
        <?php echo $content; ?>
    </main>

    <footer class="bg-gray-900 text-white py-12 text-center text-sm border-t border-gray-800">
        <div class="container mx-auto px-6">
            <img src="/logo-negativa.png" alt="Van Escolar Paraná" class="h-8 mx-auto mb-6 opacity-80">
            <p>© <?php echo date('Y'); ?> Van Escolar Paraná. Todos os direitos reservados.</p>
            <div class="mt-4 flex justify-center gap-4">
                <a href="/escolas/cidades" class="text-gray-400 hover:text-white transition-colors">Cidades</a>
                <a href="/escolas" class="text-gray-400 hover:text-white transition-colors">Escolas</a>
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

// Implementation functions

function renderHome() {
    global $pdo_escolas;
    ?>
    <section class="relative pt-12 pb-24 lg:pt-20 lg:pb-32 bg-slate-50">
        <div class="container mx-auto px-6 lg:px-12">
            <div class="max-w-4xl mx-auto text-center space-y-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 border border-blue-100 text-blue-700 text-sm font-bold">
                    Dados Atualizados 2024/2025
                </div>
                <h1 class="text-4xl md:text-6xl font-black text-slate-900 leading-tight">Guia Completo de Escolas do Paraná</h1>
                <p class="text-lg md:text-xl text-slate-600 font-medium max-w-2xl mx-auto">Explore infraestrutura, número de alunos, acessibilidade e localização de mais de 9.500 instituições de ensino no estado.</p>
                <div class="mt-12">
                    <a href="/escolas/cidades" class="inline-block px-10 py-5 bg-blue-600 text-white rounded-full font-black text-xl hover:bg-blue-700 transition-all shadow-xl shadow-blue-200 hover:scale-105 active:scale-95">Explorar Municípios do Paraná</a>
                </div>
            </div>
        </div>
    </section>
    
    <section class="py-24 bg-white">
        <div class="container mx-auto px-6 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
                <div class="space-y-6">
                    <h2 class="text-3xl md:text-4xl font-black text-slate-900 leading-tight">Transparência e Dados para Pais e Motoristas</h2>
                    <p class="text-slate-600 text-lg leading-relaxed">Encontre a escola ideal para seus filhos comparando a estrutura física e as modalidades oferecidas. Para motoristas de transporte escolar, o guia é uma ferramenta essencial para identificar escolas por bairro e planejar rotas.</p>
                    <ul class="space-y-4">
                        <li class="flex items-center gap-3 font-bold text-slate-700">
                            <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs">✓</span>
                            Mais de 9.500 escolas cadastradas
                        </li>
                        <li class="flex items-center gap-3 font-bold text-slate-700">
                            <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs">✓</span>
                            Dados de acessibilidade e infraestrutura
                        </li>
                        <li class="flex items-center gap-3 font-bold text-slate-700">
                            <span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xs">✓</span>
                            Modalidades do Infantil ao Ensino Médio
                        </li>
                    </ul>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-blue-600 p-8 rounded-[2rem] text-white space-y-2">
                        <p class="text-4xl font-black">399</p>
                        <p class="text-sm font-bold opacity-80">Municípios</p>
                    </div>
                    <div class="bg-slate-800 p-8 rounded-[2rem] text-white space-y-2">
                        <p class="text-4xl font-black">9.5k+</p>
                        <p class="text-sm font-bold opacity-80">Escolas</p>
                    </div>
                    <div class="bg-slate-100 p-8 rounded-[2rem] text-slate-900 col-span-2 space-y-2">
                        <p class="text-4xl font-black">1.5Mi+</p>
                        <p class="text-sm font-bold text-slate-500">Alunos Atendidos</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php
}

function renderCidades() {
    global $pdo_escolas, $title, $description;
    $title = "Cidades com Escolas no Paraná | Van Escolar Paraná";
    $description = "Lista de todos os municípios do Paraná com dados detalhados das escolas municipais e estaduais.";

    if (!$pdo_escolas) {
        echo "<div class='container mx-auto p-20 text-center font-bold'>Erro na conexão com o banco de dados. Por favor, tente novamente mais tarde.</div>";
        return;
    }

    $stmt = $pdo_escolas->query("SELECT DISTINCT nome_municipio FROM escolas ORDER BY nome_municipio");
    $cidades = $stmt->fetchAll(PDO::FETCH_COLUMN);

    ?>
    <section class="py-16 bg-slate-50 border-b">
        <div class="container mx-auto px-6 lg:px-12">
            <nav class="flex mb-4 text-xs font-bold uppercase tracking-widest text-slate-400">
                <a href="/escolas" class="hover:text-blue-600">Escolas</a>
                <span class="mx-2">/</span>
                <span class="text-slate-600">Municípios</span>
            </nav>
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-4">Escolas por Cidade</h1>
            <p class="text-slate-600 font-medium">Selecione o município desejado para explorar os bairros e instituições de ensino.</p>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="container mx-auto px-6 lg:px-12">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <?php foreach ($cidades as $cidade): ?>
                    <a href="/escolas/cidade/<?php echo urlencode($cidade); ?>" class="p-4 border border-slate-100 rounded-xl hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50/30 transition-all font-bold text-center bg-white shadow-sm"><?php echo $cidade; ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function renderCidade($cidade) {
    global $pdo_escolas, $title, $description;
    $cidade_decoded = urldecode($cidade);
    $title = "Escolas em $cidade_decoded - Bairros e Regiões | Van Escolar Paraná";
    $description = "Confira a lista de bairros atendidos e todas as escolas disponíveis em $cidade_decoded, Paraná.";

    if (!$pdo_escolas) {
        echo "<div class='container mx-auto p-20 text-center font-bold'>Erro na conexão com o banco de dados.</div>";
        return;
    }

    $stmt = $pdo_escolas->prepare("SELECT DISTINCT bairro FROM escolas WHERE nome_municipio = ? ORDER BY bairro");
    $stmt->execute([$cidade_decoded]);
    $bairros = $stmt->fetchAll(PDO::FETCH_COLUMN);

    ?>
    <section class="py-16 bg-slate-50 border-b">
        <div class="container mx-auto px-6 lg:px-12">
            <nav class="flex mb-4 text-xs font-bold uppercase tracking-widest text-slate-400">
                <a href="/escolas" class="hover:text-blue-600">Escolas</a>
                <span class="mx-2">/</span>
                <a href="/escolas/cidades" class="hover:text-blue-600">Municípios</a>
                <span class="mx-2">/</span>
                <span class="text-slate-600"><?php echo $cidade_decoded; ?></span>
            </nav>
            <h1 class="text-4xl md:text-5xl font-black text-slate-900 mb-4">Bairros de <?php echo $cidade_decoded; ?></h1>
            <p class="text-slate-600 font-medium italic">Selecione o bairro para listar as escolas da região.</p>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="container mx-auto px-6 lg:px-12">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($bairros as $bairro): 
                    $bairro_label = $bairro ?: 'Lista Geral';
                    ?>
                    <a href="/escolas/cidade/<?php echo urlencode($cidade_decoded); ?>/<?php echo urlencode($bairro); ?>" class="p-8 border border-slate-100 rounded-3xl hover:border-blue-600 hover:shadow-xl transition-all group bg-slate-50/50">
                        <h3 class="text-xl font-black text-slate-800 group-hover:text-blue-700 leading-tight"><?php echo $bairro_label; ?></h3>
                        <p class="text-[10px] font-black tracking-widest text-blue-600 mt-4 uppercase">Ver Escolas →</p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

function renderBairro($cidade, $bairro) {
    global $pdo_escolas, $title, $description;
    $cidade_decoded = urldecode($cidade);
    $bairro_decoded = urldecode($bairro);
    $title = "Escolas no Bairro $bairro_decoded em $cidade_decoded | Van Escolar Paraná";
    $description = "Lista completa de escolas localizadas no bairro $bairro_decoded em $cidade_decoded. Veja detalhes de infraestrutura e alunos.";

    // Pagination
    $page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    if (!$pdo_escolas) {
        echo "<div class='container mx-auto p-20 text-center font-bold'>Erro na conexão com o banco de dados.</div>";
        return;
    }

    $stmt_count = $pdo_escolas->prepare("SELECT COUNT(*) FROM escolas WHERE nome_municipio=? AND bairro=?");
    $stmt_count->execute([$cidade_decoded, $bairro_decoded]);
    $total = $stmt_count->fetchColumn();
    $totalPages = ceil($total / $perPage);

    $stmt = $pdo_escolas->prepare("SELECT * FROM escolas WHERE nome_municipio=? AND bairro=? ORDER BY nome_escola LIMIT $perPage OFFSET $offset");
    $stmt->execute([$cidade_decoded, $bairro_decoded]);
    $escolas = $stmt->fetchAll();

    ?>
    <section class="py-16 bg-slate-50 border-b">
        <div class="container mx-auto px-6 lg:px-12">
            <nav class="flex mb-4 text-xs font-bold uppercase tracking-widest text-slate-400">
                <a href="/escolas" class="hover:text-blue-600">Escolas</a>
                <span class="mx-2">/</span>
                <a href="/escolas/cidades" class="hover:text-blue-600">Municípios</a>
                <span class="mx-2">/</span>
                <a href="/escolas/cidade/<?php echo urlencode($cidade_decoded); ?>" class="hover:text-blue-600"><?php echo $cidade_decoded; ?></a>
                <span class="mx-2">/</span>
                <span class="text-slate-600"><?php echo $bairro_decoded ?: 'Bairro'; ?></span>
            </nav>
            <h1 class="text-3xl md:text-4xl font-black text-slate-900 mb-4">Escolas: <?php echo $bairro_decoded ?: 'não identificado'; ?> (<?php echo $cidade_decoded; ?>)</h1>
            <p class="text-slate-600 font-medium">Exibindo instituições de ensino localizadas no bairro.</p>
        </div>
    </section>

    <section class="py-16 bg-white">
        <div class="container mx-auto px-6 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($escolas as $escola): 
                    $slug = slugify($escola['nome_escola']);
                    ?>
                    <a href="/escolas/escola/<?php echo $escola['id_escola']; ?>-<?php echo $slug; ?>" class="school-card p-8 rounded-3xl bg-white flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <span class="text-[10px] font-black uppercase tracking-widest text-blue-600 bg-blue-50 px-3 py-1 rounded-full"><?php echo $escola['rede_escolar']; ?></span>
                                <?php if($escola['banheiro_pne']): ?>
                                    <span class="text-lg" title="Acessibilidade PCD">♿</span>
                                <?php endif; ?>
                            </div>
                            <h2 class="text-xl font-black text-slate-800 mb-2 leading-tight"><?php echo $escola['nome_escola']; ?></h2>
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded"><?php echo $escola['zona_localizacao']; ?></span>
                                <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded"><?php echo $escola['total_alunos']; ?> Alunos</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-4 text-blue-700 font-bold text-xs group-hover:gap-4 transition-all">
                            VER FICHA COMPLETA 
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 8l4 4m0 0l-4 4m4-4H3" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="mt-16 flex justify-center gap-2">
                    <?php for($i=1; $i<=$totalPages; $i++): ?>
                        <a href="?p=<?php echo $i; ?>" class="w-10 h-10 flex items-center justify-center rounded-lg font-bold <?php echo $i==$page ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

function renderEscola($id_slug) {
    global $pdo_escolas, $title, $description;
    $id = intval(explode('-', $id_slug)[0]);
    
    if (!$pdo_escolas) {
        echo "<div class='container mx-auto p-20 text-center font-bold'>Erro na conexão com o banco de dados.</div>";
        return;
    }

    $stmt = $pdo_escolas->prepare("SELECT * FROM escolas WHERE id_escola = ?");
    $stmt->execute([$id]);
    $escola = $stmt->fetch();

    if (!$escola) {
        echo "<div class='container mx-auto p-20 text-center font-bold'>Escola não encontrada.</div>";
        return;
    }

    $nome = $escola['nome_escola'];
    $municipio = $escola['nome_municipio'];
    
    $title = "Escola $nome em $municipio – Estrutura e alunos";
    $description = "Detalhes da escola $nome localizada em $municipio. Confira o total de alunos, infraestrutura, acessibilidade e modalidades de ensino oferecidas.";

    // SEO Text generation
    $seo_text = "A escola <strong>$nome</strong> está localizada no município de <strong>$municipio</strong>, Paraná. ";
    $seo_text .= "Pertencente à rede de ensino <strong>" . $escola['rede_escolar'] . "</strong>, a instituição atende a zona <strong>" . strtolower($escola['zona_localizacao']) . "</strong>. ";
    $seo_text .= "Atualmente, conta com um total de <strong>" . $escola['total_salas'] . " salas de aula</strong> para atender seus <strong>" . $escola['total_alunos'] . " alunos</strong> matriculados. ";
    
    $infra = [];
    if($escola['biblioteca']) $infra[] = "biblioteca";
    if($escola['laboratorio_informatica']) $infra[] = "laboratório de informática";
    if($escola['quadra_esportes']) $infra[] = "quadra esportiva";
    if($escola['cozinha']) $infra[] = "cozinha";
    if($escola['refeitorio']) $infra[] = "refeitório";
    if($escola['internet_alunos']) $infra[] = "acesso à internet para alunos";
    
    if(!empty($infra)) {
        if(count($infra) > 1) {
            $seo_text .= "A estrutura física da escola oferece " . implode(", ", array_slice($infra, 0, -1)) . " e " . end($infra) . ". ";
        } else {
            $seo_text .= "A estrutura física da escola oferece " . $infra[0] . ". ";
        }
    }

    ?>
    <section class="py-12 bg-slate-50 border-b">
        <div class="container mx-auto px-6 lg:px-12">
            <nav class="flex mb-8 text-xs font-bold uppercase tracking-widest text-slate-400">
                <a href="/escolas" class="hover:text-blue-600">Escolas</a>
                <span class="mx-2">/</span>
                <a href="/escolas/cidade/<?php echo urlencode($municipio); ?>" class="hover:text-blue-600"><?php echo $municipio; ?></a>
                <span class="mx-2">/</span>
                <span class="text-slate-600"><?php echo $nome; ?></span>
            </nav>

            <div class="flex flex-col lg:flex-row justify-between items-start gap-12">
                <div class="max-w-3xl">
                    <div class="flex items-center gap-4 mb-6">
                        <span class="px-5 py-2 bg-blue-100 text-blue-800 rounded-full text-xs font-black uppercase tracking-wider"><?php echo $escola['rede_escolar']; ?></span>
                        <?php if($escola['banheiro_pne']): ?>
                            <span class="text-3xl" title="Acessibilidade PCD">♿</span>
                        <?php endif; ?>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-black text-slate-900 leading-[1.1] mb-8 tracking-tight"><?php echo $nome; ?></h1>
                    <div class="prose prose-lg text-slate-600 leading-relaxed max-w-none">
                        <p class="text-xl md:text-2xl font-medium text-slate-500 mb-6"><?php echo $municipio; ?>, Paraná</p>
                        <p><?php echo $seo_text; ?></p>
                    </div>
                </div>
                
                <div class="w-full lg:w-96">
                    <div class="bg-white border-2 border-slate-100 p-10 rounded-[2.5rem] shadow-xl shadow-slate-100">
                        <div class="text-center mb-8">
                            <p class="text-6xl font-black text-blue-600 leading-none mb-4"><?php echo $escola['total_alunos']; ?></p>
                            <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Alunos Matriculados</p>
                        </div>
                        <div class="space-y-6 pt-8 border-t border-slate-50">
                            <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl">
                                <span class="text-sm font-bold text-slate-500">Salas de Aula</span>
                                <span class="text-lg font-black text-slate-900"><?php echo $escola['total_salas']; ?></span>
                            </div>
                            <div class="flex justify-between items-center bg-slate-50 p-4 rounded-2xl">
                                <span class="text-sm font-bold text-slate-500">Zona</span>
                                <span class="text-lg font-black text-slate-900"><?php echo $escola['zona_localizacao']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-white">
        <div class="container mx-auto px-6 lg:px-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Informações Gerais -->
                <div class="bg-white border-2 border-slate-50 rounded-[2rem] p-10 hover:border-blue-100 transition-all">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 text-2xl mb-8">📍</div>
                    <h3 class="text-2xl font-black text-slate-900 mb-8">Informações Gerais</h3>
                    <ul class="space-y-6">
                        <li class="flex flex-col">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Município</span>
                            <span class="text-lg font-bold text-slate-700"><?php echo $escola['nome_municipio']; ?></span>
                        </li>
                        <li class="flex flex-col">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Rede de Ensino</span>
                            <span class="text-lg font-bold text-slate-700"><?php echo $escola['rede_escolar']; ?></span>
                        </li>
                        <li class="flex flex-col">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Localização</span>
                            <span class="text-lg font-bold text-slate-700"><?php echo $escola['bairro'] ?: 'Diversos/Centro'; ?></span>
                        </li>
                    </ul>
                </div>

                <!-- Estrutura da Escola -->
                <div class="bg-white border-2 border-slate-50 rounded-[2rem] p-10 hover:border-blue-100 transition-all">
                    <div class="w-14 h-14 rounded-2xl bg-green-50 flex items-center justify-center text-green-600 text-2xl mb-8">🏢</div>
                    <h3 class="text-2xl font-black text-slate-900 mb-8">Estrutura Física</h3>
                    <ul class="grid grid-cols-2 gap-y-6 gap-x-4">
                        <li class="flex flex-col">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Biblioteca</span>
                            <span class="font-bold text-slate-700 text-lg"><?php echo formatBinary($escola['biblioteca']); ?></span>
                        </li>
                        <li class="flex flex-col">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Laboratório</span>
                            <span class="font-bold text-slate-700 text-lg"><?php echo formatBinary($escola['laboratorio_informatica']); ?></span>
                        </li>
                        <li class="flex flex-col">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Quadra</span>
                            <span class="font-bold text-slate-700 text-lg"><?php echo formatBinary($escola['quadra_esportes']); ?></span>
                        </li>
                        <li class="flex flex-col">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Refeitório</span>
                            <span class="font-bold text-slate-700 text-lg"><?php echo formatBinary($escola['refeitorio']); ?></span>
                        </li>
                        <li class="flex flex-col">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Internet</span>
                            <span class="font-bold text-slate-700 text-lg"><?php echo formatBinary($escola['internet_alunos']); ?></span>
                        </li>
                        <li class="flex flex-col">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Cozinha</span>
                            <span class="font-bold text-slate-700 text-lg"><?php echo formatBinary($escola['cozinha']); ?></span>
                        </li>
                    </ul>
                </div>

                <!-- Acessibilidade e Modalidades -->
                <div class="bg-white border-2 border-slate-50 rounded-[2rem] p-10 hover:border-blue-100 transition-all">
                    <div class="w-14 h-14 rounded-2xl bg-orange-50 flex items-center justify-center text-orange-600 text-2xl mb-8">♿</div>
                    <h3 class="text-2xl font-black text-slate-900 mb-8">Acessibilidade</h3>
                    <ul class="space-y-4 mb-10">
                        <li class="flex justify-between items-center border-b border-slate-50 pb-3">
                            <span class="font-bold text-slate-500">Banheiro PNE</span>
                            <span class="font-black text-slate-800"><?php echo formatBinary($escola['banheiro_pne']); ?></span>
                        </li>
                        <li class="flex justify-between items-center border-b border-slate-50 pb-3">
                            <span class="font-bold text-slate-500">Rampas de Acesso</span>
                            <span class="font-black text-slate-800"><?php echo formatBinary($escola['acessibilidade_rampas'] ?? 0); ?></span>
                        </li>
                    </ul>
                    <h3 class="text-xl font-black text-slate-900 mb-4">Ensino</h3>
                    <div class="flex flex-wrap gap-2">
                        <?php 
                        $modalidades = [
                            'creche' => $escola['tem_creche'] ?? 0,
                            'pré-escola' => $escola['tem_pre_escola'] ?? 0,
                            'fundamental 1' => $escola['tem_fundamental_1'] ?? 0,
                            'fundamental 2' => $escola['tem_fundamental_2'] ?? 0,
                            'médio' => $escola['tem_ensino_medio'] ?? 0,
                            'especial' => $escola['tem_educacao_especial'] ?? 0
                        ];
                        foreach($modalidades as $label => $val):
                            if($val):
                        ?>
                            <span class="px-3 py-1 bg-slate-100 rounded-full text-[10px] font-black text-slate-500 uppercase"><?php echo $label; ?></span>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="mt-32 pt-24 border-t-2 border-slate-50">
                <div class="text-center max-w-2xl mx-auto mb-16">
                    <h2 class="text-3xl md:text-4xl font-black text-slate-900 mb-4">Dúvidas Frequentes</h2>
                    <p class="text-slate-500 font-medium">Informações resumidas sobre o atendimento e estrutura da escola.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 max-w-5xl mx-auto">
                    <?php 
                    $faqs = [
                        ["A escola possui laboratório?", "Sim, a escola $nome possui laboratório de informática para atividades práticas." , $escola['laboratorio_informatica']],
                        ["Existe quadra esportiva disponível?", "Sim, a instituição conta com quadra para prática de esportes." , $escola['quadra_esportes']],
                        ["A escola é acessível para PCD?", "A escola possui banheiro adaptado para pessoas com deficiência." , $escola['banheiro_pne']],
                        ["Tem biblioteca no local?", "Sim, os alunos possuem acesso à biblioteca para estudos e pesquisas." , $escola['biblioteca']],
                        ["Os alunos têm acesso à internet?", "Sim, a escola disponibiliza acesso à rede mundial de computadores para os estudantes." , $escola['internet_alunos']],
                        ["A escola oferece ensino médio?", "Esta instituição oferece a modalidade de ensino médio." , $escola['tem_ensino_medio'] ?? 0],
                        ["Existe atendimento de educação especial?", "Sim, a escola oferece suporte para educação especial." , $escola['tem_educacao_especial'] ?? 0],
                        ["Quantos alunos estudam nesta escola?", "Atualmente a escola atende um total de " . $escola['total_alunos'] . " alunos." , 1],
                        ["A escola possui refeitório para os alunos?", "Sim, a estrutura conta com refeitório para alimentação escolar." , $escola['refeitorio']],
                        ["Existe parque infantil ou área recreativa?", "Sim, a escola possui espaços destinados ao lazer infantil." , $escola['parque_infantil'] ?? 0]
                    ];
                    
                    foreach($faqs as $faq):
                    ?>
                    <div class="bg-white p-2 rounded-2xl">
                        <h4 class="font-black text-slate-800 text-lg mb-2">● <?php echo $faq[0]; ?></h4>
                        <p class="text-slate-500 font-medium leading-relaxed pl-6"><?php echo $faq[2] ? $faq[1] : "No momento não dispomos dessa informação ou a estrutura não está disponível conforme os dados da Secretaria de Educação."; ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php
}
?>

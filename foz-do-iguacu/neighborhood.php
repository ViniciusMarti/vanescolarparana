<?php
require_once __DIR__ . '/../config/db.php';

// Descobre a cidade dinamicamente a partir da pasta onde o script está
$cidade_atual = basename(__DIR__);
$cidade_nome_limpo = ucwords(str_replace('-', ' ', $cidade_atual));
if ($cidade_atual == 'foz-do-iguacu') $cidade_nome_limpo = 'Foz do Iguaçu';
if ($cidade_atual == 'maringa') $cidade_nome_limpo = 'Maringá';

// Carrega os dados SEO dos bairros da cidade atual
$neighborhoods_json = file_get_contents(__DIR__ . '/neighborhood_data.json');
$neighborhoods_data = json_decode($neighborhoods_json, true);

// Pega o bairro da URL (ex: /londrina/aeroporto)
$bairro_slug = $_GET['bairro'] ?? '';

if (!$bairro_slug || !isset($neighborhoods_data[$bairro_slug])) {
    // Se não encontrar o bairro, redireciona para a home da cidade
    header("Location: /$cidade_atual/");
    exit;
}

// 1. Detector de Tabelas Robusto
try {
    $stmt = $pdo->query("SHOW TABLES");
    $all_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $table_name = 'vans'; // Default
    
    $candidates = ['u582732852_vans_curitiba', 'vans']; 
    foreach($all_tables as $t) if(!in_array($t, $candidates)) $candidates[] = $t;

    foreach ($candidates as $candidate) {
        if (!in_array($candidate, $all_tables)) continue;
        $check = $pdo->query("SHOW COLUMNS FROM `$candidate` LIKE 'bairro_referencia'");
        if ($check->fetch()) {
            $table_name = $candidate;
            break;
        }
    }

    if (!in_array('destaques_premium', $all_tables)) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `destaques_premium` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `permissionario` VARCHAR(255) NOT NULL,
            `prefixo` VARCHAR(50),
            `bairro` VARCHAR(255) NOT NULL,
            `vencimento` DATE NOT NULL,
            INDEX (`bairro`), INDEX (`vencimento`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }
} catch (Exception $e) { $table_name = 'vans'; }

$neighborhood = $neighborhoods_data[$bairro_slug];
$nome_bairro_exibicao = ucwords(str_replace('-', ' ', $bairro_slug));

// Função para limpar acentos para busca
function sanitizeForSearch($str) {
    $map = ['á'=>'a','à'=>'a','ã'=>'a','â'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c'];
    return str_replace(array_keys($map), array_values($map), mb_strtolower($str));
}

$bairro_busca = sanitizeForSearch($nome_bairro_exibicao);
if ($bairro_slug == 'ahu') $nome_bairro_exibicao = 'Ahú'; // Para exibição visual

$vans = [];
try {
    // 1. Tenta busca exata com o nome exibido (com acentos se houver)
    // Ordenamos por permissionario para ser justo
    $query = "SELECT * FROM `$table_name` 
              WHERE `bairro_referencia` LIKE :bairro_query 
              ORDER BY `permissionario` ASC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute(['bairro_query' => '%' . $nome_bairro_exibicao . '%']);
    $vans = $stmt->fetchAll();
    
    // 2. Se falhou, tenta busca sem acentos
    if (empty($vans) && $nome_bairro_exibicao !== $bairro_busca) {
        $stmt->execute(['bairro_query' => '%' . $bairro_busca . '%']);
        $vans = $stmt->fetchAll();
    }

    // 3. Processa destaques de forma separada no PHP para não quebrar a query SQL
    $destaques_stmt = $pdo->prepare("SELECT permissionario, prefixo FROM `destaques_premium` WHERE `bairro` = ? AND `vencimento` >= CURDATE()");
    $destaques_stmt->execute([$nome_bairro_exibicao]);
    $lista_premium = $destaques_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Mapeia quem é premium
    foreach ($vans as &$v) {
        $v['is_premium_active'] = 0;
        foreach ($lista_premium as $p) {
            if ($v['permissionario'] == $p['permissionario']) {
                $v['is_premium_active'] = 1;
                break;
            }
        }
    }
    unset($v);

    // Re-ordena: Premium primeiro
    usort($vans, function($a, $b) {
        if ($a['is_premium_active'] != $b['is_premium_active']) {
            return $b['is_premium_active'] - $a['is_premium_active'];
        }
        return strcmp($a['permissionario'], $b['permissionario']);
    });

} catch (PDOException $e) {
    // echo "DEBUG: Erro SQL: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta content="noai, noimageai" name="robots" />
  <script async="" src="https://www.googletagmanager.com/gtag/js?id=G-ETL54HBEXL">
  </script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());

    gtag('config', 'G-ETL54HBEXL');
  </script>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title><?php echo $neighborhood['title']; ?></title>
  <meta content="<?php echo $neighborhood['description']; ?>" name="description" />
  <meta content="#2563eb" name="theme-color" />
  
  <link href="/icone-favicon.png" rel="icon" type="image/png" />
  <script src="https://cdn.tailwindcss.com"></script>
  
  <link href="https://fonts.googleapis.com" rel="preconnect" />
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
  <link href="/fontawesome/css/all.min.css" rel="stylesheet"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
  
  <style>
    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; font-size: 16px; }
    .btn-whatsapp { background-color: #25d366; color: white; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: all 0.2s; }
    .btn-whatsapp:hover { background-color: #128c7e; transform: scale(1.02); }
    .step-card { background: white; border: 2px solid #e2e8f0; }
    .glass-nav { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0, 0, 0, 0.05); }
    .van-card { border: 1px solid #e2e8f0; transition: border-color 0.2s; }
    .van-card:hover { border-color: #2563eb; }
    .seo-content { line-height: 1.6; }
    .seo-content h2 { font-size: 1.5rem; font-weight: 800; margin-top: 2.5rem; margin-bottom: 1.25rem; text-align: center; color: #1e293b; }
    @media (min-width: 768px) { .seo-content h2 { font-size: 1.75rem; } }
    .seo-content h3 { font-size: 1.25rem; font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.85rem; color: #2563eb; }
    .seo-content p { margin-bottom: 1.15rem; color: #475569; }
    .seo-content ul { list-style: disc; margin-left: 1.5rem; margin-bottom: 1.25rem; }
    .seo-content strong { color: #1e293b; font-weight: 700; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
  </style>
  
  <script>
    (function (w, d, s, l, i) { w[l] || (w[l] = []); w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' }); var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f); })(window, document, 'script', 'dataLayer', 'GTM-WKBVRTDG');
  </script>
  <link href="https://www.vanescolarparana.com/<?php echo $cidade_atual; ?>/<?php echo $bairro_slug; ?>/" rel="canonical" />
</head>

<body>
  <noscript><iframe height="0" src="https://www.googletagmanager.com/ns.html?id=GTM-WKBVRTDG" style="display:none;visibility:hidden" width="0"></iframe></noscript>

  <header class="glass-nav sticky top-0 z-[100] transition-all duration-300">
    <nav class="container mx-auto flex h-20 items-center justify-between px-6 lg:px-12">
      <a class="flex items-center gap-4 group" href="/"><img alt="Van Escolar Paraná" class="h-10 md:h-12 w-auto transition-transform duration-300 group-hover:scale-105" src="/logo-comum.png" /></a>
      
      <!-- Desktop Menu -->
      <div class="hidden md:flex items-center gap-8 text-sm md:text-base">
        <a class="text-slate-600 hover:text-blue-700 font-semibold transition-colors" href="/sobre/">Sobre</a>
        <a class="text-slate-600 hover:text-blue-700 font-semibold transition-colors" href="/informativos/">Informativos</a>
        <a class="relative inline-flex items-center justify-center p-0.5 overflow-hidden text-sm font-bold text-white rounded-full bg-gradient-to-r from-blue-600 to-indigo-700 hover:shadow-lg hover:scale-105 transition-all active:scale-95 px-6 py-2.5" href="/destaque-sua-van/"><i class="fa-solid fa-star"></i> Destaque sua Van</a>
      </div>

      <!-- Mobile Menu Button -->
      <button onclick="toggleMobileMenu()" class="md:hidden p-2 text-slate-900 focus:outline-none rounded-xl hover:bg-slate-100 transition-all">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path></svg>
      </button>
    </nav>

    <!-- Menu Mobile Overlay -->
    <div id="mobile-menu" class="fixed inset-0 z-[150] opacity-0 pointer-events-none transition-all duration-300 md:hidden">
        <!-- Bloqueio de fundo -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="toggleMobileMenu()"></div>
        
        <!-- Conteúdo do Menu -->
        <div id="mobile-menu-content" class="absolute top-0 right-0 w-[280px] h-full bg-white shadow-2xl translate-x-full transition-transform duration-300 flex flex-col p-8">
            <button onclick="toggleMobileMenu()" class="self-end p-2 text-slate-500 hover:text-slate-900 mb-8 rounded-xl hover:bg-slate-50">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></svg>
            </button>
            <nav class="flex flex-col gap-4 text-left">
                <a href="/" class="text-xl font-bold text-slate-800 p-4 rounded-2xl hover:bg-slate-50 transition-colors">Início</a>
                <a href="/sobre/" class="text-xl font-bold text-slate-800 p-4 rounded-2xl hover:bg-slate-50 transition-colors">Sobre</a>
                <a href="/informativos/" class="text-xl font-bold text-slate-800 p-4 rounded-2xl hover:bg-slate-50 transition-colors">Informativos</a>
                <div class="h-px bg-slate-100 my-4"></div>
                <a href="/destaque-sua-van/" class="bg-blue-600 text-white p-5 rounded-3xl text-center font-black text-lg shadow-xl shadow-blue-100 italic active:scale-95 transition-all"><i class="fa-solid fa-star"></i> Destaque sua Van</a>
            </nav>
            <p class="text-slate-400 text-center text-xs mt-auto">Van Escolar Paraná © <?php echo date('Y'); ?></p>
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

  <main class="container mx-auto px-4 py-8 max-w-4xl">
    
    <div class="bg-blue-600 rounded-3xl p-8 mb-12 text-white shadow-xl relative overflow-hidden">
        <h1 class="text-2xl md:text-3xl font-extrabold mb-4">Procurando Van Escolar no <?php echo $nome_bairro_exibicao; ?>?</h1>
        <p class="text-lg md:text-xl text-blue-50 font-medium">Ajudamos você a encontrar o transporte mais seguro para seus filhos.</p>
    </div>

    <!-- Passo a Passo Simples -->
    <section class="mb-12">
      <h2 class="text-xl font-bold mb-6 flex items-center gap-2 text-slate-800">
        <span class="bg-blue-100 text-blue-600 w-8 h-8 rounded-full flex items-center justify-center text-lg">?</span>
        Como funciona o site
      </h2>
      <div class="flex md:grid md:grid-cols-3 gap-4 overflow-x-auto pb-6 -mx-4 px-4 md:mx-0 md:px-0 snap-x snap-mandatory no-scrollbar">
        <div class="flex-shrink-0 w-[280px] md:w-auto snap-center step-card p-6 rounded-2xl flex items-start gap-4 shadow-sm border border-slate-100 bg-white">
          <div class="text-3xl bg-slate-50 w-12 h-12 rounded-xl flex items-center justify-center"><i class="fa-solid fa-eye text-blue-600"></i></div>
          <div><p class="font-bold text-lg">1. Olhe a lista</p><p class="text-gray-500 text-sm italic">Escolha um motorista</p></div>
        </div>
        <div class="flex-shrink-0 w-[280px] md:w-auto snap-center step-card p-6 rounded-2xl flex items-start gap-4 shadow-md border-2 border-blue-100 bg-blue-50/30">
          <div class="text-3xl bg-blue-100/50 w-12 h-12 rounded-xl flex items-center justify-center"><i class="fa-solid fa-mobile-screen-button text-blue-600"></i></div>
          <div><p class="font-bold text-lg text-blue-900">2. Clique no Botão</p><p class="text-blue-700/70 text-sm italic">Tire dúvidas no Whats</p></div>
        </div>
        <div class="flex-shrink-0 w-[280px] md:w-auto snap-center step-card p-6 rounded-2xl flex items-start gap-4 shadow-sm border border-slate-100 bg-white">
          <div class="text-3xl bg-slate-50 w-12 h-12 rounded-xl flex items-center justify-center"><i class="fa-solid fa-handshake text-blue-600"></i></div>
          <div><p class="font-bold text-lg">3. Combine direto</p><p class="text-gray-500 text-sm italic">Simples e seguro</p></div>
        </div>
      </div>
    </section>

    <section class="mb-12">
      <h2 class="text-2xl font-bold mb-6 text-slate-800">Motoristas Disponíveis no <?php echo $nome_bairro_exibicao; ?></h2>
      <div class="space-y-6">
        <?php if (!empty($vans)): ?>
            <?php foreach ($vans as $index => $van): ?>
                <?php 
                    $celular_limpo = preg_replace('/\D/', '', $van['celular'] ?: $van['telefone_fixo'] ?: '');
                    $msg_whatsapp = urlencode("Olá! Vi seu número no site Van Escolar Paraná. Gostaria de saber mais sobre o transporte escolar no " . $nome_bairro_exibicao . ".");
                    $whatsapp_link = "https://wa.me/55" . $celular_limpo . "?text=" . $msg_whatsapp;
                    $is_premium = (isset($van['is_premium_active']) && $van['is_premium_active'] == 1);
                ?>
                <div class="van-card <?php echo $is_premium ? 'ring-4 ring-amber-400 border-amber-200 bg-amber-50/50 shadow-xl shadow-amber-100' : 'bg-white border-slate-100 shadow-sm'; ?> p-6 md:p-8 rounded-[2rem] md:rounded-[2.5rem] hover:shadow-md transition-all flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
                    <?php if ($is_premium): ?>
                        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-200/20 rounded-full -mr-16 -mt-16 blur-3xl"></div>
                    <?php endif; ?>
                    
                    <div class="space-y-4 flex-1 relative z-10">
                        <?php if ($is_premium): ?>
                            <div class="flex items-center">
                                <span class="bg-gradient-to-r from-amber-400 to-amber-600 text-white text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest shadow-lg shadow-amber-200 flex items-center gap-2">
                                    <span><i class="fa-solid fa-star"></i></span> Motorista em Destaque
                                </span>
                            </div>
                        <?php endif; ?>
                        <h3 class="text-xl md:text-2xl font-black text-slate-900 leading-tight"><?php echo htmlspecialchars($van['permissionario']); ?></h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-4 text-sm font-medium text-slate-500">
                            <p><strong>Van Ano:</strong> <?php echo htmlspecialchars($van['ano'] ?: '---'); ?></p>
                            <p><strong>Placa:</strong> <?php echo htmlspecialchars($van['modelo_placa'] ?: '---'); ?></p>
                            <?php if ($van['prefixo']): ?>
                                <p><strong>Prefixo:</strong> <?php echo htmlspecialchars($van['prefixo']); ?></p>
                            <?php endif; ?>
                            <?php if ($van['email'] && $van['email'] != '---'): ?>
                                <p><strong>E-mail:</strong> <?php echo htmlspecialchars($van['email']); ?></p>
                            <?php endif; ?>
                            <?php if ($van['telefone_fixo'] && $van['telefone_fixo'] != '---'): ?>
                                <p><strong>Fixo:</strong> <?php echo htmlspecialchars($van['telefone_fixo']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="<?php echo $whatsapp_link; ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp w-full md:w-auto flex items-center justify-center gap-3 px-8 py-5 rounded-2xl font-black text-lg transition-all active:scale-95 shadow-lg">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.399-4.397 9.889-9.845 9.897m8.39-18.28a11.83 11.83 0 00-8.38-3.475C5.462.037.05 5.449.05 12.1c0 2.126.556 4.197 1.612 6.007L.032 23.963l6.095-1.599c1.745.952 3.714 1.455 5.712 1.456h.005c6.64 0 12.052-5.412 12.052-12.064 0-3.235-1.261-6.276-3.551-8.566z"/></svg>
                        <span>WhatsApp agora</span>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bg-slate-50 p-16 rounded-[3rem] text-center border-4 border-dashed border-slate-200 group hover:border-blue-200 transition-all duration-500">
                <div class="w-24 h-24 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-5xl mx-auto mb-8 animate-bounce"><i class="fa-solid fa-bus"></i></div>
                <h3 class="text-3xl font-black text-slate-800 mb-4">Nenhum motorista disponível em <?php echo $neighborhood['title']; ?></h3>
                <p class="text-slate-500 font-medium text-xl mb-10 max-w-xl mx-auto">Ainda não temos transportadores cadastrados para esta região específica. Seja o pioneiro e domine as buscas aqui!</p>
                <a href="/destaque-sua-van/" class="inline-flex items-center gap-4 px-12 py-6 bg-blue-600 text-white rounded-full font-black text-2xl shadow-2xl shadow-blue-200 hover:bg-blue-700 hover:scale-105 active:scale-95 transition-all">
                    <span>QUERO SER O PRIMEIRO</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"></path></svg>
                </a>
            </div>
<?php endif; ?>

        <!-- Publicidade Estratégica para Motoristas -->
        <div class="mt-20 bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 rounded-[2.5rem] md:rounded-[3rem] p-8 md:p-12 text-white relative overflow-hidden shadow-[0_30px_60px_-15px_rgba(30,58,138,0.5)] group">
            <!-- Efeito de Luz -->
            <div class="absolute top-0 right-0 w-96 h-96 bg-blue-500/20 rounded-full -mr-32 -mt-32 blur-[100px] group-hover:bg-blue-400/30 transition-all duration-700"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-indigo-500/10 rounded-full -ml-32 -mb-32 blur-[80px]"></div>
            
            <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-8 text-center lg:text-left">
                <div class="max-w-2xl">
                    <span class="inline-block px-4 py-1.5 bg-blue-500/20 backdrop-blur-md rounded-full text-[10px] font-black uppercase tracking-widest text-blue-300 mb-6 border border-blue-500/30">Área do Transportador</span>
                    <h2 class="text-2xl md:text-4xl lg:text-5xl font-black mb-4 leading-tight">Você é motorista no <span class="text-blue-400"><?php echo $nome_bairro_exibicao; ?></span>?</h2>
                    <p class="text-lg md:text-xl text-blue-100/80 font-medium leading-relaxed">Não perca mais clientes por não ser visto. Leve sua van para o topo e multiplique seus contatos.</p>
                </div>
                <div class="flex flex-col items-center gap-4 w-full lg:w-auto">
                    <a href="/destaque-sua-van/" class="w-full lg:w-auto px-10 py-5 bg-white text-blue-900 rounded-full font-black text-xl md:text-2xl shadow-xl hover:bg-blue-50 hover:scale-105 active:scale-95 transition-all whitespace-nowrap">
                        Aparecer no Topo <i class="fa-solid fa-star"></i>
                    </a>
                    <p class="text-blue-300 text-xs font-bold md:opacity-0 md:group-hover:opacity-100 transition-opacity">Planos a partir de R$ 20,00</p>
                </div>
            </div>
        </div>
      </div>
    </section>

    <!-- Aviso de Segurança Super Claro -->
    <section class="bg-yellow-50 border-2 border-yellow-200 rounded-3xl p-6 md:p-8 mb-12 flex flex-col sm:flex-row items-center gap-4 md:gap-6">
      <div class="text-4xl md:text-5xl"><i class="fa-solid fa-triangle-exclamation text-yellow-600"></i></div>
      <div>
        <h3 class="text-lg md:text-xl font-bold text-yellow-900 mb-1">Dica importante de segurança:</h3>
        <p class="text-yellow-800 text-sm md:text-base leading-relaxed font-medium">
          Antes de fechar qualquer contrato, peça para ver o <strong>alvará da URBS ou Prefeitura</strong>. No site oficial da prefeitura de <?php echo $cidade_nome_limpo; ?> você confere se a van está em dia.
        </p>
      </div>
    </section>

    <article class="bg-white rounded-3xl border border-gray-200 p-8 shadow-sm seo-content">
      <?php echo $neighborhood['content']; ?>
    </article>

    <section id="neighbor-nav-section" class="mt-16 text-center">
      <h4 class="text-lg font-bold mb-4 text-gray-500">Mora em bairros próximos? Veja também:</h4>
      <?php echo $neighborhood['neighbor_links']; ?>
    </section>

  </main>

  <footer class="bg-gray-100 py-12 border-t border-gray-200 text-center">
    <p class="font-bold text-gray-700 mb-2">Van Escolar Paraná</p>
    <p class="text-gray-500 text-sm">Ajudando famílias do Paraná desde 2025.</p>
    <div class="mt-8 text-xs text-gray-400">© <span id="current-year"><?php echo date('Y'); ?></span> Todos os direitos reservados.</div>
  </footer>

  
  
  <script>
    // Script adicional se necessário
  </script>


</body>
</html>


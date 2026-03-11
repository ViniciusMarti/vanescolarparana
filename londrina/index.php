<?php
require_once __DIR__ . '/../config/db.php';

// Carrega os dados SEO dos bairros
$neighborhoods_json = file_get_contents(__DIR__ . '/neighborhood_data.json');
$neighborhoods_data = json_decode($neighborhoods_json, true);

$cidade_nome = "Londrina";
$cidade_slug = "londrina";

// Tenta buscar no banco (provavelmente vazio ainda)
try {
    $table_name = 'vans';
    $stmt = $pdo->query("SELECT COUNT(*) FROM `$table_name` WHERE `bairro_referencia` LIKE '%$cidade_nome%'");
    $total_vans = $stmt->fetchColumn();
} catch (PDOException $e) {
    $total_vans = 0;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta content="noai, noimageai" name="robots" />
  <script async="" src="https://www.googletagmanager.com/gtag/js?id=G-ETL54HBEXL"></script>
  <script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'G-ETL54HBEXL');</script>
  <meta charset="utf-8" />
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <title>Vans Escolares em <?php echo $cidade_nome; ?> - Encontre por Bairro | Van Escolar Paraná</title>
  <meta content="Lista completa de transportadores escolares em <?php echo $cidade_nome; ?> organizada por bairros. Escolha sua região e fale direto com o motorista pelo WhatsApp." name="description" />
  <meta content="#2563eb" name="theme-color" />
  <link href="/icone-favicon.png" rel="icon" type="image/png" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #1e293b; font-size: 18px; }
    .glass-nav { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0, 0, 0, 0.05); }
    .neighborhood-card { background: white; border: 1px solid #e2e8f0; transition: all 0.2s; }
    .neighborhood-card:hover { border-color: #2563eb; transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.1); }
    .hero-gradient { background-color: #2563eb; background-image: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); }
    .featured-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .featured-card:hover { transform: scale(1.02); }
  </style>
  <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-WKBVRTDG');</script>
</head>
<body>
  <noscript><iframe height="0" src="https://www.googletagmanager.com/ns.html?id=GTM-WKBVRTDG" style="display:none;visibility:hidden" width="0"></iframe></noscript>
  
    <header class="glass-nav sticky top-0 z-[100] transition-all duration-300">
    <nav class="container mx-auto flex h-20 items-center justify-between px-6 lg:px-12">
      <a class="flex items-center gap-4 group" href="/"><img alt="Van Escolar Paraná" class="h-10 md:h-12 w-auto transition-transform duration-300 group-hover:scale-105" src="/logo-comum.png" /></a>
      
      <!-- Desktop Menu -->
      <div class="hidden md:flex items-center gap-8 text-sm md:text-base font-semibold">
        <a class="text-slate-600 hover:text-blue-700 transition-colors" href="/sobre/">Sobre</a>
        <a class="text-slate-600 hover:text-blue-700 transition-colors" href="/informativos/">Informativos</a>
        <a class="px-6 py-2.5 bg-blue-600 text-white rounded-full font-bold shadow-sm hover:shadow-lg transition-all active:scale-95" href="/destaque-sua-van/">Destaque sua Van</a>
      </div>

      <!-- Mobile Menu Button -->
      <button onclick="toggleMobileMenu()" class="md:hidden p-2 text-slate-900 focus:outline-none rounded-xl hover:bg-slate-100 transition-all">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path></svg>
      </button>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div id="mobile-menu" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-[150] opacity-0 pointer-events-none transition-all duration-300 md:hidden">
        <div id="mobile-menu-content" class="absolute top-0 right-0 w-80 h-full bg-white shadow-2xl translate-x-full transition-transform duration-300 flex flex-col p-8">
            <button onclick="toggleMobileMenu()" class="self-end p-2 text-slate-500 hover:text-slate-900 mb-8">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
            </button>
            <nav class="flex flex-col gap-6 text-left">
                <a href="/" class="text-2xl font-black text-slate-800">Início</a>
                <a href="/sobre/" class="text-2xl font-black text-slate-800">Sobre</a>
                <a href="/informativos/" class="text-2xl font-black text-slate-800">Informativos</a>
                <div class="h-px bg-slate-100 my-4"></div>
                <a href="/destaque-sua-van/" class="bg-blue-600 text-white p-6 rounded-3xl text-center font-black text-xl shadow-xl shadow-blue-100 italic">⭐ Destaque sua Van</a>
                <p class="text-slate-400 text-center text-sm mt-auto">Van Escolar Paraná © 2025</p>
            </nav>
        </div>
    </div>
  </header>

  <script>
    function toggleMobileMenu() {
        const menu = document.getElementById("mobile-menu");
        const content = document.getElementById("mobile-menu-content");
        const isOpening = menu.classList.contains("opacity-0");
        
        if(isOpening) {
            menu.classList.replace("opacity-0", "opacity-100");
            menu.classList.remove("pointer-events-none");
            content.classList.replace("translate-x-full", "translate-x-0");
            document.body.style.overflow = "hidden";
        } else {
            menu.classList.replace("opacity-100", "opacity-0");
            menu.classList.add("pointer-events-none");
            content.classList.replace("translate-x-0", "translate-x-full");
            document.body.style.overflow = "";
        }
    }
  </script>

  <main>
    <!-- Hero Section -->
    <section class="hero-gradient text-white pt-24 pb-24 relative overflow-hidden">
      <div class="container mx-auto px-6 lg:px-12 text-center relative z-10">
        <h1 class="text-4xl md:text-5xl lg:text-7xl font-black leading-tight mb-6 leading-tight">Vans Escolares em <?php echo $cidade_nome; ?></h1>
        <p class="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto font-medium leading-relaxed">Localize o transporte escolar ideal para seus filhos com segurança e rapidez. Navegue por <?php echo count($neighborhoods_data); ?> bairros e conecte-se com motoristas regulamentados.</p>
      </div>
      <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full -mr-48 -mt-48 blur-3xl"></div>
    </section>

    <!-- Bairros em Destaque -->
    <section class="py-20 bg-slate-50 border-b border-slate-200">
      <div class="container mx-auto px-6 lg:px-12">
        <div class="flex items-center gap-4 mb-12">
            <span class="text-4xl text-blue-600">⭐</span>
            <h2 class="text-4xl font-black text-slate-800 tracking-tight">Bairros mais buscados</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
          <!-- Gleba Palhano -->
          <a href="/londrina/gleba-palhano/" class="featured-card group relative block overflow-hidden rounded-[2.5rem] shadow-xl h-[30rem] bg-slate-200">
            <img src="/images/bairros/londrina-gleba-palhano.png" alt="Gleba Palhano" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/40 to-transparent"></div>
            <div class="absolute bottom-0 p-10">
              <h3 class="text-3xl font-black text-white mb-4">Gleba Palhano</h3>
              <p class="text-gray-200 text-lg font-medium leading-relaxed opacity-90">A região mais moderna de Londrina, com infraestrutura completa e proximidade aos melhores colégios.</p>
            </div>
          </a>
          <!-- Centro -->
          <a href="/londrina/centro/" class="featured-card group relative block overflow-hidden rounded-[2.5rem] shadow-xl h-[30rem] bg-slate-200">
            <img src="/images/bairros/londrina-centro.png" alt="Centro" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/40 to-transparent"></div>
            <div class="absolute bottom-0 p-10">
              <h3 class="text-3xl font-black text-white mb-4">Centro</h3>
              <p class="text-gray-200 text-lg font-medium leading-relaxed opacity-90">O coração da cidade, conectando diversas rotas de transporte com praticidade e agilidade.</p>
            </div>
          </a>
          <!-- Higienópolis -->
          <a href="/londrina/higienopolis/" class="featured-card group relative block overflow-hidden rounded-[2.5rem] shadow-xl h-[30rem] bg-slate-200">
            <img src="/images/bairros/londrina-higienopolis.png" alt="Higienópolis" class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-110" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/40 to-transparent"></div>
            <div class="absolute bottom-0 p-10">
              <h3 class="text-3xl font-black text-white mb-4">Higienópolis</h3>
              <p class="text-gray-200 text-lg font-medium leading-relaxed opacity-90">Bairro tradicional e arborizado, com vasta oferta de transporte escolar especializado.</p>
            </div>
          </a>
        </div>
      </div>
    </section>

    <!-- Lista Completa -->
    <section class="py-24 bg-white">
      <div class="container mx-auto px-6 lg:px-12">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-16 pb-10 border-b-2 border-slate-50 gap-6">
          <h2 class="text-4xl font-black text-slate-800 tracking-tight">Todos os Bairros Atendidos</h2>
          <div class="flex items-center gap-3 bg-blue-50 text-blue-700 px-8 py-4 rounded-full font-bold text-base">
            <span>🔍</span>
            Organizado em ordem alfabética
          </div>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 lg:gap-8">
          <?php 
          ksort($neighborhoods_data);
          foreach ($neighborhoods_data as $slug => $data): 
             $display_name = ucwords(str_replace('-', ' ', $slug));
          ?>
            <a class="neighborhood-card p-6 rounded-3xl text-center font-bold text-slate-700 hover:text-blue-700 hover:shadow-lg transition-all active:scale-95" href="/<?php echo $cidade_slug; ?>/<?php echo $slug; ?>/"><?php echo $display_name; ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  
    <!-- Área do Motorista (Incentivo) -->
    <section class="py-20 bg-slate-100/50">
      <div class="container mx-auto px-6 lg:px-12">
        <div class="bg-gradient-to-r from-slate-800 to-blue-900 rounded-[3rem] p-12 text-white relative overflow-hidden shadow-2xl flex flex-col md:flex-row items-center justify-between gap-12 group">
          <div class="relative z-10 text-center md:text-left">
            <h2 class="text-3xl font-black mb-4 tracking-tight leading-tight">Você é motorista em <?php echo $cidade_nome; ?>?</h2>
            <p class="text-blue-100 text-lg font-medium max-w-xl">Receba mais contatos de pais no seu bairro todos os dias. Apareça no topo das buscas!</p>
          </div>
          <div class="relative z-10">
            <a href="/destaque-sua-van/" class="inline-block px-10 py-5 bg-white text-blue-900 rounded-full font-black text-xl shadow-xl hover:bg-blue-50 transition-all active:scale-95 whitespace-nowrap">
              Destaque seu Negócio 🚛
            </a>
          </div>
          <div class="absolute -top-24 -right-24 w-80 h-80 bg-white/5 rounded-full blur-3xl group-hover:bg-white/10 transition-all duration-700"></div>
        </div>
      </div>
    </section>
</main>

  <footer class="bg-white py-12 border-t border-slate-200 text-center">
    <div class="container mx-auto px-6 lg:px-12">
      <img alt="Van Escolar Paraná" class="h-8 mx-auto mb-6 opacity-60 grayscale" src="/logo-comum.png" />
      <p class="font-bold text-gray-700 mb-2">Van Escolar Paraná</p>
      <p class="text-gray-500 text-sm">Conectando pais e transportadores em todo o Paraná.</p>
      <div class="mt-8 text-xs text-gray-400">© <?php echo date('Y'); ?> Todos os direitos reservados.</div>
    </div>
  </footer>

  <!-- Botão Flutuante Espaço do Motorista -->
  <a href="/destaque-sua-van/" class="fixed bottom-8 right-8 z-[200] group">
    <div class="bg-blue-600 text-white flex items-center gap-4 px-8 py-5 rounded-full shadow-2xl hover:scale-110 active:scale-95 transition-all duration-300 relative overflow-hidden">
      <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700 pointer-events-none"></div>
      <span class="text-2xl">⭐</span>
      <span class="font-black text-sm uppercase tracking-widest hidden lg:inline">É Motorista? Apareça no Topo!</span>
      <span class="font-black text-xs uppercase tracking-widest lg:hidden">Apareça no Topo 🚐</span>
    </div>
  </a>
</body>
</html>

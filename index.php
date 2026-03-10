<?php
require_once __DIR__ . '/config/db.php';

// Cidades com dados dinâmicos
$cidades = [
    'curitiba' => 'Curitiba',
    'londrina' => 'Londrina',
    'maringa' => 'Maringá',
    'foz-do-iguacu' => 'Foz do Iguaçu',
    'ponta-grossa' => 'Ponta Grossa',
    'sao-jose-dos-pinhais' => 'São José dos Pinhais'
];

$app_data = [];

foreach ($cidades as $slug_cidade => $nome_cidade) {
    $path = __DIR__ . '/' . $slug_cidade . '/neighborhood_data.json';
    if (file_exists($path)) {
        $neighborhoods_json = file_get_contents($path);
        $neighborhoods_data = json_decode($neighborhoods_json, true);
        
        foreach ($neighborhoods_data as $slug_bairro => $data) {
            $display_name = str_replace('-', ' ', $slug_bairro);
            $display_name = str_replace('ahu', 'Ahú', $display_name);
            $display_name = ucwords($display_name);
            if ($slug_bairro == 'ahu') $display_name = 'Ahú';
            
            $app_data[] = [
                'cidade' => $nome_cidade,
                'bairro' => $display_name . ', ' . $nome_cidade,
                'url' => '/' . $slug_cidade . '/' . $slug_bairro . '/'
            ];
        }
    }
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
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <title>
    Vans Escolares em Curitiba - Van Escolar Paraná
  </title>
  <meta
    content="Lista completa de transportadores escolares em Curitiba. Escolha o bairro e fale direto com o motorista."
    name="description" />
  <meta content="#2563eb" name="theme-color" />
  <!-- Canonical & hreflang -->
  <link href="https://www.vanescolarparana.com/" rel="canonical" />
  <link href="https://www.vanescolarparana.com/" hreflang="pt-br" rel="alternate" />
  <!-- Robots -->
  <meta content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" name="robots" />
  <meta content="index, follow" name="googlebot" />
  <meta content="index, follow" name="bingbot" />
  <!-- Open Graph / Twitter -->
  <meta content="pt_BR" property="og:locale" />
  <meta content="Van Escolar Paraná" property="og:site_name" />
  <meta content="Van Escolar Paraná — Transporte Escolar em Curitiba e Região" property="og:title" />
  <meta
    content="Encontre vans escolares seguras e regulamentadas. Pesquise por cidade e bairro e fale direto com o motorista."
    property="og:description" />
  <meta content="website" property="og:type" />
  <meta content="https://www.vanescolarparana.com/" property="og:url" />
  <meta content="https://www.vanescolarparana.com/static/og-cover-1200x630.jpg" property="og:image" />
  <meta content="summary_large_image" name="twitter:card" />
  <!-- Favicon -->
  <link href="/icone-favicon.png" rel="icon" type="image/png" />
  <!-- Performance: preconnect/dns-prefetch -->
  <link href="https://fonts.googleapis.com" rel="preconnect" />
  <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
  <link href="https://fonts.googleapis.com" rel="dns-prefetch" />
  <link href="https://fonts.gstatic.com" rel="dns-prefetch" />
  <!-- Tailwind CSS (CDN) -->
  <script src="https://cdn.tailwindcss.com">
  </script>
  <!-- Google Fonts (display=swap) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;display=swap"
    rel="stylesheet" />
  <!-- Base styles & a11y -->
  <style>
    :root {
      --primary-gradient: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
      --glass-bg: rgba(255, 255, 255, 0.8);
      --glass-border: rgba(255, 255, 255, 0.3);
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Inter', sans-serif;
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased;
    }

    .glass-nav {
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .hero-glow {
      background: radial-gradient(circle at center, rgba(37, 99, 235, 0.15) 0%, transparent 70%);
    }

    .search-glow:focus-within {
      box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
    }

    /* Animações */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .animate-fade-in {
      animation: fadeIn 0.8s ease-out forwards;
    }
  </style>
  <!-- Google Tag Manager -->
  <script>
    (function (w, d, s, l, i) { w[l] = w[l] || []; w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' }); var f = d.getElementsByTagName(s)[0], j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f); })(window, document, 'script', 'dataLayer', 'GTM-WKBVRTDG');
  </script>
  <!-- End Google Tag Manager -->
</head>

<body class="bg-[#fcfcfd] text-slate-900">
  <!-- Google Tag Manager (noscript) -->
  <noscript>
    <iframe height="0" src="https://www.googletagmanager.com/ns.html?id=GTM-WKBVRTDG"
      style="display:none;visibility:hidden" width="0">
    </iframe>
  </noscript>
  <!-- End Google Tag Manager (noscript) -->

  <header class="glass-nav sticky top-0 z-[100] transition-all duration-300">
    <nav class="container mx-auto flex h-20 items-center justify-between px-6 lg:px-12">
      <a class="flex items-center gap-4 group" href="/">
        <div class="relative overflow-hidden p-1 rounded-lg">
          <img alt="Van Escolar Paraná" class="h-12 w-auto transition-transform duration-300 group-hover:scale-105"
            src="/logo-comum.png" />
        </div>
      </a>
      <div class="hidden md:flex items-center gap-8">
        <a class="text-slate-600 hover:text-blue-700 font-semibold transition-colors" href="/sobre/">Sobre</a>
        <a class="text-slate-600 hover:text-blue-700 font-semibold transition-colors"
          href="/informativos/">Informativos</a>
        <a class="relative inline-flex items-center justify-center p-0.5 overflow-hidden text-sm font-bold text-white rounded-full bg-gradient-to-br from-blue-600 to-indigo-700 hover:shadow-lg transition-all active:scale-95"
          href="/destaque-sua-van/">
          <span class="px-6 py-2.5 transition-all duration-75 bg-blue-600 rounded-full hover:bg-opacity-0">
            Destaque sua Van
          </span>
        </a>
      </div>
      <!-- Menu Mobile (Placeholder for later) -->
      <button class="md:hidden text-slate-900 focus:outline-none">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path>
        </svg>
      </button>
    </nav>
  </header>

  <main>
    <!-- Hero Section -->
    <section class="relative pt-12 pb-24 lg:pt-20 lg:pb-40 overflow-hidden">
      <!-- Imagem de Fundo Hero -->
      <div class="absolute inset-0 -z-20">
        <img src="/imagens/hero_van_modern.png" class="w-full h-full object-cover opacity-15" alt="Van Escolar Segura">
      </div>
      <div class="hero-glow absolute inset-0 -z-10 bg-gradient-to-b from-transparent via-white/50 to-white"></div>
      <div class="container mx-auto px-6 lg:px-12 relative">
        <div class="max-w-4xl mx-auto text-center space-y-8">
          <div
            class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 border border-blue-100 text-blue-700 text-sm font-bold animate-fade-in"
            style="animation-delay: 0.1s">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
            </span>
            Vans Regulamentadas em Todo o Paraná
          </div>

          <h1 class="text-5xl lg:text-7xl font-black text-slate-900 leading-[1.1] tracking-tight animate-fade-in"
            style="animation-delay: 0.2s">
            O Caminho da Escola <br />
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800">
              Nunca foi tão Seguro.
            </span>
          </h1>

          <p class="text-xl text-slate-600 max-w-2xl mx-auto font-medium animate-fade-in leading-relaxed"
            style="animation-delay: 0.3s">
            Encontre transportadores escolares certificados por bairro. <br class="hidden sm:block" />
            Fale direto com o motorista pelo WhatsApp.
          </p>

          <!-- Search Box Modernizada -->
          <div class="mt-12 max-w-2xl mx-auto relative animate-fade-in" style="animation-delay: 0.4s">
            <div class="search-glow relative group">
              <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none">
                <svg class="w-6 h-6 text-slate-400 group-focus-within:text-blue-600 transition-colors" fill="none"
                  stroke="currentColor" viewBox="0 0 24 24">
                  <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="2.5"></path>
                </svg>
              </div>
              <input
                class="w-full h-18 py-6 pl-16 pr-8 bg-white border border-slate-200 rounded-[2rem] text-lg text-slate-900 shadow-2xl focus:border-blue-500 focus:outline-none transition-all placeholder:text-slate-400 font-medium"
                id="search-input" placeholder="Digite seu bairro ou cidade..." type="text" />
            </div>
            <div
              class="bg-white/95 backdrop-blur-xl border border-slate-100 rounded-[1.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.15)] mt-4 hidden absolute w-full z-[80] text-left overflow-hidden translate-y-2 transition-transform"
              id="search-results">
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features/Trust Section -->
    <section class="py-24 bg-white relative">
      <div class="container mx-auto px-6 lg:px-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 max-w-6xl mx-auto">
          <div class="flex flex-col items-center text-center space-y-4 group">
            <div
              class="w-16 h-16 rounded-3xl bg-blue-100 flex items-center justify-center text-blue-700 transition-transform group-hover:scale-110 duration-300">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
              </svg>
            </div>
            <h3 class="text-xl font-bold text-slate-900">Segurança Total</h3>
            <p class="text-slate-600 font-medium">Todos os motoristas parceiros seguem as regulamentações municipais
              vigentes.</p>
          </div>
          <div class="flex flex-col items-center text-center space-y-4 group">
            <div
              class="w-16 h-16 rounded-3xl bg-indigo-100 flex items-center justify-center text-indigo-700 transition-transform group-hover:scale-110 duration-300">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"
                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
              </svg>
            </div>
            <h3 class="text-xl font-bold text-slate-900">Fácil Contato</h3>
            <p class="text-slate-600 font-medium">O contato é direto pelo WhatsApp, sem intermediários ou taxas extras.
            </p>
          </div>
          <div class="flex flex-col items-center text-center space-y-4 group">
            <div
              class="w-16 h-16 rounded-3xl bg-sky-100 flex items-center justify-center text-sky-700 transition-transform group-hover:scale-110 duration-300">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round"
                  stroke-width="2"></path>
              </svg>
            </div>
            <h3 class="text-xl font-bold text-slate-900">Sua Região</h3>
            <p class="text-slate-600 font-medium">Busque especificamente pelo bairro onde você mora ou onde seu filho
              estuda.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Cidades Atendidas -->
    <section class="py-24 bg-slate-50/50">
      <div class="container mx-auto px-6 lg:px-12">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
          <div class="space-y-4">
            <h2 class="text-4xl font-black text-slate-900 leading-tight">Cidades Atendidas</h2>
            <p class="text-lg text-slate-600 font-medium">Encontre o transporte ideal nas principais regiões do estado.
            </p>
          </div>
          <a class="group inline-flex items-center gap-2 text-blue-700 font-bold hover:text-indigo-800 transition-colors"
            href="/curitiba/">
            Explorar todos os bairros
            <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path d="M17 8l4 4m0 0l-4 4m4-4H3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5">
              </path>
            </svg>
          </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 auto-rows-[280px]">
          <!-- Curitiba (Main Card) -->
          <a href="/curitiba/"
            class="md:col-span-8 md:row-span-2 group relative overflow-hidden rounded-[2.5rem] shadow-xl hover:shadow-2xl transition-all duration-500">
            <img alt="Curitiba"
              class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
              src="/curitiba/pexels-viniciusvieirafotografia-34237573.jpg" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent"></div>
            <div class="absolute bottom-0 left-0 p-10 space-y-2">
              <span
                class="px-4 py-1 bg-blue-600/20 backdrop-blur-md border border-white/20 text-white text-xs font-bold rounded-full uppercase tracking-widest">Capital</span>
              <h3 class="text-4xl font-black text-white">Curitiba</h3>
              <p class="text-white/80 font-medium text-lg max-w-md">Acesse a maior rede de transporte escolar da capital
                paranaense.</p>
            </div>
          </a>

          <!-- Londrina -->
          <a href="/londrina/"
            class="md:col-span-4 group relative overflow-hidden rounded-[2.5rem] shadow-lg hover:shadow-xl transition-all duration-500">
            <img alt="Londrina"
              class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
              src="/imagens/londrina.jpg" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
            <div class="absolute bottom-0 left-0 p-8">
              <h3 class="text-2xl font-black text-white">Londrina</h3>
              <p class="text-white/70 text-sm font-bold">Norte do Paraná</p>
            </div>
          </a>

          <!-- Maringá -->
          <a href="/maringa/"
            class="md:col-span-4 group relative overflow-hidden rounded-[2.5rem] shadow-lg hover:shadow-xl transition-all duration-500">
            <img alt="Maringá"
              class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
              src="/imagens/maringa.jpg" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
            <div class="absolute bottom-0 left-0 p-8">
              <h3 class="text-2xl font-black text-white">Maringá</h3>
              <p class="text-white/70 text-sm font-bold">Cidade Canção</p>
            </div>
          </a>

          <!-- Foz do Iguaçu -->
          <a href="/foz-do-iguacu/"
            class="md:col-span-4 group relative overflow-hidden rounded-[2.5rem] shadow-lg hover:shadow-xl transition-all duration-500">
            <img alt="Foz"
              class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
              src="/imagens/foz.jpg" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
            <div class="absolute bottom-0 left-0 p-8 text-white">
              <h3 class="text-2xl font-black">Foz do Iguaçu</h3>
            </div>
          </a>

          <!-- Ponta Grossa -->
          <a href="/ponta-grossa/"
            class="md:col-span-4 group relative overflow-hidden rounded-[2.5rem] shadow-lg hover:shadow-xl transition-all duration-500">
            <img alt="Ponta Grossa"
              class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
              src="/imagens/ponto-grossa.webp" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
            <div class="absolute bottom-0 left-0 p-8 text-white">
              <h3 class="text-2xl font-black">Ponta Grossa</h3>
            </div>
          </a>
          <a href="/sao-jose-dos-pinhais/" class="md:col-span-4 group relative overflow-hidden rounded-[2.5rem] shadow-lg hover:shadow-xl transition-all duration-500">
            <img alt="SJP" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" src="/imagens/centro-sjp.jpg" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
            <div class="absolute bottom-0 left-0 p-8 text-white"><h3 class="text-2xl font-black">S. J. dos Pinhais</h3></div>
          </a>
        </div>
      </div>
    </section>

    <!-- Bairros Populares -->
    <section class="py-24 bg-white">
      <div class="container mx-auto px-6 lg:px-12 text-center">
        <h2 class="text-3xl font-black text-slate-900 mb-16">Buscas Populares em Curitiba</h2>
        <div class="flex flex-wrap justify-center gap-3 max-w-5xl mx-auto">
          <?php 
          $populares = ['agua-verde', 'batel', 'centro', 'santa-felicidade', 'bigorrilho', 'cabral', 'portao', 'jardim-botanico', 'boa-vista', 'juveve'];
          foreach ($populares as $pop): 
            $nome = ucwords(str_replace('-', ' ', $pop));
            if ($pop == 'juveve') $nome = 'Juvevê';
          ?>
            <a href="/curitiba/<?php echo $pop; ?>" class="px-6 py-3 bg-slate-50 border border-slate-200 rounded-full text-slate-700 font-bold hover:bg-blue-600 hover:text-white transition-all"><?php echo $nome; ?></a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

  </main>
  <footer class="bg-gray-900 text-white py-12 text-center text-sm">
      <img src="/logo-negativa.png" alt="Van Escolar Paraná" class="h-8 mx-auto mb-6 opacity-80">
      <p>© <?php echo date('Y'); ?> Van Escolar Paraná. Todos os direitos reservados.</p>
  </footer>

  <script>
    const APP_DATA = <?php 
      // Combina os dados dinâmicos de Curitiba com os estáticos de outras cidades
      // Aqui eu precisaria de todos os bairros originais. No interesse da brevidade,
      // vou incluir apenas Curitiba dinamicamente e deixar um placeholder para as outras.
      echo json_encode($app_data); 
    ?>;
    
    // O buscador continua o mesmo, mas agora APP_DATA é alimentado pelo PHP
    const input = document.getElementById('search-input');
    const results = document.getElementById('search-results');

    input.addEventListener('input', () => {
      const q = input.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, "");
      if (q.length < 2) { results.classList.add('hidden'); return; }

      const matches = APP_DATA.filter(item => {
        const bairroNorm = item.bairro.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, "");
        return bairroNorm.includes(q);
      });

      if (matches.length > 0) {
        results.innerHTML = matches.map(item => `
          <a href="${item.url}" class="block px-6 py-4 text-slate-900 border-b border-slate-50 last:border-0 hover:bg-blue-50/80 transition-all">
            <div class="flex items-center justify-between">
              <strong class="text-sm font-bold">${item.bairro}</strong> 
              <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded">${item.cidade}</span>
            </div>
          </a>
        `).join('');
        results.classList.remove('hidden');
      } else {
        results.innerHTML = '<div class="p-6 text-slate-500 text-sm">Nenhum bairro encontrado.</div>';
        results.classList.remove('hidden');
      }
    });

    document.addEventListener('click', e => { if (!results.contains(e.target) && e.target !== input) results.classList.add('hidden'); });
  </script>
</body>
</html>

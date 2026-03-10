<?php
require_once __DIR__ . '/../config/db.php';

// Carrega os dados SEO dos bairros
$neighborhoods_json = file_get_contents(__DIR__ . '/neighborhood_data.json');
$neighborhoods_data = json_decode($neighborhoods_json, true);

$cidade_nome = "São José dos Pinhais";
$cidade_slug = "sao-jose-dos-pinhais";

try {
    $table_name = 'vans';
    $stmt = $pdo->query("SELECT COUNT(*) FROM `$table_name` WHERE `bairro_referencia` LIKE '%$cidade_nome%'");
    $total_vans = $stmt->fetchColumn();
} catch (PDOException $e) { $total_vans = 0; }
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
  <header class="glass-nav sticky top-0 z-[100]">
    <nav class="container mx-auto flex h-20 items-center justify-between px-6 lg:px-12">
      <a class="flex items-center gap-4 group" href="/"><img alt="Van Escolar Paraná" class="h-12 w-auto transition-transform duration-300 group-hover:scale-105" src="/logo-comum.png" /></a>
      <div class="hidden md:flex items-center gap-8 text-sm md:text-base">
        <a class="text-slate-600 hover:text-blue-700 font-semibold transition-colors" href="/sobre/">Sobre</a>
        <a class="text-slate-600 hover:text-blue-700 font-semibold transition-colors" href="/informativos/">Informativos</a>
        <a class="px-6 py-2.5 bg-blue-600 text-white rounded-full font-bold shadow-sm hover:shadow-lg transition-all active:scale-95" href="/destaque-sua-van/">Destaque sua Van</a>
      </div>
    </nav>
  </header>
  <main>
    <section class="hero-gradient text-white pt-24 pb-24 relative overflow-hidden">
      <div class="container mx-auto px-6 lg:px-12 text-center relative z-10">
        <h1 class="text-4xl md:text-6xl font-extrabold mb-6 leading-tight text-white">Vans Escolares em S. J. dos Pinhais</h1>
        <p class="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto font-medium leading-relaxed">Localize o transporte escolar ideal na Região Metropolitana. Segurança para seus filhos a um clique de distância.</p>
      </div>
      <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full -mr-48 -mt-48 blur-3xl"></div>
    </section>
    <section class="py-20 bg-slate-50 border-b border-slate-200">
      <div class="container mx-auto px-6 lg:px-12">
        <div class="flex items-center gap-4 mb-12">
            <span class="text-4xl text-blue-600">⭐</span>
            <h2 class="text-4xl font-black text-slate-800 tracking-tight">Bairros mais buscados</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
          <a href="/sao-jose-dos-pinhais/afonso-pena/" class="featured-card group relative block overflow-hidden rounded-[2.5rem] shadow-xl h-[30rem] bg-gradient-to-br from-blue-600 to-indigo-900">
            <div class="absolute inset-0 flex items-center justify-center opacity-10"><span class="text-[12rem] font-black text-white">AP</span></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/40 to-transparent"></div>
            <div class="absolute bottom-0 p-10"><h3 class="text-3xl font-black text-white mb-4">Afonso Pena</h3><p class="text-gray-200 text-lg font-medium leading-relaxed opacity-90">O principal polo industrial e residencial, com ampla cobertura de transporte escolar.</p></div>
          </a>
          <a href="/sao-jose-dos-pinhais/centro/" class="featured-card group relative block overflow-hidden rounded-[2.5rem] shadow-xl h-[30rem] bg-gradient-to-br from-slate-700 to-slate-900">
            <div class="absolute inset-0 flex items-center justify-center opacity-10"><span class="text-[12rem] font-black text-white">SJ</span></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/40 to-transparent"></div>
            <div class="absolute bottom-0 p-10"><h3 class="text-3xl font-black text-white mb-4">Centro</h3><p class="text-gray-200 text-lg font-medium leading-relaxed opacity-90">Facilidade de acesso e conexão entre os principais bairros da cidade.</p></div>
          </a>
          <a href="/sao-jose-dos-pinhais/cidade-jardim/" class="featured-card group relative block overflow-hidden rounded-[2.5rem] shadow-xl h-[30rem] bg-gradient-to-br from-blue-800 to-slate-900">
            <div class="absolute inset-0 flex items-center justify-center opacity-10"><span class="text-[12rem] font-black text-white">CJ</span></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/40 to-transparent"></div>
            <div class="absolute bottom-0 p-10"><h3 class="text-3xl font-black text-white mb-4">Cidade Jardim</h3><p class="text-gray-200 text-lg font-medium leading-relaxed opacity-90">Região valorizada e com forte presença de famílias que buscam transporte escolar de qualidade.</p></div>
          </a>
        </div>
      </div>
    </section>
    <section class="py-24 bg-white">
      <div class="container mx-auto px-6 lg:px-12">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-16 pb-10 border-b-2 border-slate-50 gap-6">
          <h2 class="text-4xl font-black text-slate-800 tracking-tight">Todos os Bairros Atendidos</h2>
          <div class="flex items-center gap-3 bg-blue-50 text-blue-700 px-8 py-4 rounded-full font-bold text-base"><span>🔍</span>Organizado em ordem alfabética</div>
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
  </main>
  <footer class="bg-white py-12 border-t border-slate-200 text-center">
    <div class="container mx-auto px-6 lg:px-12">
      <img alt="Van Escolar Paraná" class="h-8 mx-auto mb-6 opacity-60 grayscale" src="/logo-comum.png" />
      <p class="font-bold text-gray-700 mb-2">Van Escolar Paraná</p>
      <p class="text-gray-500 text-sm">Conectando pais e transportadores em todo o Paraná.</p>
      <div class="mt-8 text-xs text-gray-400">© <?php echo date('Y'); ?> Todos os direitos reservados.</div>
    </div>
  </footer>
</body>
</html>

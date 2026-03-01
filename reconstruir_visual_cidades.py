import os
import glob

# ==========================================
# 1. CONFIGURAÇÕES
# ==========================================
PASTA_RAIZ = os.getcwd()
CIDADES = ['curitiba', 'sao-jose-dos-pinhais']

def recuperar_index_com_design(nome_cidade):
    caminho_cidade = os.path.join(PASTA_RAIZ, nome_cidade)
    if not os.path.exists(caminho_cidade): return

    # 1. Lista os arquivos .html (bairros) ignorando o index
    arquivos = glob.glob(os.path.join(caminho_cidade, "*.html"))
    bairros = [os.path.basename(f).replace('.html', '') for f in arquivos if "index.html" not in f]
    bairros.sort()

    # 2. Gera o HTML dos Cards de Bairros
    cards_html = ""
    for b in bairros:
        nome_exibicao = b.replace('-', ' ').title()
        cards_html += f"""
        <a href="/{nome_cidade}/{b}.html" class="group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md transition-all text-center">
            <span class="text-gray-800 font-bold group-hover:text-blue-600">{nome_exibicao}</span>
        </a>"""

    # 3. Template Completo com o seu Header e Footer oficiais
    html_final = f"""<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vans Escolares em {nome_cidade.replace('-', ' ').title()} - Van Escolar Paraná</title>
    <meta name="description" content="Lista completa de transportadores escolares em {nome_cidade.replace('-', ' ').title()}. Escolha o bairro e fale direto com o motorista.">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {{ font-family: 'Inter', sans-serif; }}
    </style>

    <script>(function(w,d,s,l,i){{w[l]=w[l]||[];w[l].push({{'gtm.start':
    new Date().getTime(),event:'gtm.js'}});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    }})(window,document,'script','dataLayer','GTM-WKBVRTDG');</script>
</head>
<body class="bg-gray-50 text-gray-800">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WKBVRTDG" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

    <header class="bg-white/90 backdrop-blur border-b border-gray-100 sticky top-0 z-50">
        <nav class="container mx-auto flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="/" class="flex items-center gap-3">
                <img src="/logo-van-escolar-parana.png" alt="Van Escolar Paraná" class="h-10" />
            </a>
            <div class="flex items-center gap-4">
                <a href="/sobre/" class="hidden sm:inline text-gray-600 hover:text-blue-700 font-medium">Sobre</a>
                <a href="/destaque-sua-van/" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">Destaque sua Van</a>
            </div>
        </nav>
    </header>

    <main>
        <section class="bg-blue-600 text-white py-16 text-center">
            <div class="container mx-auto px-4">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-4">Vans em {nome_cidade.replace('-', ' ').title()}</h1>
                <p class="text-lg opacity-90">Selecione o bairro abaixo para ver os motoristas que atendem sua região.</p>
            </div>
        </section>

        <section class="py-12">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    {cards_html}
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-gray-900 text-white py-12 mt-12">
        <div class="container mx-auto px-4 text-center">
            <img src="/logo-van-escolar-parana.png" class="h-8 mx-auto mb-4 opacity-50 grayscale invert">
            <p class="text-gray-400 text-sm">&copy; {datetime.now().year} Van Escolar Paraná. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>"""

    with open(os.path.join(caminho_cidade, "index.html"), "w", encoding="utf-8") as f:
        f.write(html_final)
    print(f"✅ Página de {nome_cidade} reconstruída com sucesso!")

# Importação para pegar o ano atual
from datetime import datetime
for c in CIDADES:
    recuperar_index_com_design(c)
import os
import unicodedata
import re

# Configurações iniciais
CITY_NAME = "Maringá"
CITY_SLUG = "maringa"
BASE_DIR = r'C:\Users\marti\Documents\Projetos\vanescolarparana\maringa'
BASE_URL = "https://www.vanescolarparana.com"

# Lista de bairros fornecida
bairros = [
    "Zona 01", "Zona 02", "Zona 03", "Zona 04", "Zona 05", "Zona 06", "Zona 07", 
    "Zona 08", "Zona 10", "Zona 11", "Zona 12", "Zona 19", "Zona 27", "Zona 37", "Zona 38",
    "Jardim Alvorada", "Jardim América", "Jardim Andrade", "Jardim Atlanta", "Jardim Aclimação", 
    "Jardim Diamante", "Jardim Eldorado", "Jardim Everest", "Jardim Higienópolis", "Jardim Imperial", 
    "Jardim Internorte", "Jardim Ipanema", "Jardim Itália", "Jardim Liberdade", "Jardim Monte Rei", 
    "Jardim Novo Horizonte", "Jardim Ouro Cola", "Jardim Paulista", "Jardim Pinheiros", "Jardim Quebec", 
    "Jardim Real", "Jardim Rebouças", "Jardim Santa Helena", "Jardim São Jorge", "Jardim Universo",
    "Vila Bosque", "Vila Esperança", "Vila Morangueira", "Vila Nova", "Vila Operária", "Vila Santo Antônio",
    "Conjunto Habitacional Cidade Alta", "Conjunto Guaiapó", "Conjunto Habitacional Inocente Vila Nova Júnior", 
    "Conjunto João de Barro", "Conjunto Requião", "Conjunto Santa Felicidade"
]

def slugify(text):
    """Transforma 'Zona 01' em 'zona-01'"""
    text = unicodedata.normalize('NFD', text).encode('ascii', 'ignore').decode('utf-8')
    text = text.lower().strip()
    text = re.sub(r'[^\w\s-]', '', text)
    text = re.sub(r'[\s_-]+', '-', text)
    text = re.sub(r'^-+|-+$', '', text)
    return text

def criar_index():
    """Gera o index.html da cidade com o layout padrão"""
    links_html = ""
    for b in sorted(bairros):
        slug = slugify(b)
        links_html += f"""
                <a class="group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md transition-all text-center" href="/{CITY_SLUG}/{slug}.html">
                    <span class="text-gray-800 font-bold group-hover:text-blue-600">{b}</span>
                </a>"""

    content = f"""<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Vans Escolares em {CITY_NAME} - Van Escolar Paraná</title>
    <meta content="Lista completa de transportadores escolares em {CITY_NAME}. Escolha o bairro e fale direto com o motorista." name="description"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet"/>
    <style>body {{ font-family: 'Inter', sans-serif; }}</style>
    <script>(function(w,d,s,l,i){{w[l]=w[l]||[];w[l].push({{'gtm.start':new Date().getTime(),event:'gtm.js'}});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);}})(window,document,'script','dataLayer','GTM-WKBVRTDG');</script>
    <link href="{BASE_URL}/{CITY_SLUG}/" rel="canonical"/>
</head>
<body class="bg-gray-50 text-gray-800">
    <noscript><iframe height="0" src="https://www.googletagmanager.com/ns.html?id=GTM-WKBVRTDG" style="display:none;visibility:hidden" width="0"></iframe></noscript>
    <header class="bg-white/90 backdrop-blur border-b border-gray-100 sticky top-0 z-50">
        <nav class="container mx-auto flex h-16 items-center justify-between px-4">
            <a class="flex items-center gap-3" href="/">
                <img alt="Van Escolar Paraná" class="h-10 w-auto" height="40" src="/logo-van-escolar-parana.png" width="160"/>
            </a>
            <div class="flex items-center gap-4">
                <a class="hidden sm:inline text-gray-600 hover:text-blue-700 font-medium" href="/sobre/">Sobre</a>
                <a class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700" href="/destaque-sua-van/">Destaque sua Van</a>
            </div>
        </nav>
    </header>
    <main>
        <section class="bg-blue-600 text-white py-16 text-center">
            <div class="container mx-auto px-4">
                <h1 class="text-4xl md:text-5xl font-extrabold mb-4">Vans em {CITY_NAME}</h1>
                <p class="text-lg opacity-90">Selecione o bairro abaixo para ver os motoristas que atendem sua região.</p>
            </div>
        </section>
        <section class="py-12">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    {links_html}
                </div>
            </div>
        </section>
    </main>
    <footer class="bg-gray-900 text-white py-12 mt-12 text-center">
        <p>© 2026 Van Escolar Paraná. Todos os direitos reservados.</p>
    </footer>
</body>
</html>"""
    
    with open(os.path.join(BASE_DIR, "index.html"), "w", encoding="utf-8") as f:
        f.write(content)

def criar_bairros():
    """Gera cada arquivo bairro.html diretamente na pasta da cidade"""
    for b in bairros:
        slug = slugify(b)
        file_path = os.path.join(BASE_DIR, f"{slug}.html")

        content = f"""<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta content=\"noai, noimageai\" name=\"robots\"/> 
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Vans Escolares no {b}, {CITY_NAME} - Van Escolar Paraná</title>
    <meta content="Encontre a lista de vans escolares para o bairro {b} em {CITY_NAME}. Veja detalhes dos veículos e fale via WhatsApp com os motoristas." name="description"/>
    <link href="{BASE_URL}/{CITY_SLUG}/{slug}.html" rel="canonical"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <style>
        body {{ font-family: 'Inter', sans-serif; }}
        .whatsapp-button {{ background-color: #128C7E; color: white; transition: all 0.3s ease; }}
        .whatsapp-button:hover {{ background-color: #075E54; transform: translateY(-2px); }}
    </style>
    <script>(function(w,d,s,l,i){{w[l]=w[l]||[];w[l].push({{'gtm.start':new Date().getTime(),event:'gtm.js'}});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);}})(window,document,'script','dataLayer','GTM-WKBVRTDG');</script>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="flex flex-col min-h-screen">
        <header class="bg-white/90 backdrop-blur border-b border-gray-100 sticky top-0 z-50">
            <nav class="container mx-auto flex h-16 items-center justify-between px-4">
                <a class="flex items-center gap-3" href="/">
                    <img alt="Van Escolar Paraná" class="h-10 w-auto" height="40" src="/logo-van-escolar-parana.png" width="160"/>
                </a>
                <div class="flex items-center gap-4">
                    <a class="hidden sm:inline text-gray-600 font-medium" href="/sobre/">Sobre</a>
                    <a class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700" href="/destaque-sua-van/">Destaque sua Van</a>
                </div>
            </nav>
        </header>
        <main class="flex-grow container mx-auto px-4 py-12">
            <div class="max-w-4xl mx-auto">
                <nav class="text-sm mb-6 text-gray-500">
                    <a href="/">Início</a> / <a href="/{CITY_SLUG}/">{CITY_NAME}</a> / {b}
                </nav>
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 text-center">Vans Escolares no {b}</h1>
                <p class="text-lg text-gray-600 mb-8 text-center">Confira abaixo os motoristas que atendem sua região em {CITY_NAME}.</p>
                <section class="space-y-4" id="vans-list" style="min-height: 800px">
                    <div class="bg-white p-8 rounded-lg shadow text-center">
                        <p class="text-gray-600">Nenhuma van cadastrada para este bairro no momento.</p>
                        <a href="/destaque-sua-van/" class="font-bold underline text-blue-600 block mt-4">Motorista, apareça aqui!</a>
                    </div>
                </section>
            </div>
        </main>
        <footer class="bg-gray-900 text-white py-12 mt-12 text-center">
            <p>© 2026 Van Escolar Paraná. Todos os direitos reservados.</p>
        </footer>
    </div>
</body>
</html>"""
        
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(content)

if __name__ == "__main__":
    if not os.path.exists(BASE_DIR):
        os.makedirs(BASE_DIR)
    criar_index()
    criar_bairros()
    print(f"✅ Estrutura de {CITY_NAME} finalizada com sucesso!")
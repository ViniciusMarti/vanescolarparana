import os
from datetime import datetime

# Configurações
URL_BASE = "https://www.vanescolarparana.com.br"
PASTA_RAIZ = os.getcwd()

header = '<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'
footer = '</urlset>'

def gerar_sitemap():
    urls = []
    hoje = datetime.now().strftime('%Y-%m-%d')

    # Busca todos os arquivos HTML recursivamente
    import glob
    arquivos = glob.glob(os.path.join(PASTA_RAIZ, "**", "*.html"), recursive=True)

    for caminho in arquivos:
        if ".git" in caminho: continue
        
        # Converte o caminho do Windows para URL
        rel_path = os.path.relpath(caminho, PASTA_RAIZ).replace("\\", "/")
        
        # Limpa o nome do arquivo para a URL
        if rel_path == "index.html":
            loc = f"{URL_BASE}/"
            prioridade = "1.0"
        elif rel_path.endswith("index.html"):
            loc = f"{URL_BASE}/{rel_path.replace('index.html', '')}"
            prioridade = "0.9" # Cidades/Bairros
        else:
            loc = f"{URL_BASE}/{rel_path}"
            prioridade = "0.7"

        url_entry = f"""  <url>
    <loc>{loc}</loc>
    <lastmod>{hoje}</lastmod>
    <changefreq>weekly</changefreq>
    <priority>{prioridade}</priority>
  </url>"""
        urls.append(url_entry)

    with open("sitemap.xml", "w", encoding="utf-8") as f:
        f.write(header + "\n".join(urls) + "\n" + footer)
    
    print(f"✅ Sitemap gerado com {len(urls)} URLs!")

gerar_sitemap()
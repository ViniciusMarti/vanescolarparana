import os
from datetime import datetime
import glob

# 1. Detecta em qual projeto estamos para definir a URL correta
pasta_atual = os.getcwd().lower()
if "vanescolar" in pasta_atual:
    URL_BASE = "https://www.vanescolarparana.com"
else:
    URL_BASE = "https://viniciuscodes.com"

print(f"Gerando sitemap para: {URL_BASE}")

# 2. Estrutura do XML
header = '<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'
footer = '</urlset>'

def gerar():
    urls = []
    hoje = datetime.now().strftime('%Y-%m-%d')
    
    # Busca todos os arquivos HTML (recursivo para entrar em pastas de cidades)
    arquivos = glob.glob(os.path.join(os.getcwd(), "**", "*.html"), recursive=True)

    for caminho in arquivos:
        # Ignora arquivos de sistema e pastas ocultas
        if ".git" in caminho or "node_modules" in caminho:
            continue
        
        # Converte o caminho do arquivo para o formato de URL
        rel_path = os.path.relpath(caminho, os.getcwd()).replace("\\", "/")
        
        # Regras de prioridade e limpeza de URL
        if rel_path == "index.html":
            loc = f"{URL_BASE}/"
            prioridade = "1.0"
        elif rel_path.endswith("index.html"):
            # Para pastas tipo /curitiba/index.html -> vira /curitiba/
            loc = f"{URL_BASE}/{rel_path.replace('index.html', '')}"
            prioridade = "0.8"
        else:
            loc = f"{URL_BASE}/{rel_path}"
            prioridade = "0.6"

        entry = f"""  <url>
    <loc>{loc}</loc>
    <lastmod>{hoje}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>{prioridade}</priority>
  </url>"""
        urls.append(entry)

    with open("sitemap.xml", "w", encoding="utf-8") as f:
        f.write(header + "\n".join(urls) + "\n" + footer)
    
    print(f"✅ Sucesso! Sitemap criado com {len(urls)} links apontando para .com")

gerar()
import os
from datetime import datetime
import xml.etree.ElementTree as ET
from xml.dom import minidom

# Configurações
BASE_DIR = r'C:\Users\marti\Documents\Projetos\vanescolarparana'
BASE_URL = "https://www.vanescolarparana.com"
HOJE = datetime.now().strftime('%Y-%m-%d')

def gerar_sitemap():
    # Cria a estrutura básica do XML
    urlset = ET.Element("urlset", xmlns="http://www.sitemaps.org/schemas/sitemap/0.9")

    for root, dirs, files in os.walk(BASE_DIR):
        for file in files:
            if file.endswith(".html"):
                # Caminho relativo (ex: curitiba\abranches.html)
                rel_path = os.path.relpath(os.path.join(root, file), BASE_DIR).replace('\\', '/')
                
                # Transforma o caminho em URL limpa
                if rel_path == "index.html":
                    url_path = ""
                    priority = "1.0"
                else:
                    url_path = rel_path.replace('.html', '/')
                    url_path = url_path.replace('index/', '') # Ajusta index de subpastas
                    priority = "0.8"
                    
                full_url = f"{BASE_URL}/{url_path}"

                # Cria o nó da URL
                url_node = ET.SubElement(urlset, "url")
                ET.SubElement(url_node, "loc").text = full_url
                ET.SubElement(url_node, "lastmod").text = HOJE
                ET.SubElement(url_node, "changefreq").text = "monthly"
                ET.SubElement(url_node, "priority").text = priority

    # Formatação "bonita" (pretty print)
    xml_str = ET.tostring(urlset, encoding='utf-8')
    parsed_str = minidom.parseString(xml_str)
    pretty_xml = parsed_str.toprettyxml(indent="  ")

    # Salva o arquivo
    sitemap_path = os.path.join(BASE_DIR, "sitemap.xml")
    with open(sitemap_path, "w", encoding="utf-8") as f:
        f.write(pretty_xml)
    
    print(f"✅ Sitemap gerado com sucesso em: {sitemap_path}")

if __name__ == "__main__":
    gerar_sitemap()
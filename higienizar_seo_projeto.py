import os
import unicodedata
import re
import csv
from bs4 import BeautifulSoup

# Configurações
BASE_DIR = r'C:\Users\marti\Documents\Projetos\vanescolarparana'
BASE_URL = "https://www.vanescolarparana.com"

def slugify(text):
    """Remove acentos, ç e caracteres não permitidos"""
    nfkd = unicodedata.normalize('NFD', text)
    text_clean = nfkd.encode('ascii', 'ignore').decode('utf-8')
    text_clean = text_clean.lower().replace(' ', '-')
    return re.sub(r'[^a-z0-9-]', '', text_clean)

def processar_projeto():
    mapeamento_redirecionamentos = []
    
    # 1. Renomear Pastas primeiro (de baixo para cima para não perder a referência)
    for root, dirs, files in os.walk(BASE_DIR, topdown=False):
        for name in dirs:
            old_path = os.path.join(root, name)
            new_name = slugify(name)
            if name != new_name:
                new_path = os.path.join(root, new_name)
                # Verifica se a pasta de destino já existe antes de renomear
                if not os.path.exists(new_path):
                    os.rename(old_path, new_path)
                    print(f"📁 Pasta renomeada: {name} -> {new_name}")
                else:
                    print(f"⚠️ Aviso: Pasta {new_name} já existe. Mova os ficheiros manualmente de {name}.")

    # 2. Processar Ficheiros HTML e Canonical
    for root, dirs, files in os.walk(BASE_DIR):
        for file in files:
            if file.endswith(".html"):
                file_path = os.path.join(root, file)
                
                # Caminho relativo para lógica de URL
                rel_path = os.path.relpath(file_path, BASE_DIR).replace('\\', '/')
                
                # Gera a nova URL Slugificada (Padrão Curitiba)
                parts = rel_path.split('/')
                clean_parts = [slugify(p.replace('.html', '')) for p in parts]
                
                # Lógica para index.html (URL da raiz da pasta)
                if clean_parts[-1] == 'index':
                    url_clean_path = "/".join(clean_parts[:-1]) + "/"
                else:
                    url_clean_path = "/".join(clean_parts) + "/"
                
                # Remove barras duplas se existirem
                url_clean_path = url_clean_path.replace('//', '/')
                new_canonical = f"{BASE_URL}/{url_clean_path}".replace('https:/', 'https://')

                # Abrir e actualizar o HTML
                with open(file_path, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                soup = BeautifulSoup(content, 'lxml')
                tag_canonical = soup.find('link', rel='canonical')
                
                old_canonical = tag_canonical.get('href', '') if tag_canonical else "N/A"
                
                # Actualiza ou cria a tag
                if tag_canonical:
                    tag_canonical['href'] = new_canonical
                else:
                    if soup.head:
                        new_tag = soup.new_tag('link', rel='canonical', href=new_canonical)
                        soup.head.append(new_tag)

                # Salva o ficheiro limpo
                with open(file_path, 'w', encoding='utf-8') as f:
                    f.write(str(soup))

                # Regista para o documento de redirect
                if old_canonical != new_canonical:
                    mapeamento_redirecionamentos.append({
                        'origem': old_canonical,
                        'destino': new_canonical,
                        'ficheiro_local': rel_path
                    })

    # 3. Gerar Documento de Redirect (CSV)
    doc_path = os.path.join(BASE_DIR, "redirecionamentos_seo.csv")
    with open(doc_path, 'w', newline='', encoding='utf-8') as csvfile:
        fieldnames = ['origem', 'destino', 'ficheiro_local']
        writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
        writer.writeheader()
        for row in mapeamento_redirecionamentos:
            writer.writerow(row)

    print(f"\n✅ Higienização concluída!")
    print(f"📊 Relatório de redirecionamentos gerado em: {doc_path}")

if __name__ == "__main__":
    processar_projeto()
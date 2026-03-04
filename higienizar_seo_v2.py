import os
import unicodedata
import re
import csv
from bs4 import BeautifulSoup

BASE_DIR = r'C:\Users\marti\Documents\Projetos\vanescolarparana'
BASE_URL = "https://www.vanescolarparana.com"

def slugify(text):
    nfkd = unicodedata.normalize('NFD', text)
    text_clean = nfkd.encode('ascii', 'ignore').decode('utf-8')
    text_clean = text_clean.lower().replace(' ', '-')
    return re.sub(r'[^a-z0-9-]', '', text_clean)

def processar_projeto():
    mapeamento_redirecionamentos = []
    
    # 1. Renomear Pastas (IGNORANDO HIDDEN FOLDERS COMO .git)
    for root, dirs, files in os.walk(BASE_DIR, topdown=False):
        # Filtra para não mexer em pastas que começam com ponto
        dirs[:] = [d for d in dirs if not d.startswith('.')]
        
        for name in dirs:
            old_path = os.path.join(root, name)
            new_name = slugify(name)
            if name != new_name:
                new_path = os.path.join(root, new_name)
                if not os.path.exists(new_path):
                    os.rename(old_path, new_path)
                    print(f"📁 Pasta corrigida: {name} -> {new_name}")

    # 2. Processar Ficheiros e Canonical
    for root, dirs, files in os.walk(BASE_DIR):
        # Ignora pastas ocultas no processamento de arquivos também
        if any(part.startswith('.') for part in root.split(os.sep)):
            continue

        for file in files:
            if file.endswith(".html"):
                file_path = os.path.join(root, file)
                rel_path = os.path.relpath(file_path, BASE_DIR).replace('\\', '/')
                
                # Gera URL Slugificada
                parts = rel_path.split('/')
                clean_parts = [slugify(p.replace('.html', '')) for p in parts]
                
                if clean_parts[-1] == 'index':
                    url_clean_path = "/".join(clean_parts[:-1])
                else:
                    url_clean_path = "/".join(clean_parts)
                
                # CONSTRUÇÃO SEGURA DA URL (Sem triplas barras)
                if url_clean_path:
                    new_canonical = f"{BASE_URL}/{url_clean_path}/".replace('//', '/').replace('https:/', 'https://')
                else:
                    new_canonical = f"{BASE_URL}/"

                with open(file_path, 'r', encoding='utf-8') as f:
                    soup = BeautifulSoup(f.read(), 'lxml')
                
                tag_canonical = soup.find('link', rel='canonical')
                old_canonical = tag_canonical.get('href', '') if tag_canonical else "N/A"
                
                if tag_canonical:
                    tag_canonical['href'] = new_canonical
                elif soup.head:
                    new_tag = soup.new_tag('link', rel='canonical', href=new_canonical)
                    soup.head.append(new_tag)

                with open(file_path, 'w', encoding='utf-8') as f:
                    f.write(str(soup))

                if old_canonical != new_canonical:
                    mapeamento_redirecionamentos.append({
                        'origem': old_canonical,
                        'destino': new_canonical,
                        'ficheiro_local': rel_path
                    })

    # 3. Gerar Documento de Redirect
    doc_path = os.path.join(BASE_DIR, "redirecionamentos_seo.csv")
    with open(doc_path, 'w', newline='', encoding='utf-8') as csvfile:
        fieldnames = ['origem', 'destino', 'ficheiro_local']
        writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(mapeamento_redirecionamentos)

    print(f"\n✅ Higienização V2 concluída com sucesso!")
    print(f"📊 Relatório atualizado gerado em: {doc_path}")

if __name__ == "__main__":
    processar_projeto()
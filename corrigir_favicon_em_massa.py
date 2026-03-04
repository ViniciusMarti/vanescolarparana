import os
from bs4 import BeautifulSoup

BASE_DIR = r'C:\Users\marti\Documents\Projetos\vanescolarparana'
FAVICON_TAG = '<link rel="icon" type="image/png" href="/logo-van-escolar-parana.png"/>'

def corrigir_favicon():
    print("Iniciando correção de favicons...")
    arquivos_corrigidos = 0

    for root, dirs, files in os.walk(BASE_DIR):
        # Ignora a pasta .git
        if '.git' in root:
            continue
            
        for file in files:
            if file.endswith(".html"):
                file_path = os.path.join(root, file)
                
                with open(file_path, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                # Verifica se o favicon já existe no texto (para evitar duplicatas)
                if 'rel="icon"' in content or 'rel="shortcut icon"' in content:
                    continue

                soup = BeautifulSoup(content, 'lxml')
                
                if soup.head:
                    # Cria a nova tag de favicon
                    new_link = soup.new_tag('link', rel='icon', type='image/png', href='/logo-van-escolar-parana.png')
                    soup.head.append(new_link)
                    
                    with open(file_path, 'w', encoding='utf-8') as f:
                        f.write(str(soup))
                    
                    arquivos_corrigidos += 1

    print(f"✅ Sucesso! Favicon adicionado em {arquivos_corrigidos} arquivos.")

if __name__ == "__main__":
    corrigir_favicon()
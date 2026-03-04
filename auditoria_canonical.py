import os
from bs4 import BeautifulSoup

# Configurações de ambiente
BASE_DIR = r'C:\Users\marti\Documents\Projetos\vanescolarparana'
BASE_URL = "https://www.vanescolarparana.com"

def auditoria_canonical():
    print(f"{'ARQUIVO':<50} | {'STATUS':<10} | {'OBSERVAÇÃO'}")
    print("-" * 100)
    
    erros_encontrados = 0
    total_arquivos = 0

    for root, dirs, files in os.walk(BASE_DIR):
        for file in files:
            if file.endswith(".html"):
                total_arquivos += 1
                file_path = os.path.join(root, file)
                rel_path = os.path.relpath(file_path, BASE_DIR).replace('\\', '/')
                
                # Calcula a URL esperada (Seguindo a lógica do seu sitemap e V3)
                clean_path = rel_path.replace('index.html', '').replace('.html', '')
                if clean_path and not clean_path.endswith('/'):
                    clean_path += '/'
                
                url_esperada = f"{BASE_URL}/{clean_path}".replace('//', '/').replace('https:/', 'https://')

                with open(file_path, 'r', encoding='utf-8') as f:
                    soup = BeautifulSoup(f.read(), 'lxml')
                
                canonical_tag = soup.find('link', rel='canonical')
                
                if not canonical_tag:
                    print(f"{rel_path:<50} | ❌ ERRO    | Tag ausente")
                    erros_encontrados += 1
                else:
                    url_encontrada = canonical_tag.get('href', '')
                    
                    if url_encontrada == url_esperada:
                        # Tudo certo (comentado para não poluir o terminal, se preferir)
                        # print(f"{rel_path:<50} | ✅ OK      | -")
                        pass
                    else:
                        print(f"{rel_path:<50} | ❌ ERRO    | Encontrado: {url_encontrada}")
                        erros_encontrados += 1

    print("-" * 100)
    print(f"📊 Resumo: {total_arquivos} arquivos analisados. {erros_encontrados} erros encontrados.")

if __name__ == "__main__":
    auditoria_canonical()
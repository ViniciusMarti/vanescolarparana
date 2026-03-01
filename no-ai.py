import os
import glob

PASTA_RAIZ = os.getcwd()
META_NOAI = '<meta name="robots" content="noai, noimageai">'

for arquivo in glob.glob(os.path.join(PASTA_RAIZ, "**", "*.html"), recursive=True):
    with open(arquivo, 'r', encoding='utf-8') as f:
        conteudo = f.read()
    
    if 'noai' not in conteudo:
        # Inserir logo abaixo do <head>
        novo = conteudo.replace('<head>', f'<head>\n    {META_NOAI}')
        with open(arquivo, 'w', encoding='utf-8') as f:
            f.write(novo)

print("✅ Tag No-AI injetada em todos os arquivos!")
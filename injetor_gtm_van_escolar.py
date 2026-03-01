import os
import glob

# ==========================================
# 1. CONFIGURAÇÕES DO GTM (ID: GTM-WKBVRTDG)
# ==========================================
PASTA_RAIZ = os.getcwd()

# Bloco que vai no topo do <head>
GTM_HEAD = """<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-WKBVRTDG');</script>
"""

# Bloco que vai logo após o <body>
GTM_BODY = """<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WKBVRTDG"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
"""

# ==========================================
# 2. EXECUÇÃO EM TODAS AS SUBPASTAS
# ==========================================
print(f"Iniciando instalação do GTM em: {PASTA_RAIZ}")
contador = 0

# Busca todos os arquivos .html em todas as pastas e subpastas
for arquivo in glob.glob(os.path.join(PASTA_RAIZ, "**", "*.html"), recursive=True):
    # Ignora pastas de sistema do Git
    if ".git" in arquivo:
        continue

    with open(arquivo, 'r', encoding='utf-8') as f:
        conteudo = f.read()

    # Verifica se o GTM já está instalado para evitar duplicidade
    if 'GTM-WKBVRTDG' not in conteudo:
        # Injeta no Head
        conteudo = conteudo.replace('<head>', f'<head>\n{GTM_HEAD}')
        
        # Injeta no Body
        conteudo = conteudo.replace('<body>', f'<body>\n{GTM_BODY}')
        
        with open(arquivo, 'w', encoding='utf-8') as f:
            f.write(conteudo)
        
        print(f"✅ GTM instalado: {os.path.relpath(arquivo, PASTA_RAIZ)}")
        contador += 1

print(f"\n🚀 Concluído! GTM-WKBVRTDG instalado em {contador} páginas.")
import os
import glob

# ==========================================
# 1. CONFIGURAÇÕES (ID: G-ETL54HBEXL)
# ==========================================
PASTA_RAIZ = os.getcwd() 

CODIGO_GA4_VAN = """<script async src="https://www.googletagmanager.com/gtag/js?id=G-ETL54HBEXL"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-ETL54HBEXL');
</script>
"""

# ==========================================
# 2. EXECUÇÃO RECURSIVA (ENTRA EM TODAS AS PASTAS)
# ==========================================
print(f"Iniciando GA4 no projeto Van Escolar: {PASTA_RAIZ}")
contador = 0

# O padrão "**/*.html" vasculha a pasta atual e todas as subpastas
for arquivo in glob.glob(os.path.join(PASTA_RAIZ, "**", "*.html"), recursive=True):
    # Pula arquivos dentro de pastas ocultas como .git
    if ".git" in arquivo:
        continue

    with open(arquivo, 'r', encoding='utf-8') as f:
        conteudo = f.read()

    # Verifica se já existe para não duplicar
    if 'G-ETL54HBEXL' not in conteudo:
        # Injeta logo após o <head>
        novo_conteudo = conteudo.replace('<head>', f'<head>\n{CODIGO_GA4_VAN}')
        
        with open(arquivo, 'w', encoding='utf-8') as f:
            f.write(novo_conteudo)
        
        print(f"✅ GA4 injetado em: {os.path.relpath(arquivo, PASTA_RAIZ)}")
        contador += 1

print(f"\n🚀 Sucesso! O GA4 foi instalado em {contador} páginas de vans.")
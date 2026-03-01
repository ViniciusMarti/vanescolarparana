import os
import glob
import re

# ==========================================
# 1. CONFIGURAÇÕES - O PADRÃO OFICIAL
# ==========================================
PASTA_RAIZ = os.getcwd()

HEADER_OFICIAL = """
  <header class="bg-white/90 backdrop-blur border-b border-gray-100 sticky top-0 z-50">
    <nav class="container mx-auto flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
      <a href="/" class="flex items-center gap-3">
        <img src="/logo-van-escolar-parana.png" alt="" class="h-10" />
        <span class="sr-only">Van Escolar Paraná</span>
      </a>
      <div class="flex items-center gap-4">
        <a href="/sobre/" class="hidden sm:inline text-gray-600 hover:text-blue-700 font-medium">Sobre</a>
        <a href="/informativos/" class="hidden sm:inline text-gray-600 hover:text-blue-700 font-medium">Informativos</a>
        <a href="/destaque-sua-van/" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">Destaque sua Van</a>
      </div>
    </nav>
  </header>
"""

# ==========================================
# 2. EXECUÇÃO
# ==========================================
print(f"Limpando e padronizando menus em: {PASTA_RAIZ}")
contador = 0

# Busca recursiva em todas as subpastas
for arquivo in glob.glob(os.path.join(PASTA_RAIZ, "**", "*.html"), recursive=True):
    if ".git" in arquivo:
        continue

    with open(arquivo, 'r', encoding='utf-8') as f:
        conteudo = f.read()

    # --- PASSO 1: LIMPEZA DE CSS ERRADO ---
    # Remove qualquer bloco de estilo do menu dropdown que possa ter sobrado
    padrao_css = r'<style id="menu-v2-styles">.*?</style>'
    conteudo = re.sub(padrao_css, '', conteudo, flags=re.DOTALL)
    
    # Remove o comentário manual de CSS anterior
    padrao_css_manual = r'/\* Estilos do Novo Menu Dropdown \*/.*?\}'
    conteudo = re.sub(padrao_css_manual, '', conteudo, flags=re.DOTALL)

    # --- PASSO 2: PADRONIZAÇÃO DO HEADER ---
    # Localiza QUALQUER tag <header>...</header> e substitui pelo oficial
    # O flags=re.DOTALL permite que o '.' capture quebras de linha
    padrao_qualquer_header = r'<header.*?>.*?</header>'
    
    if re.search(padrao_qualquer_header, conteudo, flags=re.DOTALL):
        conteudo = re.sub(padrao_qualquer_header, HEADER_OFICIAL, conteudo, flags=re.DOTALL)
        
        with open(arquivo, 'w', encoding='utf-8') as f:
            f.write(conteudo)
        
        print(f"✅ Menu padronizado: {os.path.relpath(arquivo, PASTA_RAIZ)}")
        contador += 1

print(f"\n🚀 Sucesso! {contador} arquivos agora possuem o menu oficial e limpo.")
import os
import glob
import re

# ==========================================
# 1. CONFIGURAÇÕES
# ==========================================
PASTA_RAIZ = os.getcwd()

# O Header original do Van Escolar Paraná que você me enviou
HEADER_ORIGINAL = """
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
# 2. EXECUÇÃO DA REVERSÃO
# ==========================================
print("Iniciando reversão do menu no Van Escolar...")

arquivos = glob.glob(os.path.join(PASTA_RAIZ, "**", "*.html"), recursive=True)

for arquivo in arquivos:
    if ".git" in arquivo: continue
    
    with open(arquivo, 'r', encoding='utf-8') as f:
        conteudo = f.read()

    # 1. Remove o CSS do dropdown que foi injetado por engano
    # Buscamos o bloco que começa com o comentário do script anterior
    padrao_css = r'/\* Estilos do Novo Menu Dropdown \*/.*?@media \(max-width: 600px\) \{.*?\}'
    conteudo = re.sub(padrao_css, '', conteudo, flags=re.DOTALL)

    # 2. Localiza o header errado (o do viniciuscodes) e troca pelo original do vanescolar
    # O header errado tem a classe 'header-container'
    padrao_header_errado = r'<header>.*?</header>'
    conteudo = re.sub(padrao_header_errado, HEADER_ORIGINAL, conteudo, flags=re.DOTALL)

    with open(arquivo, 'w', encoding='utf-8') as f:
        f.write(conteudo)

print(f"✅ Menu restaurado com sucesso em {len(arquivos)} arquivos!")
import os
import glob

# ==========================================
# 1. CONFIGURAÇÕES
# ==========================================
# O script vai atuar na pasta onde ele for executado
PASTA_RAIZ = os.getcwd()

# O erro encontrado no seu código: "or%C3%A" + "ç" + "amento"
TEXTO_COM_ERRO = "or%C3%Açamento"
# A forma correta codificada para URL: "or%C3%A7amento"
TEXTO_CORRIGIDO = "or%C3%A7amento"

# ==========================================
# 2. EXECUÇÃO DA CORREÇÃO EM MASSA
# ==========================================
print(f"Iniciando correção de encoding no WhatsApp em: {PASTA_RAIZ}")
contador = 0

# Busca recursiva em todas as subpastas
for arquivo in glob.glob(os.path.join(PASTA_RAIZ, "**", "*.html"), recursive=True):
    if ".git" in arquivo:
        continue

    with open(arquivo, 'r', encoding='utf-8') as f:
        conteudo = f.read()

    # Se encontrar o erro, faz a substituição
    if TEXTO_COM_ERRO in conteudo:
        novo_conteudo = conteudo.replace(TEXTO_COM_ERRO, TEXTO_CORRIGIDO)
        
        with open(arquivo, 'w', encoding='utf-8') as f:
            f.write(novo_conteudo)
        
        print(f"✅ Corrigido: {os.path.relpath(arquivo, PASTA_RAIZ)}")
        contador += 1

print(f"\n🚀 Sucesso! {contador} arquivos foram corrigidos.")
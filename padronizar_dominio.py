import os
import glob

# Defina o caminho da pasta que você quer limpar (Vinicius Codes ou Van Escolar)
PASTA_PROJETO = os.getcwd() 

print(f"Iniciando padronização para .com em: {PASTA_PROJETO}")

arquivos = glob.glob(os.path.join(PASTA_PROJETO, "**", "*.html"), recursive=True)
contador = 0

for arquivo in arquivos:
    if ".git" in arquivo: continue
    
    with open(arquivo, 'r', encoding='utf-8') as f:
        conteudo = f.read()

    # Troca o domínio completo para evitar erros de substituição parcial
    if ".com.br" in conteudo:
        novo_conteudo = conteudo.replace("vanescolarparana.com.br", "vanescolarparana.com")
        novo_conteudo = novo_conteudo.replace("viniciuscodes.com.br", "viniciuscodes.com")
        
        with open(arquivo, 'w', encoding='utf-8') as f:
            f.write(novo_conteudo)
        
        contador += 1

print(f"✅ Sucesso! {contador} arquivos foram corrigidos e agora apontam para .com")
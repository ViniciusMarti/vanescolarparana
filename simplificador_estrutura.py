import os
import shutil

# ==========================================
# 1. CONFIGURAÇÕES
# ==========================================
PASTA_RAIZ = os.getcwd()
CIDADES_PARA_PROCESSAR = ['curitiba', 'sao-jose-dos-pinhais']

def simplificar():
    for cidade in CIDADES_PARA_PROCESSAR:
        caminho_cidade = os.path.join(PASTA_RAIZ, cidade)
        
        if not os.path.exists(caminho_cidade):
            print(f"⚠️ Pasta não encontrada: {cidade}")
            continue

        print(f"📂 Processando cidade: {cidade}...")

        # Lista todas as subpastas (os bairros)
        subpastas = [f.path for f in os.scandir(caminho_cidade) if f.is_dir()]

        for pasta_bairro in subpastas:
            nome_bairro = os.path.basename(pasta_bairro)
            arquivo_index = os.path.join(pasta_bairro, 'index.html')
            novo_nome_arquivo = os.path.join(caminho_cidade, f"{nome_bairro}.html")

            # Verifica se o index.html existe dentro da pasta do bairro
            if os.path.exists(arquivo_index):
                # Move e renomeia: /curitiba/boa-vista/index.html -> /curitiba/boa-vista.html
                shutil.move(arquivo_index, novo_nome_arquivo)
                print(f"✅ Movido: {nome_bairro}.html")

                # Remove a pasta do bairro se ela estiver vazia agora
                try:
                    os.rmdir(pasta_bairro)
                    print(f"🗑️  Pasta removida: {nome_bairro}")
                except OSError:
                    print(f"⚠️  Pasta {nome_bairro} não removida (ainda contém arquivos, como imagens).")

    print("\n🚀 Estrutura simplificada com sucesso!")

if __name__ == "__main__":
    simplificar()
import os
import shutil

PASTA_RAIZ = os.getcwd()
CIDADES = ['curitiba', 'sao-jose-dos-pinhais']

for cidade in CIDADES:
    caminho_cidade = os.path.join(PASTA_RAIZ, cidade)
    if not os.path.exists(caminho_cidade): continue

    print(f"Organizando {cidade}...")
    
    # Busca todas as subpastas (bairros que ainda são pastas)
    for item in os.listdir(caminho_cidade):
        caminho_item = os.path.join(caminho_cidade, item)
        
        # Se for uma pasta e não for a pasta .git
        if os.path.isdir(caminho_item) and not item.startswith('.'):
            arquivo_index_bairro = os.path.join(caminho_item, 'index.html')
            
            if os.path.exists(arquivo_index_bairro):
                # Move o index do bairro para fora: curitiba/boa-vista/index.html -> curitiba/boa-vista.html
                novo_local = os.path.join(caminho_cidade, f"{item}.html")
                shutil.move(arquivo_index_bairro, novo_local)
                
                # Remove a pasta agora vazia
                try:
                    shutil.rmtree(caminho_item)
                    print(f"✅ {item}.html pronto.")
                except:
                    pass

print("\n🚀 Tudo no lugar! Cidades com index.html e bairros com nome.html")
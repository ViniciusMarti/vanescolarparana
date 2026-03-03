import os
import re

# Caminhos das pastas
base_path = r'C:\Users\marti\Documents\Projetos\vanescolarparana'
origem_file = os.path.join(base_path, 'sao-jose-dos-pinhais', 'index.html')
destino_dir = os.path.join(base_path, 'curitiba')
destino_file = os.path.join(destino_dir, 'index.html')

def sincronizar_paginas():
    # 1. Ler o HTML de São José dos Pinhais (O Modelo)
    with open(origem_file, 'r', encoding='utf-8') as f:
        html_sjp = f.read()

    # 2. Listar bairros de Curitiba
    bairros_curitiba = [f for f in os.listdir(destino_dir) 
                        if f.endswith('.html') and f != 'index.html']
    
    # Criar o novo bloco de links (mantendo a indentação simples)
    novo_bloco_links = ""
    for bairro in sorted(bairros_curitiba):
        nome_exibicao = bairro.replace('.html', '').replace('-', ' ').title()
        novo_bloco_links += f'    <a href="{bairro}">{nome_exibicao}</a><br>\n'

    # 3. Ajustar Textos Gerais (SEO)
    # Troca todas as menções de SJP para Curitiba no texto
    novo_html = html_sjp.replace("São José dos Pinhais", "Curitiba")
    novo_html = novo_html.replace("sao-jose-dos-pinhais", "curitiba")

    # 4. Tentar encontrar a lista de links original e substituir
    # Aqui vamos procurar por um padrão comum. Se seus links estiverem 
    # dentro de uma div específica, podemos ser mais precisos.
    # Vou usar uma regex que procura por links .html que NÃO tenham "/" (links locais)
    padrao_links = r'<a href="[^/]+\.html">.*?</a>(?:<br>)?'
    
    # Encontramos todos os links de bairros antigos
    links_antigos = re.findall(padrao_links, novo_html)
    
    if links_antigos:
        # Pegamos o primeiro e o último link para identificar a "zona de bairros"
        primeiro = links_antigos[0]
        ultimo = links_antigos[-1]
        
        # Criamos um marcador temporário para a substituição
        inicio_idx = novo_html.find(primeiro)
        fim_idx = novo_html.find(ultimo) + len(ultimo)
        
        html_final = novo_html[:inicio_idx] + novo_bloco_links + novo_html[fim_idx:]
    else:
        html_final = novo_html
        print("Aviso: Não consegui identificar a lista de links automaticamente.")

    # 5. Salvar o resultado
    with open(destino_file, 'w', encoding='utf-8') as f:
        f.write(html_final)

    print(f"Sucesso! {destino_file} atualizado com CSS interno e novos links.")

if __name__ == "__main__":
    sincronizar_paginas()

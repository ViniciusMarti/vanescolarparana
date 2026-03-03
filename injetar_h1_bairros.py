import os
from bs4 import BeautifulSoup

base_path = r'C:\Users\marti\Documents\Projetos\vanescolarparana'
pastas_cidades = ['curitiba', 'sao-jose-dos-pinhais', 'londrina', 'maringa', 'ponta-grossa', 'pinhais', 'foz-do-iguaçu']

def injetar_h1_centralizado():
    for cidade in pastas_cidades:
        caminho_cidade = os.path.join(base_path, cidade)
        if not os.path.exists(caminho_cidade): continue

        print(f"Centralizando títulos em: {cidade}")

        for arquivo in os.listdir(caminho_cidade):
            if arquivo.endswith(".html") and arquivo != "index.html":
                caminho_file = os.path.join(caminho_cidade, arquivo)
                
                with open(caminho_file, 'r', encoding='utf-8') as f:
                    soup = BeautifulSoup(f, 'html.parser')

                # Se já existir um H1, vamos apenas garantir que ele esteja centralizado
                h1_existente = soup.find('h1')
                if h1_existente:
                    h1_existente['class'] = "text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 tracking-tight text-center"
                else:
                    # Se não existir, criamos o novo centralizado
                    full_title = soup.title.string if soup.title else "Van Escolar"
                    h1_text = full_title.split(' (')[0]

                    new_h1 = soup.new_tag("h1")
                    # Adicionado 'text-center'
                    new_h1['class'] = "text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 tracking-tight text-center"
                    new_h1.string = h1_text

                    new_p = soup.new_tag("p")
                    # Adicionado 'text-center' e 'mx-auto' para o parágrafo
                    new_p['class'] = "text-lg text-gray-600 mb-12 text-center max-w-2xl mx-auto"
                    new_p.string = "Confira abaixo os motoristas que atendem sua região com segurança e conforto."

                    vans_list = soup.find(id="vans-list")
                    if vans_list:
                        vans_list.insert_before(new_h1)
                        vans_list.insert_before(new_p)

                # Salva o arquivo com a formatação bonita
                with open(caminho_file, 'w', encoding='utf-8') as f:
                    f.write(soup.prettify())

    print("\n--- Títulos centralizados com sucesso! ---")

if __name__ == "__main__":
    injetar_h1_centralizado()
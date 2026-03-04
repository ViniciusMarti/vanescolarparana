import os
from bs4 import BeautifulSoup
import re

# Caminho para o index principal (Homepage)
INDEX_PATH = r'C:\Users\marti\Documents\Projetos\vanescolarparana\index.html'

def adicionar_cidades_homepage():
    if not os.path.exists(INDEX_PATH):
        print(f"Erro: Arquivo não encontrado em {INDEX_PATH}")
        return

    with open(INDEX_PATH, 'r', encoding='utf-8') as f:
        soup = BeautifulSoup(f.read(), 'lxml')

    # Cidades novas higienizadas (sem acentos nas URLs)
    novas_cidades = [
        {"nome": "Foz do Iguaçu", "url": "/foz-do-iguacu/"},
        {"nome": "Londrina", "url": "/londrina/"},
        {"nome": "Maringá", "url": "/maringa/"},
        {"nome": "Ponta Grossa", "url": "/ponta-grossa/"}
    ]

    # Localiza um bloco existente para copiar as classes de estilo exatas
    referencia = soup.find('a', href=re.compile(r'/(curitiba|sao-jose-dos-pinhais)/?$'))
    
    if not referencia:
        print("Não foi possível localizar o container de cidades no index.html.")
        return

    container = referencia.parent
    classes_layout = referencia.get('class')

    print("Iniciando atualização dos blocos...")

    for cidade in novas_cidades:
        # Verifica se o link já existe para evitar duplicatas
        if soup.find('a', href=cidade['url']):
            print(f"Skipped: {cidade['nome']} já está no index.")
            continue

        # Cria o novo elemento <a> mantendo o layout
        nova_tag = soup.new_tag('a', href=cidade['url'], attrs={'class': classes_layout})
        
        # Cria o span interno com o nome da cidade
        span = soup.new_tag('span', class_="text-gray-800 font-bold group-hover:text-blue-600")
        span.string = cidade['nome']
        
        nova_tag.append(span)
        container.append(nova_tag)
        print(f"✅ Bloco adicionado: {cidade['nome']}")

    # Salva o arquivo final preservando a formatação
    with open(INDEX_PATH, 'w', encoding='utf-8') as f:
        f.write(soup.prettify())
    
    print("\n🚀 Homepage atualizada com sucesso!")

if __name__ == "__main__":
    adicionar_cidades_homepage()
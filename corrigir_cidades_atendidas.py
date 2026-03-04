import os
from bs4 import BeautifulSoup
import re

# Caminho para o index principal
INDEX_PATH = r'C:\Users\marti\Documents\Projetos\vanescolarparana\index.html'

def atualizar_secao_cidades():
    if not os.path.exists(INDEX_PATH):
        print("Arquivo index.html não encontrado.")
        return

    with open(INDEX_PATH, 'r', encoding='utf-8') as f:
        soup = BeautifulSoup(f.read(), 'lxml')

    # 1. Localiza a seção "Cidades atendidas"
    titulo_secao = soup.find('h2', string=re.compile(r'Cidades atendidas', re.I))
    if not titulo_secao:
        print("Seção 'Cidades atendidas' não encontrada.")
        return
    
    container = titulo_secao.find_next('div', class_='grid')

    # 2. Remove o bloco "Em breve" de Ponta Grossa
    bloco_em_breve = container.find('h3', string=re.compile(r'Ponta Grossa', re.I))
    if bloco_em_breve:
        bloco_em_breve.find_parent('div', class_='rounded-2xl').decompose()
        print("✅ Bloco 'Em breve' removido.")

    # 3. Dados das novas cidades
    novas_cidades = [
        {
            "nome": "Foz do Iguaçu", 
            "url": "/foz-do-iguacu/", 
            "img": "/imagens/foz.jpg", 
            "desc": "Transporte escolar na Terra das Cataratas."
        },
        {
            "nome": "Londrina", 
            "url": "/londrina/", 
            "img": "/imagens/londrina.jpg", 
            "desc": "Encontre vans seguras na região norte."
        },
        {
            "nome": "Maringá", 
            "url": "/maringa/", 
            "img": "/imagens/maringa.jpg", 
            "desc": "Diretório completo para a Cidade Canção."
        },
        {
            "nome": "Ponta Grossa", 
            "url": "/ponta-grossa/", 
            "img": "/imagens/ponto-grossa.webp", 
            "desc": "Vans escolares nos Campos Gerais."
        }
    ]

    for cidade in novas_cidades:
        # Verifica se já existe para não duplicar
        if soup.find('h3', string=cidade['nome']) and not "Em breve" in str(soup.find('h3', string=cidade['nome']).parent):
            continue

        # HTML do Card (Copiado fielmente do layout de Curitiba/SJP)
        card_html = f'''
        <div class="bg-white rounded-2xl shadow overflow-hidden group">
            <a href="{cidade['url']}">
                <img alt="{cidade['nome']}" class="w-full h-48 object-cover group-hover:scale-105 transition" src="{cidade['img']}"/>
            </a>
            <div class="p-6">
                <h3 class="text-2xl font-bold">{cidade['nome']}</h3>
                <p class="text-gray-600 mt-2">{cidade['desc']}</p>
                <a class="inline-block mt-4 bg-blue-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-blue-700" href="{cidade['url']}">
                    Ver bairros
                </a>
            </div>
        </div>
        '''
        container.append(BeautifulSoup(card_html, 'html.parser'))
        print(f"🚀 Card adicionado: {cidade['nome']}")

    # Salva as alterações
    with open(INDEX_PATH, 'w', encoding='utf-8') as f:
        f.write(soup.prettify())

    print("\n✅ Seção 'Cidades atendidas' atualizada com sucesso!")

if __name__ == "__main__":
    atualizar_secao_cidades()
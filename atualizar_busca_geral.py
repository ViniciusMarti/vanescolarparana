import os
import re
from bs4 import BeautifulSoup

# Caminhos e Configurações
BASE_DIR = r'C:\Users\marti\Documents\Projetos\vanescolarparana'
INDEX_PATH = os.path.join(BASE_DIR, 'index.html')

# Mapeamento para nomes bonitos na exibição da busca
CITY_PRETTY_NAMES = {
    'curitiba': 'Curitiba',
    'foz-do-iguacu': 'Foz do Iguaçu',
    'londrina': 'Londrina',
    'maringa': 'Maringá',
    'pinhais': 'Pinhais',
    'ponta-grossa': 'Ponta Grossa',
    'sao-jose-dos-pinhais': 'São José dos Pinhais'
}

def extrair_dados_busca():
    lista_app_data = []
    cidades_folders = list(CITY_PRETTY_NAMES.keys())

    print("🔍 Analisando bairros para o sistema de busca...")

    for cidade_slug in cidades_folders:
        cidade_path = os.path.join(BASE_DIR, cidade_slug)
        if not os.path.exists(cidade_path):
            continue

        for file in os.listdir(cidade_path):
            # Ignora o index da própria cidade e ficheiros que não são HTML
            if file.endswith('.html') and file != 'index.html':
                file_path = os.path.join(cidade_path, file)
                
                with open(file_path, 'r', encoding='utf-8') as f:
                    soup = BeautifulSoup(f.read(), 'lxml')
                
                # Extrai o nome do bairro do <h1> (ex: "Vans Escolares no Alvorada")
                h1 = soup.find('h1')
                if h1:
                    bairro_nome = h1.get_text().replace('Vans Escolares no ', '').replace('Vans Escolares em ', '').strip()
                    bairro_slug = file.replace('.html', '')
                    
                    # Formata para o padrão APP_DATA (URL limpa com / no final)
                    entry = {
                        'cidade': CITY_PRETTY_NAMES[cidade_slug],
                        'bairro': bairro_nome,
                        'url': f'/{cidade_slug}/{bairro_slug}/'
                    }
                    lista_app_data.append(entry)

    return lista_app_data

def atualizar_index(novos_dados):
    with open(INDEX_PATH, 'r', encoding='utf-8') as f:
        content = f.read()

    # Formata a nova lista JS
    js_array_items = [f"  {{ cidade: '{d['cidade']}', bairro: '{d['bairro']}', url: '{d['url']}' }}" for d in novos_dados]
    novo_app_data_js = "const APP_DATA = [\n" + ",\n".join(js_array_items) + "\n];"

    # Substitui o bloco APP_DATA antigo pelo novo usando Regex
    pattern = r'const APP_DATA = \[.*?\];'
    new_content = re.sub(pattern, novo_app_data_js, content, flags=re.DOTALL)

    with open(INDEX_PATH, 'w', encoding='utf-8') as f:
        f.write(new_content)
    
    print(f"✅ Sucesso! {len(novos_dados)} bairros adicionados à busca no index.html.")

if __name__ == "__main__":
    dados = extrair_dados_busca()
    if dados:
        atualizar_index(dados)
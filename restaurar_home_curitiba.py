import os
from bs4 import BeautifulSoup

# Caminho para o arquivo que está com erro
FILE_PATH = r'C:\Users\marti\Documents\Projetos\vanescolarparana\index.html'

# Lista oficial de bairros de Curitiba para restaurar o grid
BAIRROS_CURITIBA = [
    "Abranches", "Água Verde", "Ahú", "Alto Boqueirão", "Alto da Glória", "Alto da Rua XV", 
    "Atuba", "Augusta", "Bacacheri", "Bairro Alto", "Barreirinha", "Batel", "Bigorrilho", 
    "Boa Vista", "Bom Retiro", "Boqueirão", "Butiatuvinha", "Cabral", "Cachoeira", 
    "Cajuru", "Campina do Siqueira", "Campo Comprido", "Campo de Santana", "Capão da Imbuia", 
    "Capão Raso", "Cascatinha", "Centro", "Centro Cívico", "Cidade Industrial", "Fanny", 
    "Fazendinha", "Ganchinho", "Guabirotuba", "Guaíra", "Hauer", "Hugo Lange", "Jardim Botânico", 
    "Jardim das Américas", "Jardim Social", "Juveve", "Lindoia", "Mercês", "Mossunguê", 
    "Novo Mundo", "Orleans", "Parolin", "Pascoalina", "Pilarzinho", "Pinheirinho", 
    "Portão", "Prado Velho", "Rebouças", "Riviera", "Santa Cândida", "Santa Felicidade", 
    "Santa Quitéria", "Santo Inácio", "São Braz", "São Francisco", "São João", 
    "São Lourenço", "Seminário", "Sítio Cercado", "Taboão", "Tarumã", "Tatuquara", 
    "Uberaba", "Umbará", "Vila Izabel", "Vista Alegre", "Xaxim"
]

def slugify(text):
    import unicodedata
    import re
    text = unicodedata.normalize('NFD', text).encode('ascii', 'ignore').decode('utf-8')
    return re.sub(r'[^a-z0-9-]', '', text.lower().replace(' ', '-'))

def restaurar_curitiba():
    if not os.path.exists(FILE_PATH):
        print("Arquivo não encontrado.")
        return

    with open(FILE_PATH, 'r', encoding='utf-8') as f:
        soup = BeautifulSoup(f.read(), 'lxml')

    # 1. Corrigir SEO Tags
    if soup.title:
        soup.title.string = "Vans Escolares em Curitiba - Van Escolar Paraná"
    
    meta_desc = soup.find('meta', attrs={'name': 'description'})
    if meta_desc:
        meta_desc['content'] = "Lista completa de transportadores escolares em Curitiba. Escolha o bairro e fale direto com o motorista."

    # 2. Corrigir H1 e Textos
    h1 = soup.find('h1')
    if h1:
        h1.string = "Vans em Curitiba"
    
    # 3. Restaurar o Grid de Bairros
    container = soup.find('div', class_='grid')
    if container:
        container.clear() # Remove os bairros de SJP
        
        for bairro in sorted(BAIRROS_CURITIBA):
            slug = slugify(bairro)
            # Cria o link padrão Curitiba
            link = soup.new_tag('a', href=f"/curitiba/{slug}/", attrs={'class': "group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md transition-all text-center"})
            span = soup.new_tag('span', class_="text-gray-800 font-bold group-hover:text-blue-600")
            span.string = bairro
            link.append(span)
            container.append(link)

    # 4. Salvar correção
    with open(FILE_PATH, 'w', encoding='utf-8') as f:
        f.write(soup.prettify())
    
    print("✅ Curitiba restaurada com sucesso! Bairros de SJP removidos.")

if __name__ == "__main__":
    restaurar_curitiba()
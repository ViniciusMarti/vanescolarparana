import os
import re
from bs4 import BeautifulSoup

def slugify(text):
    import unicodedata
    text = unicodedata.normalize('NFD', text).encode('ascii', 'ignore').decode('utf-8')
    return re.sub(r'[^a-z0-9-]', '', text.lower().replace(' ', '-'))

BAIRROS_CURITIBA = [
    "Abranches", "Água Verde", "Ahú", "Alto Boqueirão", "Alto da Glória", "Alto da Rua XV", 
    "Atuba", "Augusta", "Bacacheri", "Bairro Alto", "Barreirinha", "Batel", "Bigorrilho", 
    "Boa Vista", "Bom Retiro", "Boqueirão", "Butiatuvinha", "Cabral", "Cachoeira", 
    "Cajuru", "Campina do Siqueira", "Campo Comprido", "Campo de Santana", "Capão da Imbuia", 
    "Capão Raso", "Cascatinha", "Centro", "Centro Cívico", "Cidade Industrial", "Fanny", 
    "Fazendinha", "Ganchinho", "Guabirotuba", "Guaíra", "Hauer", "Hugo Lange", "Jardim Botânico", 
    "Jardim das Américas", "Jardim Social", "Juveve", "Lindoia", "Mercês", "Mossunguê", 
    "Novo Mundo", "Orleans", "Parolin", "Pilarzinho", "Pinheirinho", 
    "Portão", "Prado Velho", "Rebouças", "Riviera", "Santa Cândida", "Santa Felicidade", 
    "Santa Quitéria", "Santo Inácio", "São Braz", "São Francisco", "São João", 
    "São Lourenço", "Seminário", "Sítio Cercado", "Taboão", "Tarumã", "Tatuquara", 
    "Uberaba", "Umbará", "Vila Izabel", "Vista Alegre", "Xaxim"
]

path = r'c:\Users\marti\Documents\Repositório\vanescolarparana\curitiba\index.html'

with open(path, 'r', encoding='utf-8') as f:
    soup = BeautifulSoup(f.read(), 'html.parser')

# Update title and description
if soup.title:
    soup.title.string = "Vans Escolares em Curitiba - Van Escolar Paraná"

desc = soup.find('meta', attrs={'name': 'description'})
if desc:
    desc['content'] = "Lista completa de transportadores escolares em Curitiba. Escolha o bairro e fale direto com o motorista."

# Update canonical
canonical = soup.find('link', rel='canonical')
if canonical:
    canonical['href'] = "https://www.vanescolarparana.com/curitiba/"

# Update H1
h1 = soup.find('h1')
if h1:
    h1.string = "Vans em Curitiba"

# Clear existing grid and add Curitiba neighborhoods
grid = soup.find('div', class_=re.compile(r'grid'))
if grid:
    grid.clear()
    for bairro in sorted(BAIRROS_CURITIBA):
        slug = slugify(bairro)
        
        # Link tag
        a = soup.new_tag('a', href=f"/curitiba/{slug}", attrs={'class': "group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md transition-all text-center"})
        
        # Span tag
        span = soup.new_tag('span', class_="text-gray-800 font-bold group-hover:text-blue-600")
        span.string = bairro
        
        a.append(span)
        grid.append(a)

with open(path, 'w', encoding='utf-8') as f:
    f.write(str(soup))

print("curitiba/index.html fixed.")

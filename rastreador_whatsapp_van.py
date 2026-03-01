import os
import glob

# ==========================================
# 1. CONFIGURAÇÕES
# ==========================================
PASTA_RAIZ = os.getcwd() 

# Script JavaScript que detecta cliques em links do WhatsApp
# Ele envia o evento 'whatsapp_contact' com o nome da cidade (pasta)
CODIGO_RASTREIO_WA = """
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Busca todos os links que contêm wa.me ou whatsapp.com
  var linksWA = document.querySelectorAll('a[href*="wa.me"], a[href*="whatsapp.com"]');
  
  linksWA.forEach(function(link) {
    link.addEventListener('click', function() {
      // Pega o nome da cidade pela URL (ex: /curitiba/ -> curitiba)
      var pathParts = window.location.pathname.split('/');
      var cidade = pathParts[1] || 'home';
      
      // Envia o evento para o GA4
      gtag('event', 'whatsapp_contact', {
        'event_category': 'conversion',
        'event_label': link.href,
        'city': cidade,
        'page_location': window.location.href
      });
      
      console.log('Conversão WhatsApp registrada para a cidade: ' + cidade);
    });
  });
});
</script>
"""

# ==========================================
# 2. INJEÇÃO RECURSIVA
# ==========================================
print(f"Injetando rastreador de WhatsApp em: {PASTA_RAIZ}")
contador = 0

for arquivo in glob.glob(os.path.join(PASTA_RAIZ, "**", "*.html"), recursive=True):
    if ".git" in arquivo: continue

    with open(arquivo, 'r', encoding='utf-8') as f:
        conteudo = f.read()

    # Verifica se o rastreador já existe
    if 'whatsapp_contact' not in conteudo:
        # Injeta antes de fechar o body para não atrasar o carregamento da página
        if '</body>' in conteudo:
            novo_conteudo = conteudo.replace('</body>', f'{CODIGO_RASTREIO_WA}\n</body>')
            
            with open(arquivo, 'w', encoding='utf-8') as f:
                f.write(novo_conteudo)
            
            contador += 1

print(f"\n🚀 Sucesso! Rastreador de WhatsApp instalado em {contador} páginas.")
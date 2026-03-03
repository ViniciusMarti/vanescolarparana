import os
import re

# Configuração de caminhos
base_path = r'C:\Users\marti\Documents\Projetos\vanescolarparana'
# Pastas que contêm páginas de bairros
pastas_cidades = ['curitiba', 'sao-jose-dos-pinhais', 'londrina', 'maringa', 'ponta-grossa', 'pinhais', 'foz-do-iguaçu']

def corrigir_htmls():
    # Regex para encontrar o bloco do Header (do <header ao </header>)
    # O modificador (?s) faz o ponto (.) aceitar quebras de linha
    header_pattern = re.compile(r'<header.*?</header>', re.DOTALL)

    for cidade in pastas_cidades:
        caminho_cidade = os.path.join(base_path, cidade)
        if not os.path.exists(caminho_cidade):
            continue

        print(f"Processando cidade: {cidade}...")

        for arquivo in os.listdir(caminho_cidade):
            if arquivo.endswith(".html"):
                caminho_arquivo = os.path.join(caminho_cidade, arquivo)
                
                with open(caminho_arquivo, 'r', encoding='utf-8') as f:
                    content = f.read()

                # Encontra todas as ocorrências de <header>...</header>
                matches = list(header_pattern.finditer(content))

                if len(matches) > 1:
                    print(f"  [!] Duplicidade encontrada em: {arquivo}")
                    
                    # Mantemos o primeiro (index 0) e removemos os outros.
                    # Vamos reconstruir o HTML mantendo tudo ANTES da 2ª ocorrência
                    # e tudo DEPOIS da 2ª ocorrência.
                    
                    # Posição de início e fim da segunda ocorrência
                    start_second = matches[1].start()
                    end_second = matches[1].end()
                    
                    # Removemos o bloco e o excesso de espaços/quebras de linha ao redor
                    new_content = content[:start_second] + content[end_second:]
                    
                    # Salva a correção
                    with open(caminho_arquivo, 'w', encoding='utf-8') as f:
                        f.write(new_content)
                else:
                    # Se não tiver duplicata, não faz nada
                    pass

    print("\n--- Correção Finalizada! ---")

if __name__ == "__main__":
    corrigir_htmls()
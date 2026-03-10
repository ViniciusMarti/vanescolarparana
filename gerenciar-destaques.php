<?php
session_start();
require_once __DIR__ . '/config/db.php';

/**
 * CONFIGURAÇÃO
 */
$senha_mestra = "N*tUdaTZDU5wbz"; 

// 1. Migração Automática / Detector de Tabelas Robusto
try {
    $stmt = $pdo->query("SHOW TABLES");
    $all_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $table_main = 'vans'; // Default
    
    // Lista de candidatas
    $candidates = ['u582732852_vans_curitiba', 'vans']; 
    foreach($all_tables as $t) if(!in_array($t, $candidates)) $candidates[] = $t;

    foreach ($candidates as $candidate) {
        if (!in_array($candidate, $all_tables)) continue;
        
        // Verifica se esta tabela tem a coluna necessária
        $check = $pdo->query("SHOW COLUMNS FROM `$candidate` LIKE 'bairro_referencia'");
        if ($check->fetch()) {
            $table_main = $candidate;
            break;
        }
    }

    // Criar tabela de destaques se não existir
    $pdo->exec("CREATE TABLE IF NOT EXISTS `destaques_premium` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `permissionario` VARCHAR(255) NOT NULL,
        `prefixo` VARCHAR(50),
        `bairro` VARCHAR(255) NOT NULL,
        `vencimento` DATE NOT NULL,
        `valor_pago` DECIMAL(10,2) DEFAULT 0.00,
        `data_pagamento` DATE DEFAULT NULL,
        `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`permissionario`),
        INDEX (`bairro`),
        INDEX (`vencimento`),
        INDEX (`data_pagamento`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Verificar se as colunas novas existem (caso a tabela já existisse)
    $pdo->exec("ALTER TABLE `destaques_premium` ADD COLUMN IF NOT EXISTS `valor_pago` DECIMAL(10,2) DEFAULT 0.00;");
    $pdo->exec("ALTER TABLE `destaques_premium` ADD COLUMN IF NOT EXISTS `data_pagamento` DATE DEFAULT NULL;");
    $pdo->exec("ALTER TABLE `destaques_premium` ADD COLUMN IF NOT EXISTS `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP;");
} catch (Exception $e) {}

// Login / Logout
if (isset($_POST['login'])) {
    if ($_POST['password'] === $senha_mestra) $_SESSION['admin_logged'] = true;
    else $error = "Senha incorreta!";
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: gerenciar-destaques.php");
    exit;
}

if (!isset($_SESSION['admin_logged'])) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Gestão de Destaques</title>
        <link href="/icone-favicon.png" rel="icon" type="image/png" />
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    </head>
    <body class="bg-slate-100 flex items-center justify-center h-screen px-4 font-['Inter']">
        <div class="bg-white p-10 rounded-[2.5rem] shadow-2xl w-full max-w-md border border-white">
            <div class="text-center mb-8">
                <img src="/logo-comum.png" alt="Logo" class="h-16 mx-auto mb-6">
                <h1 class="text-3xl font-black text-slate-800">Destaques</h1>
                <p class="text-slate-500 mt-2 font-medium">Gestão Premium por Bairro</p>
            </div>
            <?php if (isset($error)) echo "<div class='bg-red-50 text-red-600 p-4 rounded-2xl mb-6 text-center font-bold border border-red-100'>$error</div>"; ?>
            <form method="POST" class="space-y-6">
                <div><label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Senha</label>
                <input type="password" name="password" placeholder="••••••••" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl outline-none" autofocus></div>
                <button type="submit" name="login" class="w-full bg-blue-600 text-white p-5 rounded-2xl font-black text-lg hover:bg-blue-700 transition-all">Acessar</button>
            </form>
        </div>
    </body>
    </html>
    <?php exit;
}

// AÇÕES
$success = "";
$error_msg = "";

// 1. Adicionar/Atualizar Destaque
if (isset($_POST['add_destaque'])) {
    $perm = $_POST['permissionario'];
    $pref = $_POST['prefixo'];
    $bairros = $_POST['bairros'] ?? [];
    $periodo = isset($_POST['periodo']) ? (int)$_POST['periodo'] : 0;
    $bonus_percent = isset($_POST['bonus']) ? (int)$_POST['bonus'] : 0;
    $valor_total = (float)($_POST['valor_pago'] ?? 0);
    $data_pagto = $_POST['data_pagamento'] ?: date('Y-m-d');

    if (empty($bairros)) {
        $error_msg = "Selecione pelo menos um bairro!";
    } elseif ($periodo <= 0) {
        $error_msg = "Período inválido!";
    } else {
        try {
            $dias_adicionais = floor($periodo * (1 + ($bonus_percent / 100)));
            $valor_por_bairro = $valor_total / count($bairros);
            
            foreach ($bairros as $b) {
                $check_stmt = $pdo->prepare("SELECT vencimento FROM `destaques_premium` WHERE permissionario = ? AND bairro = ? AND vencimento >= CURDATE() ORDER BY vencimento DESC LIMIT 1");
                $check_stmt->execute([$perm, $b]);
                $atual = $check_stmt->fetch();
                
                $base_data = $atual ? new DateTime($atual['vencimento']) : new DateTime();
                $base_data->modify("+$dias_adicionais days");
                $nova_data = $base_data->format('Y-m-d');

                $ins = $pdo->prepare("INSERT INTO `destaques_premium` (permissionario, prefixo, bairro, vencimento, valor_pago, data_pagamento) VALUES (?, ?, ?, ?, ?, ?)");
                $ins->execute([$perm, $pref, $b, $nova_data, $valor_por_bairro, $data_pagto]);
            }
            $success = "Venda registrada! Ganhos: R$ " . number_format($valor_total, 2, ',', '.') . " | +$dias_adicionais dias em " . count($bairros) . " bairros.";
        } catch (Exception $e) { $error_msg = "Erro: " . $e->getMessage(); }
    }
}

// 2. Remover Destaque Individual
if (isset($_GET['delete_id'])) {
    $pdo->prepare("DELETE FROM `destaques_premium` WHERE id = ?")->execute([$_GET['delete_id']]);
    $success = "Destaque removido com sucesso.";
}

// BUSCA
$search = $_GET['s'] ?? '';
$search_results = [];
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM `$table_main` WHERE `permissionario` LIKE :s OR `prefixo` LIKE :s OR `bairro_referencia` LIKE :s LIMIT 15");
    $stmt->execute(['s' => "%$search%"]);
    $search_results = $stmt->fetchAll();
}

// LISTAGEM ATIVA
$ativos = $pdo->query("SELECT * FROM `destaques_premium` WHERE `vencimento` >= CURDATE() ORDER BY `vencimento` ASC")->fetchAll();
$aba = $_GET['aba'] ?? 'buscar';

// Dados para Relatório
$hoje = date('Y-m-d');
$mes_atual = date('Y-m');
$stats = $pdo->query("SELECT 
    SUM(valor_pago) as total,
    SUM(CASE WHEN data_pagamento LIKE '$mes_atual%' THEN valor_pago ELSE 0 END) as mes_atual,
    COUNT(DISTINCT permissionario) as total_clientes
FROM `destaques_premium`")->fetch();

$ultimas_vendas = $pdo->query("SELECT * FROM `destaques_premium` ORDER BY id DESC LIMIT 20")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Destaques ⭐ Van Escolar Paraná</title>
    <link href="/icone-favicon.png" rel="icon" type="image/png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen">

    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 h-20 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⭐</span>
                <span class="font-black text-xl tracking-tight text-slate-800 uppercase">Gestão <span class="text-blue-600">Destaques</span></span>
            </div>
            <div class="flex items-center gap-4">
                <nav class="flex bg-slate-100 p-1 rounded-xl mr-6">
                    <a href="?aba=buscar" class="px-5 py-2 rounded-lg text-sm font-bold transition-all <?php echo $aba == 'buscar' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-800'; ?>">Nova Venda</a>
                    <a href="?aba=ativos" class="px-5 py-2 rounded-lg text-sm font-bold transition-all <?php echo $aba == 'ativos' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-800'; ?>">Bairros Ativos (<?php echo count($ativos); ?>)</a>
                    <a href="?aba=relatorio" class="px-5 py-2 rounded-lg text-sm font-bold transition-all <?php echo $aba == 'relatorio' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500 hover:text-slate-800'; ?>">Relatórios 📊</a>
                </nav>
                <a href="?logout=1" class="text-xs font-bold text-slate-400 hover:text-red-500">Sair</a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 max-w-5xl py-12">
        
        <?php if ($success): ?><div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-2xl mb-8 flex items-center gap-3"><span>✅</span><p class="font-bold"><?php echo $success; ?></p></div><?php endif; ?>
        <?php if ($error_msg): ?><div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-2xl mb-8 flex items-center gap-3"><span>⚠️</span><p class="font-bold"><?php echo $error_msg; ?></p></div><?php endif; ?>

        <?php if ($aba == 'buscar'): ?>
            <section class="bg-white p-10 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-white transition-all">
                <div class="mb-10 text-center">
                    <h2 class="text-3xl font-black text-slate-800">Novo Destaque</h2>
                    <p class="text-slate-500 font-medium">Busque o motorista para definir os bairros e prazos.</p>
                </div>

                <form method="GET" class="relative max-w-2xl mx-auto mb-12">
                    <input type="hidden" name="aba" value="buscar">
                    <input type="text" name="s" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nome, Prefixo ou Bairro..." 
                           class="w-full p-6 pl-8 bg-slate-50 border border-slate-100 rounded-3xl outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all text-xl font-medium pr-32" autofocus>
                    <button type="submit" class="absolute right-3 top-3 bottom-3 px-8 bg-blue-600 text-white rounded-2xl font-bold hover:bg-blue-700 active:scale-95 transition-all">Buscar</button>
                </form>

                <?php if (!empty($search_results)): ?>
                    <div class="grid grid-cols-1 gap-6">
                        <?php foreach ($search_results as $res): 
                            $bairros_raw = $res['bairro_referencia'] ?? '';
                            if (is_string($bairros_raw) && $bairros_raw !== '') {
                                $bairros_lista = array_map('trim', explode(',', $bairros_raw));
                            } else {
                                $bairros_lista = [];
                            }
                            $bairros_lista = array_filter($bairros_lista);
                        ?>
                            <div class="p-8 bg-slate-50 rounded-[2rem] border border-slate-100">
                                <form method="POST">
                                    <input type="hidden" name="permissionario" value="<?php echo htmlspecialchars($res['permissionario']); ?>">
                                    <input type="hidden" name="prefixo" value="<?php echo htmlspecialchars($res['prefixo']); ?>">
                                    
                                    <div class="flex flex-col md:flex-row justify-between gap-6 mb-8 pb-8 border-b border-slate-200">
                                        <div>
                                            <h3 class="text-2xl font-black text-slate-800"><?php echo htmlspecialchars($res['permissionario']); ?></h3>
                                            <p class="text-slate-500 font-bold uppercase text-[10px] tracking-widest mt-1">Prefixo: <?php echo htmlspecialchars($res['prefixo'] ?: '---'); ?></p>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-4">
                                            <div class="flex flex-col">
                                                <label class="text-[10px] font-black text-slate-400 uppercase mb-1">Período</label>
                                                <select name="periodo" class="p-4 bg-white border border-slate-200 rounded-2xl font-bold shadow-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                                    <option value="7">7 Dias</option>
                                                    <option value="15">15 Dias</option>
                                                    <option value="30" selected>30 Dias (Mensal)</option>
                                                    <option value="365">365 Dias (Anual)</option>
                                                </select>
                                            </div>
                                            <div class="flex flex-col">
                                                <label class="text-[10px] font-black text-slate-400 uppercase mb-1">Bônus Extra</label>
                                                <select name="bonus" class="p-4 bg-white border border-slate-200 rounded-2xl font-bold shadow-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                                    <option value="0" selected>Sem Bônus</option>
                                                    <option value="10">10% de Bônus</option>
                                                    <option value="20">20% de Bônus</option>
                                                    <option value="30">30% de Bônus (Máx)</option>
                                                </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                        <div class="flex flex-col">
                                            <label class="text-[10px] font-black text-slate-400 uppercase mb-1">Valor Total Pago (R$)</label>
                                            <input type="number" step="0.01" name="valor_pago" required placeholder="0,00" class="p-4 bg-white border border-slate-200 rounded-2xl font-black text-blue-600 text-lg shadow-sm outline-none">
                                        </div>
                                        <div class="flex flex-col">
                                            <label class="text-[10px] font-black text-slate-400 uppercase mb-1">Data do Pagamento</label>
                                            <input type="date" name="data_pagamento" value="<?php echo date('Y-m-d'); ?>" class="p-4 bg-white border border-slate-200 rounded-2xl font-bold shadow-sm outline-none">
                                        </div>
                                    </div>

                                    <div class="mb-8">
                                        <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4">Escolha os bairros para destacar:</p>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                            <?php foreach ($bairros_lista as $b): ?>
                                                <label class="flex items-center gap-3 p-4 bg-white border border-slate-200 rounded-2xl cursor-pointer hover:border-blue-500 transition-all select-none">
                                                    <input type="checkbox" name="bairros[]" value="<?php echo htmlspecialchars($b); ?>" class="w-5 h-5 rounded-lg border-2 border-slate-300 text-blue-600 transition-all cursor-pointer">
                                                    <span class="text-sm font-bold text-slate-700"><?php echo htmlspecialchars($b); ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <button type="submit" name="add_destaque" class="w-full bg-slate-900 text-white p-5 rounded-2xl font-black text-lg hover:bg-blue-600 transition-all active:scale-[0.98]">Confirmar Destaque</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($search): ?>
                    <p class="text-center py-20 text-slate-400 font-bold">Nenhum motorista encontrado para esta busca.</p>
                <?php endif; ?>
            </section>

        <?php elseif ($aba == 'relatorio'): ?>
            <section class="space-y-8">
                <!-- Dashboard Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-white">
                        <p class="text-slate-400 font-black text-[10px] uppercase tracking-widest mb-2">Total Histórico</p>
                        <h3 class="text-4xl font-black text-slate-800">R$ <?php echo number_format($stats['total'] ?: 0, 2, ',', '.'); ?></h3>
                    </div>
                    <div class="bg-blue-600 p-8 rounded-[2.5rem] shadow-xl shadow-blue-100 border border-blue-500">
                        <p class="text-blue-200 font-black text-[10px] uppercase tracking-widest mb-2">Ganhos no Mês</p>
                        <h3 class="text-4xl font-black text-white">R$ <?php echo number_format($stats['mes_atual'] ?: 0, 2, ',', '.'); ?></h3>
                    </div>
                    <div class="bg-slate-900 p-8 rounded-[2.5rem] shadow-xl border border-slate-800">
                        <p class="text-slate-400 font-black text-[10px] uppercase tracking-widest mb-2">Clientes Únicos</p>
                        <h3 class="text-4xl font-black text-white"><?php echo $stats['total_clientes']; ?></h3>
                    </div>
                </div>

                <!-- Lista de Transações -->
                <div class="bg-white p-10 rounded-[2.5rem] shadow-xl border border-white">
                    <h2 class="text-2xl font-black text-slate-800 mb-8">Últimas Ativações</h2>
                    <div class="overflow-hidden border border-slate-100 rounded-3xl">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="p-6 text-[10px] font-black text-slate-400 uppercase">Data Pagto</th>
                                    <th class="p-6 text-[10px] font-black text-slate-400 uppercase">Motorista / Bairro</th>
                                    <th class="p-6 text-[10px] font-black text-slate-400 uppercase text-right">Valor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <?php foreach ($ultimas_vendas as $v): ?>
                                    <tr class="hover:bg-slate-50 transition-all">
                                        <td class="p-6 text-sm font-bold text-slate-500"><?php echo date('d/m/Y', strtotime($v['data_pagamento'])); ?></td>
                                        <td class="p-6">
                                            <p class="font-black text-slate-800 leading-none"><?php echo htmlspecialchars($v['permissionario']); ?></p>
                                            <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase"><?php echo htmlspecialchars($v['bairro']); ?></p>
                                        </td>
                                        <td class="p-6 text-right font-black text-blue-600 text-lg">R$ <?php echo number_format($v['valor_pago'], 2, ',', '.'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        <?php else: // ABA ATIVOS ?>
            <section class="bg-white p-10 rounded-[2.5rem] shadow-xl border border-white">
                <div class="mb-12 flex justify-between items-end">
                    <div>
                        <h2 class="text-3xl font-black text-slate-800">Destaques Ativos</h2>
                        <p class="text-slate-500 font-medium">Todos os motoristas que aparecem no topo atualmente.</p>
                    </div>
                    <div class="bg-blue-50 text-blue-600 px-6 py-2 rounded-full font-black text-sm uppercase tracking-widest"><?php echo count($ativos); ?> Itens</div>
                </div>

                <?php if (empty($ativos)): ?>
                    <div class="text-center py-32 bg-slate-50 rounded-[2rem] border border-dashed border-slate-200">
                        <span class="text-6xl mb-6 block">📭</span>
                        <p class="text-slate-500 font-black text-xl">Não há nenhum destaque ativo no momento.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-hidden border border-slate-100 rounded-3xl">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    <th class="p-6">Motorista</th>
                                    <th class="p-6">Bairro</th>
                                    <th class="p-6 text-center">Vencimento</th>
                                    <th class="p-6 text-right">Ação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($ativos as $d): 
                                    $hoje = new DateTime();
                                    $venci = new DateTime($d['vencimento']);
                                    $diff = $hoje->diff($venci)->days;
                                    $status_class = $diff <= 3 ? 'text-orange-500 bg-orange-50' : 'text-green-600 bg-green-50';
                                ?>
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="p-6">
                                            <p class="font-black text-slate-800 leading-tight"><?php echo htmlspecialchars($d['permissionario']); ?></p>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">Ref: <?php echo htmlspecialchars($d['prefixo']); ?></p>
                                        </td>
                                        <td class="p-6 font-bold text-slate-600"><?php echo htmlspecialchars($d['bairro']); ?></td>
                                        <td class="p-6 text-center">
                                            <span class="px-4 py-2 rounded-xl text-xs font-black inline-block <?php echo $status_class; ?>">
                                                <?php echo date('d/m/y', strtotime($d['vencimento'])); ?>
                                            </span>
                                        </td>
                                        <td class="p-6 text-right">
                                            <a href="?aba=ativos&delete_id=<?php echo $d['id']; ?>" onclick="return confirm('Tem certeza que deseja remover este destaque?')" 
                                               class="text-red-400 hover:text-red-600 font-black text-[10px] uppercase tracking-tighter opacity-0 group-hover:opacity-100 transition-all">Excluir</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

    </main>

</body>
</html>

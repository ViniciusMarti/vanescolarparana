<?php
session_start();
require_once __DIR__ . '/config/db.php';

/**
 * CONFIGURAÇÃO
 * Mude a senha abaixo para sua segurança!
 */
$senha_mestra = "parana2026"; 

if (isset($_POST['login'])) {
    if ($_POST['password'] === $senha_mestra) {
        $_SESSION['admin_logged'] = true;
    } else {
        $error = "Senha incorreta!";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: gerenciar-destaques.php");
    exit;
}

// Detector de tabela e coluna (auto-correção)
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $table_name = 'vans';
    if (!in_array('vans', $tables) && !empty($tables)) {
        if (in_array('u582732852_vans_curitiba', $tables)) {
            $table_name = 'u582732852_vans_curitiba';
        } else {
            $table_name = $tables[0];
        }
    }
    
    // Tenta adicionar a coluna premium_until se não existir
    $stmt = $pdo->query("SHOW COLUMNS FROM `$table_name` LIKE 'premium_until'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE `$table_name` ADD COLUMN `premium_until` DATE DEFAULT NULL");
    }
} catch (Exception $e) {}

// Proteção da página
if (!isset($_SESSION['admin_logged'])) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Van Escolar Paraná</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
        <style>body { font-family: 'Inter', sans-serif; }</style>
    </head>
    <body class="bg-slate-100 flex items-center justify-center h-screen px-4">
        <div class="bg-white p-10 rounded-[2.5rem] shadow-2xl w-full max-w-md border border-white">
            <div class="text-center mb-8">
                <div class="bg-blue-600 w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-200">
                    <span class="text-4xl text-white">⭐</span>
                </div>
                <h1 class="text-3xl font-black text-slate-800">Destaques</h1>
                <p class="text-slate-500 mt-2 font-medium">Painel de gerenciamento premium</p>
            </div>
            
            <?php if (isset($error)) echo "<div class='bg-red-50 text-red-600 p-4 rounded-2xl mb-6 text-center font-bold border border-red-100'>$error</div>"; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 ml-1">Senha de Acesso</label>
                    <input type="password" name="password" placeholder="••••••••" class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all text-lg" autofocus>
                </div>
                <button type="submit" name="login" class="w-full bg-blue-600 text-white p-5 rounded-2xl font-black text-lg hover:bg-blue-700 hover:shadow-xl hover:shadow-blue-200 active:scale-[0.98] transition-all">Acessar Painel</button>
            </form>
            
            <p class="text-center mt-8 text-slate-400 text-sm font-medium">Van Escolar Paraná &copy; <?php echo date('Y'); ?></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Lógica de atualização de destaque
if (isset($_POST['update_premium'])) {
    $permissionario = $_POST['permissionario'];
    $date = $_POST['premium_until'];
    
    // Se a data estiver vazia, removemos o destaque (null)
    if (empty($date)) $date = null;
    
    try {
        $stmt = $pdo->prepare("UPDATE `$table_name` SET `premium_until` = :date WHERE `permissionario` = :perm");
        $stmt->execute(['date' => $date, 'perm' => $permissionario]);
        $success = "O motorista <strong>$permissionario</strong> agora está em destaque!";
        if (!$date) $success = "Destaque removido com sucesso de <strong>$permissionario</strong>.";
    } catch (Exception $e) {
        $error = "Erro ao atualizar: " . $e->getMessage();
    }
}

// Busca de motoristas
$search = $_GET['s'] ?? '';
$results = [];
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM `$table_name` WHERE `permissionario` LIKE :s OR `prefixo` LIKE :s OR `bairro_referencia` LIKE :s LIMIT 10");
    $stmt->execute(['s' => "%$search%"]);
    $results = $stmt->fetchAll();
}

// Destaques ativos no momento
$destaques = $pdo->query("SELECT * FROM `$table_name` WHERE `premium_until` >= CURDATE() ORDER BY `premium_until` ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Destaques ⭐ Van Escolar Paraná</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-[#f8fafc] min-h-screen pb-20">

    <header class="glass sticky top-0 z-50 border-b border-slate-200">
        <div class="container mx-auto px-6 h-20 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⭐</span>
                <span class="font-black text-xl tracking-tight text-slate-800">VAN ESCOLAR <span class="text-blue-600">ADMIN</span></span>
            </div>
            <a href="?logout=1" class="px-6 py-2 bg-slate-100 text-slate-600 rounded-full font-bold text-sm hover:bg-red-50 hover:text-red-500 transition-all">Sair do Painel</a>
        </div>
    </header>

    <div class="container mx-auto px-6 max-w-5xl mt-12">
        
        <div class="mb-12">
            <h1 class="text-4xl font-black text-slate-900 leading-tight">Controle de <span class="text-blue-600">Destaques</span></h1>
            <p class="text-slate-500 font-medium text-lg mt-2">Destaque motoristas nas páginas de bairro e resultados de busca.</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 p-6 rounded-[2rem] mb-10 flex items-center gap-4 animate-bounce-short">
                <span class="text-2xl">✅</span>
                <p class="font-medium"><?php echo $success; ?></p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            
            <!-- Coluna de Busca e Adição -->
            <div class="lg:col-span-2 space-y-8">
                <section class="bg-white p-10 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-white">
                    <h2 class="text-2xl font-black mb-8 text-slate-800">Novo Destaque</h2>
                    <form method="GET" class="relative group">
                        <input type="text" name="s" value="<?php echo htmlspecialchars($search); ?>" placeholder="Quem você quer destacar? (Nome, Prefixo...)" 
                               class="w-full p-6 bg-slate-50 border border-slate-100 rounded-3xl outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500 transition-all text-lg font-medium pr-32">
                        <button type="submit" class="absolute right-3 top-3 bottom-3 px-8 bg-blue-600 text-white rounded-2xl font-bold hover:bg-blue-700 transition-all active:scale-95 shadow-lg shadow-blue-100">Buscar</button>
                    </form>

                    <?php if (!empty($results)): ?>
                        <div class="mt-10 space-y-4">
                            <p class="text-sm font-bold text-slate-400 uppercase tracking-widest ml-1 mb-4">Resultados encontrados:</p>
                            <?php foreach ($results as $res): ?>
                                <div class="bg-slate-50 p-6 rounded-3xl flex flex-col md:flex-row md:items-center justify-between gap-6 border border-slate-100 hover:border-blue-200 transition-colors">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-black text-lg text-slate-800"><?php echo htmlspecialchars($res['permissionario']); ?></p>
                                            <?php if ($res['premium_until'] >= date('Y-m-d')): ?>
                                                <span class="bg-blue-100 text-blue-600 text-[10px] font-black px-2 py-0.5 rounded-full">ATIVO</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-sm text-slate-500 font-medium">Prefixo: <span class="text-slate-700"><?php echo htmlspecialchars($res['prefixo'] ?: '---'); ?></span> | Bairro: <span class="text-slate-700"><?php echo htmlspecialchars($res['bairro_referencia']); ?></span></p>
                                    </div>
                                    <form method="POST" class="flex flex-wrap items-center gap-3">
                                        <input type="hidden" name="permissionario" value="<?php echo htmlspecialchars($res['permissionario']); ?>">
                                        <div class="relative">
                                            <input type="date" name="premium_until" value="<?php echo $res['premium_until']; ?>" 
                                                   class="p-4 bg-white border border-slate-200 rounded-2xl outline-none text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <button type="submit" name="update_premium" class="bg-slate-900 text-white px-6 p-4 rounded-2xl font-bold text-sm hover:bg-blue-600 transition-all active:scale-95">Salvar</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (!empty($search)): ?>
                        <div class="mt-10 text-center py-10 bg-slate-50 rounded-3xl border border-dashed border-slate-200">
                            <span class="text-4xl mb-4 block">🔍</span>
                            <p class="text-slate-500 font-bold">Nenhum motorista encontrado.</p>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <!-- Coluna de Ativos -->
            <div class="space-y-8">
                <section class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-white h-fit">
                    <h2 class="text-xl font-black mb-8 text-slate-800 flex items-center gap-3">
                        Destaques Agora
                        <span class="bg-blue-600 text-white text-[10px] w-5 h-5 flex items-center justify-center rounded-full"><?php echo count($destaques); ?></span>
                    </h2>
                    
                    <?php if (empty($destaques)): ?>
                        <div class="text-center py-8">
                             <p class="text-slate-400 font-medium">Ninguém pagou ainda? <br>Que triste! 😅</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($destaques as $d): ?>
                                <div class="group p-5 rounded-3xl border border-slate-100 hover:bg-slate-50 transition-all relative">
                                    <p class="font-bold text-slate-800 truncate pr-8"><?php echo htmlspecialchars($d['permissionario']); ?></p>
                                    <div class="flex items-center justify-between mt-2">
                                        <p class="text-[11px] font-bold text-blue-600 uppercase">Vence em: <?php echo date('d/m/Y', strtotime($d['premium_until'])); ?></p>
                                        <form method="POST" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                            <input type="hidden" name="permissionario" value="<?php echo htmlspecialchars($d['permissionario']); ?>">
                                            <input type="hidden" name="premium_until" value="">
                                            <button type="submit" name="update_premium" class="text-red-500 text-[10px] font-black hover:underline">REMOVER</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-8 pt-6 border-t border-slate-100">
                        <p class="text-xs text-slate-400 leading-relaxed">Os destaques aparecem automaticamente no topo das listas de bairro até a data de expiração definida.</p>
                    </div>
                </section>
            </div>

        </div>

    </div>
</body>
</html>

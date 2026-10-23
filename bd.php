<?php
/**
 * Listo - Arquivo de Conexão com o Banco de Dados (bd.php)
 * Conexão segura utilizando PDO com prepared statements contra SQL Injections.
 *
 * Suporta primariamente MySQL (conforme requisitos do projeto para XAMPP / InfinityFree / Wuaze)
 * e inclui fallback automático com SQLite local para permitir testes imediatos em localhost
 * mesmo quando o serviço MySQL não estiver ativo na máquina.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações do MySQL (Padrão para XAMPP ou Hospedagem InfinityFree / Wuaze)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'listo');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Retorna a instância única do PDO para toda a aplicação
 *
 * @return PDO
 */
function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        // Tentar primeiro conexão com MySQL
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $_SESSION['db_driver_used'] = 'mysql';
        } catch (PDOException $e) {
            // Se falhar a conexão com MySQL (ex: serviço MySQL offline no localhost),
            // ativar emulador SQLite local para permitir testes completos imediatos
            $sqlitePath = __DIR__ . '/database.sqlite';
            $isNewDb = !file_exists($sqlitePath);

            try {
                $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);

                // Registrar funções compatíveis com MySQL no SQLite
                $pdo->sqliteCreateFunction('NOW', fn() => date('Y-m-d H:i:s'));
                $pdo->sqliteCreateFunction('RAND', fn() => mt_rand() / mt_getrandmax());

                $_SESSION['db_driver_used'] = 'sqlite';

                if ($isNewDb) {
                    inicializarBancoSQLite($pdo);
                }
            } catch (Exception $e2) {
                // Em caso de falha em ambos os bancos, exibir tela diagnóstica
                $errorMsg = "MySQL: " . $e->getMessage() . " | SQLite: " . $e2->getMessage();
                include_once __DIR__ . '/includes/db_error.php';
                exit;
            }
        }
    }

    return $pdo;
}

/**
 * Cria tabelas e insere dados demonstrativos no SQLite para emulação local
 */
function inicializarBancoSQLite(PDO $pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            senha TEXT NOT NULL,
            avatar_cor TEXT DEFAULT 'indigo',
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS grupos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            descricao TEXT,
            codigo_convite TEXT NOT NULL UNIQUE,
            criado_por INTEGER NOT NULL,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS membros_grupo (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            grupo_id INTEGER NOT NULL,
            usuario_id INTEGER NOT NULL,
            papel TEXT DEFAULT 'membro',
            entrou_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE (grupo_id, usuario_id),
            FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS listas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            titulo TEXT NOT NULL,
            descricao TEXT,
            tipo TEXT DEFAULT 'tarefas',
            icone TEXT DEFAULT 'check-square',
            cor TEXT DEFAULT 'indigo',
            usuario_id INTEGER NOT NULL,
            grupo_id INTEGER,
            status TEXT DEFAULT 'ativa',
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE SET NULL
        );

        CREATE TABLE IF NOT EXISTS itens_lista (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            lista_id INTEGER NOT NULL,
            titulo TEXT NOT NULL,
            concluido INTEGER DEFAULT 0,
            ordem INTEGER DEFAULT 0,
            quantidade REAL DEFAULT 1.00,
            unidade TEXT DEFAULT 'un',
            preco_estimado REAL DEFAULT 0.00,
            preco_real REAL DEFAULT 0.00,
            genero TEXT,
            plataforma TEXT,
            nota INTEGER,
            rendimento TEXT,
            tempo_preparo TEXT,
            ingredientes TEXT,
            modo_preparo TEXT,
            prioridade TEXT DEFAULT 'media',
            data_limite TEXT,
            notas TEXT,
            status TEXT DEFAULT 'ativo',
            criado_por INTEGER NOT NULL,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (lista_id) REFERENCES listas(id) ON DELETE CASCADE,
            FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE CASCADE
        );
    ");

    // Inserir Usuários de Demonstração (Senha: 123456)
    $hashSenha = password_hash('123456', PASSWORD_DEFAULT);
    $stmtUser = $pdo->prepare("INSERT OR IGNORE INTO usuarios (id, nome, email, senha, avatar_cor) VALUES (?, ?, ?, ?, ?)");
    $stmtUser->execute([1, 'Usuário Demonstração', 'demo@listo.com', $hashSenha, 'indigo']);
    $stmtUser->execute([2, 'Maria Silva', 'maria@listo.com', $hashSenha, 'emerald']);

    // Grupo de Exemplo
    $stmtGrupo = $pdo->prepare("INSERT OR IGNORE INTO grupos (id, nome, descricao, codigo_convite, criado_por) VALUES (?, ?, ?, ?, ?)");
    $stmtGrupo->execute([1, 'Família Silva', 'Lista compartilhada de compras e tarefas da casa', 'SILVA2026', 1]);

    $stmtMg = $pdo->prepare("INSERT OR IGNORE INTO membros_grupo (grupo_id, usuario_id, papel) VALUES (?, ?, ?)");
    $stmtMg->execute([1, 1, 'admin']);
    $stmtMg->execute([1, 2, 'membro']);

    // Listas de Exemplo
    $stmtList = $pdo->prepare("INSERT OR IGNORE INTO listas (id, titulo, descricao, tipo, icone, cor, usuario_id, grupo_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtList->execute([1, 'Compras da Semana', 'Supermercado e feira da casa', 'supermercado', 'shopping-cart', 'emerald', 1, 1, 'ativa']);
    $stmtList->execute([2, 'Maratona de Filmes e Séries', 'O que queremos assistir no fim de semana', 'filmes', 'film', 'rose', 1, 1, 'ativa']);
    $stmtList->execute([3, 'Receitas Favoritas', 'Cardápio e receitas testadas da família', 'receitas', 'utensils', 'amber', 1, 1, 'ativa']);
    $stmtList->execute([4, 'Tarefas da Casa & Trabalho', 'Organização de prioridades da semana', 'tarefas', 'check-square', 'indigo', 1, null, 'ativa']);
    $stmtList->execute([5, 'Mala de Viagem - Praia', 'Checklist essencial para as férias', 'viagens', 'plane', 'sky', 1, null, 'ativa']);

    // Itens de Supermercado
    $stmtItem = $pdo->prepare("INSERT OR IGNORE INTO itens_lista (id, lista_id, titulo, concluido, ordem, quantidade, unidade, preco_estimado, preco_real, criado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtItem->execute([1, 1, 'Arroz Tipo 1 (5kg)', 1, 1, 1.00, 'pct', 28.50, 27.90, 1]);
    $stmtItem->execute([2, 1, 'Feijão Carioca (1kg)', 1, 2, 2.00, 'pct', 8.50, 8.20, 1]);
    $stmtItem->execute([3, 1, 'Azeite de Oliva Extra Virgem', 0, 3, 1.00, 'un', 39.90, 0.00, 2]);
    $stmtItem->execute([4, 1, 'Leite Integral', 0, 4, 6.00, 'litros', 5.20, 0.00, 1]);
    $stmtItem->execute([5, 1, 'Maçãs Gala', 0, 5, 1.50, 'kg', 11.00, 0.00, 2]);

    // Itens de Filmes
    $stmtFilme = $pdo->prepare("INSERT OR IGNORE INTO itens_lista (id, lista_id, titulo, concluido, ordem, genero, plataforma, nota, notas, criado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtFilme->execute([6, 2, 'Interestelar', 1, 1, 'Ficção Científica', 'Max / HBO', 5, 'Obra de arte emocionante!', 1]);
    $stmtFilme->execute([7, 2, 'Duna: Parte 2', 0, 2, 'Ficção Científica', 'Max / Prime Video', 5, 'Assistir com som alto na sala', 2]);
    $stmtFilme->execute([8, 2, 'Stranger Things', 0, 3, 'Suspense / Mistério', 'Netflix', 4, 'Esperando a próxima temporada', 1]);

    // Itens de Receitas
    $receitaIngredientes = "- 3 cenouras raladas\n- 4 ovos\n- 1 xícara de óleo\n- 2 xícaras de açúcar\n- 2 e 1/2 xícaras de farinha de trigo\n- 1 colher de fermento";
    $receitaPreparo = "1. Bata no liquidificador cenouras, ovos e óleo.\n2. Misture os secos na tigela.\n3. Asse por 40 min a 180°C.";
    $stmtRec = $pdo->prepare("INSERT OR IGNORE INTO itens_lista (id, lista_id, titulo, concluido, ordem, rendimento, tempo_preparo, ingredientes, modo_preparo, notas, criado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtRec->execute([9, 3, 'Bolo de Cenoura com Chocolate', 1, 1, '8 porções', '50 min', $receitaIngredientes, $receitaPreparo, 'Favorito da família!', 1]);

    // Tarefas
    $stmtTar = $pdo->prepare("INSERT OR IGNORE INTO itens_lista (id, lista_id, titulo, concluido, ordem, prioridade, data_limite, notas, criado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtTar->execute([10, 4, 'Pagar boleto da internet', 1, 1, 'urgente', '2026-10-25', 'Vencimento sem juros', 1]);
    $stmtTar->execute([11, 4, 'Agendar consulta dentista', 0, 2, 'media', '2026-10-30', 'Ligar no período da tarde', 1]);
    $stmtTar->execute([12, 4, 'Revisar relatório do projeto', 0, 3, 'alta', '2026-10-28', 'Verificar requisitos', 1]);
}

// Inicializa a conexão
try {
    $pdo = getDB();
} catch (Exception $e) {
    $pdo = null;
}

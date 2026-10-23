# Listo - Aplicativo de Listas Inteligentes & Compartilhadas

Plataforma prática, intuitiva e moderna desenvolvida em **PHP**, **MySQL (PDO)** e **Tailwind CSS**, criada para que pessoas e famílias organizem sua rotina diária criando, personalizando e compartilhando listas de compras, filmes, receitas, viagens e tarefas.

---

## 👥 Equipe & Links de Hospedagem
- **Julia**: [https://juluawos.wuaze.com/](https://juluawos.wuaze.com/)
- **Isabelly Santos**: [http://belly.infinityfree.io/](http://belly.infinityfree.io/)
- **Lauany Vianna**
- **Natacha Kauany** (`natachakauany07`)

---

## 🚀 Tecnologias Utilizadas
- **Backend**: PHP estruturado com sessões nativas (`$_SESSION`).
- **Banco de Dados**: MySQL com prepared statements via **PDO** (proteção contra SQL Injection).
- **Frontend**: HTML5, CSS3, Tailwind CSS (Mobile-First responsivo).
- **Ícones**: Lucide Icons.
- **Interatividade**: JavaScript assíncrono (AJAX com Fetch API) para marcação instantânea de itens.

---

## ✨ Funcionalidades Implementadas (de acordo com `requisitos.md`)

### 1. Autenticação e Usuários
- Cadastro seguro de usuário com Nome, E-mail único e Senha criptografada com `password_hash()`.
- Autenticação e controle de acesso estritamente via sessões nativas do PHP (`$_SESSION`).
- Gerenciamento de perfil e avatar individual.

### 2. Gestão de Listas por Categorias
Cada categoria conta com campos e estrutura personalizados:
- **🛒 Supermercado**:
  - Quantidade, unidade de medida (`kg`, `un`, `litros`, `pct`, etc.).
  - Preço estimado e preço real pago.
  - Painel com cálculo automático em tempo real do valor total estimado vs valor real gasto no carrinho!
  - Botão **"Reativar Lista"** para reutilizar a lista na próxima compra mensal.
- **🎬 Filmes & Séries**:
  - Gênero, onde assistir (Netflix, Prime Video, Disney+, Max, etc.).
  - Avaliação por estrelas (1 a 5) e resenha/notas.
  - Status "Para Assistir" / "Assistido".
- **🍳 Receitas**:
  - Rendimento (ex: "8 porções") e tempo de preparo.
  - Lista de ingredientes formatada com checklist para marcar durante o preparo.
  - Modo de preparo passo a passo.
- **✅ Tarefas & Rotina / ✈️ Viagens**:
  - Níveis de prioridade (Baixa, Média, Alta, Urgente).
  - Data limite / prazo de vencimento.
  - Notas adicionais e checklist dinâmico.

### 3. Compartilhamento e Grupos (Família)
- Criação de grupos compartilhados (ex: "Família Silva", "Galera da Casa").
- Convite por **Código Único** (ex: `LISTO-A7B8C9`) com botão de copiar em 1 clique.
- Convite direto por **E-mail** de usuário cadastrado.
- Controle de permissões:
  - **Administrador**: Gerencia membros, edita ou exclui qualquer lista do grupo.
  - **Membro**: Visualiza, cria listas e marca/edita itens do grupo.
- **Listas Privadas vs. Compartilhadas**: Listas sem grupo são privadas; listas vinculadas ao grupo ficam disponíveis para todos os membros em tempo real.

### 4. Interatividade e Regras Globais
- **Sincronização Imediata (AJAX)**: Ao marcar/desmarcar um item, o status é gravado no MySQL instantaneamente.
- **Histórico & Lixeira (Recycle Bin)**:
  - Listas e itens descartados vão para a Lixeira e podem ser restaurados a qualquer momento.
  - Opção de exclusão definitiva ou restauração.
- **Notificações Flutuantes (TOASTS)**: Mensagens de erro e sucesso exibidas com design moderno.
- **Modais Customizados**: Diálogos de confirmação nativos do navegador (`window.confirm`/`alert`) foram substituídos por modais animados e responsivos.
- **Design Mobile-First**: Otimizado para smartphones com barra de navegação inferior estilo app nativo e expansão responsiva para computadores.

---

## 🛠️ Como Instalar e Rodar Localmente

### Opção A: Com XAMPP / WampServer / Laragon
1. Coloque a pasta do projeto dentro de `htdocs` (no XAMPP) ou `www` (no WampServer), ex: `C:\xampp\htdocs\listo`.
2. Inicie os serviços do **Apache** e **MySQL** no painel do XAMPP.
3. Abra seu navegador em:
   ```
   http://localhost/listo/install.php
   ```
4. Clique no botão **"Executar Instalação e Criar Tabelas"**. O instalador criará o banco `listo`, as tabelas e dados de teste automaticamente!
5. Acesse o sistema em:
   ```
   http://localhost/listo/
   ```

### Opção B: Importando via phpMyAdmin
1. Abra o **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Crie um banco chamado `listo`.
3. Vá na aba **Importar**, selecione o arquivo `database.sql` e clique em **Executar**.

---

## 🌐 Como Publicar no InfinityFree / Wuaze
1. No painel de controle da sua hospedagem, crie uma base de dados MySQL.
2. Abra o **phpMyAdmin** da hospedagem e importe o arquivo `database.sql`.
3. Abra o arquivo `bd.php` e preencha as constantes com os dados fornecidos pela hospedagem:
   ```php
   define('DB_HOST', 'sqlXXX.infinityfree.com'); // ou sqlXXX.wuaze.com
   define('DB_NAME', 'epiz_XXXXXXX_listo');
   define('DB_USER', 'epiz_XXXXXXX');
   define('DB_PASS', 'sua_senha_do_painel');
   ```
4. Faça upload dos arquivos para a pasta `htdocs` via FTP ou Gerenciador de Arquivos.

---

## 🔑 Acesso de Teste (Usuário Demonstração)
O banco de dados já vem com uma conta pré-configurada para testes e avaliação:
- **E-mail:** `demo@listo.com`
- **Senha:** `123456`

*(Você também pode clicar no botão "Preencher com Usuário Demo" na tela de login).*

---

## 📂 Estrutura de Arquivos
```text
listo/
├── actions/                  # Controladores e scripts de backend
│   ├── auth_login.php        # Autenticação de login
│   ├── auth_logout.php       # Encerramento de sessão
│   ├── auth_register.php     # Cadastro com hash de senha
│   ├── group_create.php      # Criação de grupos
│   ├── group_delete.php      # Exclusão de grupo
│   ├── group_invite.php      # Convite por e-mail
│   ├── group_join.php        # Entrada por código de convite
│   ├── group_member_remove.php # Remoção e saída de membros
│   ├── item_create.php       # Adição de item conforme categoria
│   ├── item_delete.php       # Envio para lixeira ou exclusão
│   ├── item_edit.php         # Edição de atributos do item
│   ├── item_toggle.php       # Alternância de concluído com AJAX
│   ├── list_create.php       # Criação de lista
│   ├── list_delete.php       # Mover para lixeira ou restaurar
│   ├── list_edit.php         # Edição de lista
│   ├── list_reactivate.php   # Reativação de lista (desmarcar itens)
│   └── profile_update.php    # Atualização de perfil e senha
├── assets/
│   ├── css/custom.css        # Animações, estilos e scrollbars
│   └── js/app.js             # Sistema de Modais, Toasts e AJAX
├── config/
│   └── bd.php                # Alias para bd.php
├── includes/
│   ├── auth.php              # Funções de verificação e permissões
│   ├── db_error.php          # Diagnóstico amigável de erro de banco
│   ├── footer.php            # Rodapé, navegação mobile e toasts
│   ├── functions.php         # Formatações, sanitização e categorias
│   └── header.php            # Cabeçalho, Tailwind CSS e ícones
├── views/
│   ├── dashboard.php         # Painel principal e resumo de atividades
│   ├── group_view.php        # Detalhes do grupo, listas e membros
│   ├── groups.php            # Listagem e criação de grupos
│   ├── history.php           # Lixeira de listas e itens descartados
│   ├── list_view.php         # Tela da lista (Supermercado, Filmes, etc.)
│   ├── lists.php             # Gerenciador com filtros e busca
│   ├── login.php             # Tela de login com preenchimento demo
│   ├── profile.php           # Perfil e configurações da conta
│   ├── register.php          # Cadastro de novos usuários
│   └── partials/
│       └── modal_item_edit.php # Modal de edição por item
├── bd.php                    # Conexão PDO com MySQL
├── database.sql              # Script SQL com tabelas e dados demonstrativos
├── install.php               # Instalador web automático em 1 clique
├── index.php                 # Roteamento inicial
├── LICENSE                   # Licença MIT
└── requisitos.md             # Requisitos originais do projeto
```

---
Licença MIT &copy; 2026 Listo.

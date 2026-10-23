# Listo

## OBJETIVOS 

criar um site que a seja uma plataforma prática e intuitiva para que as pessoas possam organizar sua rotina criando, personalizando e compartilhando listas de tarefas, compras, filmes, músicas e viagens com outras pessoas, utilizando ícones visuais para facilitar a identificação. Para garantir a segurança dos dados e a individualidade de cada conta, o acesso é feito por meio de um cadastro único atrelado a um só e-mail por usuário.

### STACK TECNÓLOGICO
- Backend: PHP estruturado em sessões nativas
- Banco de dados: MySQL (PDO para segurança)
- Frontend: HTML5, PHP, CSS, Tailwind CSS

#### Regras de negocio (CORE) 

##### Regras globais
- Use sempre o mesmo PDO para conexão e queries no MySQL para evitar SQL Injections
- Mantenha o código limpo e comente apenas lógicas complexas.
- Separe os arquivos de forma lógica: um arquivo para conexão com a base (bd.php) e scripts de backend isolados em views em HTML5/PHP
- Estilize as telas em tailwind de forma responsiva priorizando o MobileFirst.

##### 1. Autenticação e Usuários
- **Cadastro e Login:** O usuário deve se cadastrar com Nome, E-mail e Senha (com hash seguro via `password_hash`).
- **Sessão:** A autenticação e o controle de acesso às páginas privadas devem ser gerenciados estritamente por sessões nativas do PHP (`$_SESSION`).
- **Perfil:** Cada usuário possui um perfil individual que pode estar associado a um ou mais grupos/famílias.

##### 2. Gestão de Listas
- **Tipos de Lista:** Suporte a diferentes categorias/tipos de listas (Supermercado, Filmes/Séries, Receitas, Tarefas, etc.).
- **Estrutura dos Itens:**
  - *Supermercado:* Nome do item, quantidade, unidade de medida, preço estimado/real e status (marcado/desmarcado).
  - *Filmes/Séries:* Título, gênero, onde assistir (streaming/plataforma) e status (assistido/para assistir).
  - *Receitas:* Ingredientes, modo de preparo e rendimento.
- **Criação e Edição:** Apenas o criador da lista ou membros do grupo compartilhado têm permissão para editar ou excluir a lista e seus itens.

##### 3. Compartilhamento e Grupos (Família)
- **Criação de Grupos:** Um usuário pode criar um grupo (ex: "Família Silva") e convidar outros usuários cadastrados por e-mail ou código de convite.
- **Níveis de Permissão:**
  - *Dono/Administrador:* Pode excluir o grupo, gerenciar membros e editar/excluir qualquer lista do grupo.
  - *Membro:* Pode visualizar, criar e editar listas associadas ao grupo.
- **Listas Privadas vs. Compartilhadas:** Por padrão, listas criadas sem grupo são privadas. Listas vinculadas a um grupo ficam visíveis para todos os membros do grupo.

##### 4. Atualização e Interatividade
- **Sincronização de Itens:** Ao marcar um item como concluído/comprado, o status deve ser atualizado no banco de dados para refletir imediatamente para os demais membros.
- **Histórico/Lixeira:** Listas apagadas ou itens concluídos podem ser mantidos em histórico para reutilização rápida (ex: reativar lista de compras mensal).
- 

##### Regras globais
- Use sempre o mesmo PDO para conexão e quieres no MySQL para evitar SQL Injections
- Mantenha o código limpo e comente apenas logicas complexas.
- Separe os arquivos  de forma logica: um arquivo para conexão com a base (bd.php) e scrips de backend isolados em views em HTML5/PHP
- Estilize as telas em tailwind de forma responsiva priorizando o MobileFrist.
- Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)
- sempre trate as mensagens de caixa de mensagens de caixa de mensagens nativas de mensagens nativas do navegador em um MODAL


# 🇧🇷 SABOR BRASIL · Sistema Integrado de Gestão Gastronômica & PDV

O **SABOR BRASIL** é um sistema web Full-Stack de Ponto de Venda (PDV) e Planejamento de Recursos Empresariais (ERP) desenvolvido para digitalizar o fluxo operacional de restaurantes de médio porte. O software cobre desde a recepção e controle visual de mesas até o lançamento de comandas, emissão de extratos com preservação de histórico de preços e relatórios gerenciais com gráficos analíticos.

Projeto desenvolvido como requisito de avaliação da disciplina de **Programação para Internet I** do curso de **Análise e Desenvolvimento de Sistemas**.

---

## 🎯 Objetivo e Contexto

Sistemas tradicionais baseados em papel e caneta geram erros de comunicação entre salão e cozinha, atrasos em horários de pico e furos no controle de caixa. Este projeto resolve esse problema digitalizando integralmente o atendimento web, utilizando o **PHP 8+** como camada central de lógica de negócios e o **MySQL** como banco de dados relacional.

### 🛡️ Destaques da Arquitetura e Segurança:
* **Preservação Histórica de Preços:** Ao adicionar um item na comanda, o preço unitário e o nome do produto no momento exato do pedido são gravados de forma fixa na tabela `pedido_itens`. Mudanças futuras nos preços do cardápio não alteram o histórico nem adulteram relatórios financeiros antigos.
* **Segurança contra SQL Injection:** Toda e qualquer comunicação com o banco de dados é intermediada por **PDO (PHP Data Objects)** com o uso de *Prepared Statements*. Nenhuma consulta é feita por concatenação direta de variáveis.
* **Criptografia Padrão Bcrypt:** As senhas dos operadores nunca são salvas em texto puro. O sistema aplica obrigatoriamente a função nativa `password_hash()` no cadastro e valida o acesso via `password_verify()`.
* **Transações Atômicas (`Commit / Rollback`):** O gerenciamento de abertura de mesas, lançamento de pedidos e liberação de caixa utiliza transações no MySQL, prevenindo dados inconsistentes em caso de falhas de rede ou servidor.
* **Sessões e Proteção de Rotas (`$_SESSION`):** Nenhuma página interna pode ser acessada sem autenticação. O topo de cada script valida o perfil do usuário logado e redireciona automaticamente invasores ou acessos não autorizados.

---

## 👥 Perfis de Acesso

O sistema gerencia dois níveis de privilégios controlados rigidamente via sessão:

| Perfil | Nível de Acesso | Funcionalidades Autorizadas |
| :--- | :--- | :--- |
| **Administrador (`admin`)** | **Gestão Total (ERP)** | Acesso aos painéis gerenciais, CRUD completo do catálogo de produtos, controle de estoque (ativar/desativar itens no salão), cadastro e auditoria de operadores, e visualização de relatórios de faturamento com filtros de data e gráficos analíticos. |
| **Garçom (`garcom`)** | **Operação de Salão (PDV)** | Visualização gráfica do salão com status em tempo real (Livre, Ocupada, Aguardando Pagamento), abertura de comandas, lançamento e estorno de itens, emissão de cupom de conferência e processamento de fechamento de contas. |

> 🔒 **Trava de Segurança Backend:** Um administrador logado é bloqueado pelo sistema de desativar a própria credencial de acesso em uso, impedindo o bloqueio acidental da gerência.

---

## 🛠️ Como Executar o Projeto Localmente (Guia do Avaliador)

O projeto foi configurado e testado para execução nativa em servidores locais como **WampServer64** ou **XAMPP**. Siga os passos abaixo para rodar em seu computador:

### 1. Pré-requisitos
* Servidor local instalado ([WampServer64](https://www.wampserver.com/) ou [XAMPP](https://www.apachefriends.org/)) rodando **PHP 8.0+** e **MySQL 5.7+ / MariaDB**.
* Serviço Apache rodando na porta padrão web (`80`) e MySQL na porta padrão (`3306`).

### 2. Alocação dos Arquivos
1. Clone ou copie a pasta integral do projeto (`restaurante`) para dentro do diretório raiz web do seu servidor:
   * No **WampServer:** Coloque em `C:\wamp64\www\restaurante`
   * No **XAMPP:** Coloque em `C:\xampp\htdocs\restaurante`
2. Certifique-se de manter a estrutura original de pastas intacta.

### 3. Importação do Banco de Dados (`banco.sql`)
O arquivo **`banco.sql`** na raiz do projeto é o script DDL/DML que prepara todo o ecossistema do restaurante.
1. Ligue os serviços do seu servidor local (o ícone do WampServer deve ficar **Verde 🟢**).
2. Abra seu navegador web e acesse o painel do MySQL: `http://localhost/phpmyadmin/`.
3. Clique na aba principal **SQL** na parte superior.
4. Abra o arquivo **`banco.sql`** em um editor de texto, copie **todo** o seu conteúdo e cole na caixa de comandos do phpMyAdmin.
5. Clique no botão **Executar**. 
6. *Pronto!* O script criará o banco `restaurante_db`, implementará todas as tabelas e chaves estrangeiras, irá pré-cadastrar as 10 mesas fixas do salão e populará o cardápio e a equipe com senhas criptografadas.

### 4. Parâmetros de Conexão
As configurações de conexão ficam isoladas em um único arquivo: **`config/db.php`**. Por padrão, o ambiente vem configurado para o WampServer64 (usuário `root` sem senha):
```php
$host = '127.0.0.1';       // IP local (Evita bugs de resolução de DNS do Windows)
$dbname = 'restaurante_db';
$username = 'root';
$password = '';            // Se o seu servidor local exigir senha para o root, insira aqui.

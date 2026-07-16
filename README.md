# ERP & PDV SABOR BRASIL
**Sistema Integrado de Gestão Gastronômica, Controle de Salão e Ponto de Venda**

---

## 1. Visão Geral e Objetivos do Sistema

O **SABOR BRASIL** é uma aplicação web Full-Stack orientada à digitalização e ao controle operacional de restaurantes de médio porte. O software foi concebido para substituir processos manuais de atendimento por um fluxo automatizado e auditável, eliminando erros de comunicação entre o salão e a cozinha, atrasos em horários de pico e divergências no fechamento de caixa.

O sistema foi desenvolvido como requisito de avaliação na disciplina de **Programação para Internet I** do curso superior de **Análise e Desenvolvimento de Sistemas**, utilizando a linguagem **PHP 8+** na camada de lógica no servidor e o **MySQL** como sistema de gerenciamento de banco de dados relacional.

---

## 2. Padrões de Arquitetura e Segurança de Dados

O projeto foi estruturado seguindo rigorosas boas práticas de engenharia de software e segurança da informação:

### 2.1. Preservação Histórica de Preços e Auditoria
Para manter a integridade fiscal e financeira do estabelecimento, o modelo relacional adota a dissociação entre o catálogo atual e o histórico de vendas. No momento em que o operador lança um produto na comanda, o sistema realiza uma consulta ao catálogo e grava o `nome_item` e o `preco_unitario` de forma estática na tabela `pedido_itens`. 
Desta forma, reajustes futuros nos preços do cardápio não alteram o valor das contas já encerradas, garantindo que os relatórios contábeis reflitam exatamente o valor transacionado na data da venda.

### 2.2. Prevenção contra SQL Injection (PHP PDO)
Todas as operações de entrada, saída e manipulação de dados utilizam exclusivamente a extensão **PDO (PHP Data Objects)** com declarações preparadas (*Prepared Statements*). A vinculação de parâmetros via `bindParam()` e `execute()` impede a concatenação direta de variáveis em strings SQL, neutralizando riscos de ataques por injeção de código.

### 2.3. Criptografia e Autenticação de Senhas
O armazenamento de credenciais descarta o uso de texto puro ou algoritmos obsoletos (como MD5 ou SHA1). O sistema aplica a função nativa `password_hash()` utilizando o algoritmo **Bcrypt** para a geração de hashes criptográficos unidirecionais no cadastro de usuários, autenticando as sessões por meio do processamento analítico de `password_verify()`.

### 2.4. Integridade Transacional (Commit e Rollback)
Operações críticas que envolvem múltiplas tabelas simultaneamente — como a abertura de comandas (que insere o pedido e altera o status da mesa) ou o recebimento de contas (que altera o status do pedido, registra a forma de pagamento e libera a mesa) — são encapsuladas em blocos transacionados (`$pdo->beginTransaction()`, `$pdo->commit()` e `$pdo->rollBack()`). Caso ocorra qualquer falha de processamento em uma das etapas, todas as alterações são desfeitas no banco de dados, impedindo inconsistências.

### 2.5. Controle de Acesso via Sessão (`$_SESSION`)
O controle de roteamento e privilégios é validado no servidor antes da renderização de qualquer interface HTML. O middleware utilitário `session_helper.php` verifica a existência da sessão ativa e o perfil do usuário logado, redirecionando automaticamente acessos não autorizados para a tela de autenticação.

---

## 3. Perfis de Usuário e Controle de Permissões

O sistema implementa o controle de acesso baseado em funções (RBAC - *Role-Based Access Control*), dividindo a operação em dois níveis hierárquicos distintos:

| Perfil de Acesso | Módulo Direcionado | Permissões e Funcionalidades Autorizadas |
| :--- | :--- | :--- |
| **Administrador (`admin`)** | **ERP Gerencial** | Acesso integral ao sistema. Gestão do catálogo de produtos (cadastro, edição, precificação e alteração de disponibilidade), gerenciamento de equipe (cadastro de novos operadores e bloqueio de acessos) e consulta a relatórios analíticos de vendas com filtragem por período e renderização de gráficos. |
| **Garçom (`garcom`)** | **PDV Operacional** | Acesso restrito ao salão. Monitoramento em tempo real do mapa de mesas (Livre, Ocupada, Aguardando Pagamento), abertura de comandas, lançamento e estorno de itens no pedido, conferência de subtotais e processamento do fechamento de conta. |

> **Observação Técnica:** A camada de backend conta com uma trava de segurança que impede que um administrador autenticado desative ou bloqueie a própria conta em uso, evitando a indisponibilidade acidental do painel de gestão.

---

## 4. Guia de Instalação e Execução (Ambiente Local)

As instruções abaixo descrevem o procedimento necessário para configurar, executar e avaliar o projeto em um ambiente local utilizando servidores como **WampServer64** ou **XAMPP**.

### 4.1. Pré-requisitos
* Servidor web local ([WampServer64](https://www.wampserver.com/) ou [XAMPP](https://www.apachefriends.org/)) com **PHP 8.0+** e **MySQL 5.7+** (ou MariaDB) ativos.
* Portas padrão liberadas no sistema operacional: HTTP (`80`) e MySQL (`3306`).

### 4.2. Instalação dos Arquivos
1. Clone este repositório ou copie a pasta integral do projeto (`restaurante`) para o diretório de publicação web do servidor:
   * **WampServer:** `C:\wamp64\www\restaurante`
   * **XAMPP:** `C:\xampp\htdocs\restaurante`
2. Certifique-se de que a estrutura de diretórios original seja mantida.

### 4.3. Implementação do Banco de Dados (`banco.sql`)
O arquivo **`banco.sql`**, localizado na raiz do projeto, contém todas as instruções DDL (criação de estrutura) e DML (carga de dados) necessárias para o funcionamento do sistema.
1. Inicie os serviços do servidor web e do banco de dados no seu painel de controle local.
2. Acesse o gerenciador de banco de dados pelo navegador: `http://localhost/phpmyadmin/`.
3. Clique na aba superior **SQL**.
4. Abra o arquivo **`banco.sql`** em um editor de texto, copie integralmente todo o código e cole na área de execução do phpMyAdmin.
5. Clique no botão **Executar**.
6. O script gerará o banco de dados `restaurante_db`, construirá as tabelas relacionais com suas respectivas chaves estrangeiras, cadastrará as 10 mesas fixas do salão e inserirá os dados iniciais do cardápio e da equipe operacional.

### 4.4. Configuração dos Parâmetros de Conexão
Os dados de autenticação com o banco de dados estão centralizados no arquivo **`config/db.php`**. A configuração padrão atende à instalação nativa do WampServer64 (usuário `root` e senha ´root´):

```php
$host = '127.0.0.1';       // Endereço de loopback (Evita latência de resolução DNS do Windows)
$dbname = 'restaurante_db';// Nome da base de dados gerada pelo script
$username = 'root';        // Usuário padrão do MySQL
$password = 'root';



## 5. Credenciais Padrão para Avaliação

Para agilizar o processo de verificação e correção pelo docente, o script de banco de dados cadastra previamente usuários de teste para ambos os perfis com hashes criptográficos funcionais:

| Nome do Operador / Gestor | Login de Acesso | Palavra-passe | Perfil Atribuído | Módulo de Entrada |
| :--- | :--- | :--- | :--- | :--- |
| **Adielson dos Santos** | `adielson` | `admin123` | `admin` | Painel Gerencial ERP |
| **Gestão Sistema Padrão** | `admin` | `admin123` | `admin` | Painel Gerencial ERP |
| **Jhonatas Gomes** | `jhonatas` | `garcom123` | `garcom` | Salão de Mesas PDV |

---

## 7. Funcionalidades Avançadas e Diferenciais Técnicos

Além da cobertura integral dos requisitos obrigatórios solicitados nas especificações do desafio, foram implementadas as seguintes soluções de engenharia:

1. **Impressão Nativa de Comandas (`@media print`):** A interface de fechamento de conta (`pedidos/fechar_conta.php`) possui folha de estilo adaptada exclusivamente para impressão. Ao acionar o botão de impressão, o navegador suprime os elementos de navegação e menus, renderizando um extrato limpo e estruturado em preto e branco, compatível com impressoras térmicas de cupom ou formato A4.
2. **Gráfico Interativo de Faturamento via Chart.js:** O relatório gerencial processa agregações no banco de dados (`SUM` com `GROUP BY`) para calcular a receita dividida por categorias (Pratos Principais, Bebidas, Entradas e Sobremesas). O array resultante é convertido em JSON e enviado ao JavaScript para renderização de um gráfico analítico de rosca (*Doughnut Chart*) sem a necessidade de processamento externo.
3. **Algoritmo de Mapeamento de Desempenho Operacional:** A tela de auditoria processa uma consulta que avalia as vendas vinculadas aos operadores no período filtrado, identificando automaticamente o garçom com maior volume de faturamento gerado e maior quantidade de mesas atendidas, exibindo um indicador de destaque gerencial no topo do relatório.
4. **Controle de Estoque Sem Separação de Histórico (Soft Toggle):** Para evitar a exclusão física de registros — o que invalidaria cálculos de faturamento de meses anteriores —, o gerenciamento do cardápio adota a alteração de status lógica através de instrução condicional no MySQL (`IF(disponivel = 1, 0, 1)`). O produto esgotado desaparece imediatamente da tela do garçom, preservando a integridade do banco de dados relacional.

---

## 8. Autoria e Desenvolvimento

Software concebido, modelado e desenvolvido integralmente para a disciplina de **Programação para Internet I**:

* **Desenvolvedor Full-Stack:** Adielson dos Santos
* **Curso:** Análise e Desenvolvimento de Sistemas

---
*Instituto Federal · Curso Superior de Análise e Desenvolvimento de Sistemas · 2026*

# Simplificado - Plataforma de Pagamentos

Este projeto é uma implementação simplificada de uma plataforma de pagamentos, semelhante ao PicPay, onde usuários podem realizar transferências de dinheiro entre si. O projeto foi desenvolvido utilizando o framework Laravel e Docker para facilitar o desenvolvimento e a implantação.

## Funcionalidades

- Cadastro de usuários e lojistas com validação de CPF/CNPJ e e-mail únicos.
- Realização de transferências de dinheiro entre usuários e entre usuários e lojistas.
- Validação de saldo antes de realizar transferências.
- Consulta a um serviço autorizador externo antes de finalizar uma transferência.
- Envio de notificações de pagamento (via e-mail/SMS) utilizando um serviço externo.
- Transações realizadas de forma atômica para garantir a consistência dos dados.

## Tecnologias Utilizadas

- **PHP**: Linguagem de programação principal do projeto.
- **Laravel**: Framework PHP utilizado para desenvolvimento rápido e eficaz.
- **MySQL**: Banco de dados relacional para armazenamento das informações.
- **Docker**: Ferramenta de containerização para isolar e gerenciar o ambiente de desenvolvimento.
- **Composer**: Gerenciador de pacotes para PHP.
- **Git**: Controle de versão.
- **Insomnia**: Ferramenta utilizada para testar as APIs desenvolvidas.

## Pré-requisitos

Antes de começar, certifique-se de ter o seguinte instalado:

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)
- [Composer](https://getcomposer.org/)

## Instalação

Siga os passos abaixo para configurar o ambiente de desenvolvimento:

1. **Clone o repositório:**
   ```bash
    git clone https://github.com/gersonvinicius/simplificado.git
    cd simplificado

2. **Copie o arquivo de ambiente e configure as variáveis:**    
    ```bash
    cp .env.example .env

**Edite o arquivo .env para configurar as credenciais do banco de dados e outras configurações necessárias.**  
    
3. **Copie o arquivo de ambiente e configure as variáveis:**  
    Suba os containers do Docker:
    ```bash
    docker-compose up -d

4. **Instale as dependências do projeto:**  
    ```bash
    docker-compose exec app composer install

5. **Gere a chave da aplicação:**  
    ```bash
    docker-compose exec app php artisan key:generate

6. **Execute as migrations para criar as tabelas do banco de dados:**  
    ```bash
    docker-compose exec app php artisan migrate

7. **Execute os seeders para popular o banco de dados com dados iniciais:**  
    ```bash
    docker-compose exec app php artisan db:seed

**Testes**  
    
1. **Para rodar os testes unitários e de integração, utilize o comando abaixo:**  
    ```bash
    docker-compose exec app php artisan test

2. **Os testes estão configurados para rodar em um banco de dados separado, conforme especificado no arquivo .env.testing.**  
    
    ***Endpoints da API***
    ```bash
    POST /transfer

**Realiza uma transferência entre usuários.**  
    
3. **Corpo da Requisição:**
   ```bash
    {
        "value": 100.0,
        "payer": 4,
        "payee": 15
    }

**Licença**  
    
    Este projeto está licenciado sob a licença MIT - consulte o arquivo LICENSE para obter mais detalhes.
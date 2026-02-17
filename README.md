# Sistema de Registo de Ocorrências Comunitárias

Sistema web para registro e acompanhamento de ocorrências comunitárias (água, energia, lixo, segurança).

## Requisitos

- Docker Desktop instalado
- Docker Compose

## Como Rodar com Docker

### 1. Iniciar os containers

```bash
docker-compose up -d --build
```

### 2. Acessar a aplicação

| Serviço | URL |
|---------|-----|
| **Aplicação** | http://localhost:8080 |
| **phpMyAdmin** | http://localhost:8081 |

### 3. Credenciais de Teste

**Utilizador:**
- Email: `teste@teste.com`
- Senha: `123456`

**Base de Dados (phpMyAdmin):**
- Servidor: `db`
- Utilizador: `root`
- Senha: `root`

## Comandos Docker Úteis

```bash
# Iniciar containers
docker-compose up -d

# Parar containers
docker-compose down

# Ver logs
docker-compose logs -f app

# Reconstruir após alterações
docker-compose up -d --build

# Limpar tudo (incluindo volumes)
docker-compose down -v
```

## Estrutura do Projeto

```
├── config/
│   └── db.php              # Configuração da base de dados
├── css/
│   └── style.css           # Estilos (Nubank theme)
├── docker/
│   └── init.sql            # Script inicial da BD
├── responsavel/
│   └── ocorrencias.php     # Painel técnico
├── dashboard.php           # Dashboard do utilizador
├── index.php               # Página principal
├── login.php               # Login
├── registar.php            # Registo
├── nova_ocorrencia.php     # Nova ocorrência
├── minhas_ocorrencias.php  # Ocorrências do utilizador
├── logout.php              # Logout
├── Dockerfile              # Configuração Docker
└── docker-compose.yml      # Orquestração
```

## Rodar Localmente (sem Docker)

1. Instalar XAMPP/MAMP/WAMP
2. Colocar projeto na pasta `htdocs` ou `www`
3. Criar base de dados `ocorrencias_comunitarias`
4. Executar o script `docker/init.sql` no MySQL
5. Acessar `http://localhost/sistema-de-registo-comunitario-`

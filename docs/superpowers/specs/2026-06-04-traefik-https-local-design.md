# Traefik + HTTPS Local (ffmarket)

**Data:** 2026-06-04  
**Status:** Superado — HTTPS foi implementado em 2026-09-01, mas usando o Traefik **compartilhado** externo em `/var/www/traefik` (não um Traefik dedicado ao projeto como este doc propõe) e host `ffxiv.local` (não `ffmarket`). Entrypoint `websecure`/443, provider `file` e cert mkcert em `/var/www/traefik/certs/ffxiv.local.pem`, redirect HTTP→HTTPS escopado só ao router `ffxiv` (para não afetar os outros projetos que compartilham o mesmo Traefik). Ver `docker-compose.yml` deste repo e `/var/www/traefik/` para o estado real.

## Objetivo

Servir a aplicação FFXIV Market em `https://ffmarket` no ambiente de desenvolvimento local, com certificado confiável gerado por mkcert (sem aviso de browser). HTTP (`http://ffmarket`) redireciona automaticamente para HTTPS.

## Componentes

### 1. Certificados (`docker/certs/`)

Gerados com mkcert na máquina host. Não versionados (`.gitignore`).

- `docker/certs/ffmarket.pem` — certificado público
- `docker/certs/ffmarket-key.pem` — chave privada

### 2. Configuração estática do Traefik (`docker/traefik/traefik.yml`)

- Entrypoint `web` na porta 80 com redirect global para HTTPS
- Entrypoint `websecure` na porta 443
- Provider `file` apontando para `/etc/traefik/dynamic.yml`

### 3. Configuração dinâmica do Traefik (`docker/traefik/dynamic.yml`)

- Define o store TLS com os certificados mkcert montados no container

### 4. `docker-compose.yml`

- Novo serviço `traefik`:
  - Imagem `traefik:v3.0`
  - Portas `80:80` e `443:443`
  - Volumes: socket Docker (read-only), `traefik.yml`, `dynamic.yml`, `docker/certs/`
  - Conectado à rede `ffxiv`
- Serviço `webserver`:
  - Porta `8888:80` removida
  - Labels Traefik para roteamento `Host(ffmarket)` → `websecure`
- Serviço `app`: sem alterações
- Serviço `database`: sem alterações
- Serviço `redis`: sem alterações

## Fluxo de Requisição

```
https://ffmarket  →  Traefik:443  →  nginx (container):80  →  PHP-FPM:9000
http://ffmarket   →  Traefik:80   →  redirect 301          →  https://ffmarket
```

## Passos de Setup (uma vez por máquina)

1. Instalar mkcert: `sudo dnf install mkcert` (ou via Homebrew/binary)
2. Instalar CA local: `mkcert -install`
3. Gerar certificados: `mkcert -cert-file docker/certs/ffmarket.pem -key-file docker/certs/ffmarket-key.pem ffmarket`
4. Subir stack: `docker compose up -d`

## O que NÃO muda

- Configuração do nginx (`docker/nginx/default.conf`) permanece igual
- Dockerfile PHP permanece igual
- `ffmarket` já está no `/etc/hosts` → não precisa de alteração

## Exclusões do `.gitignore`

```
docker/certs/
```

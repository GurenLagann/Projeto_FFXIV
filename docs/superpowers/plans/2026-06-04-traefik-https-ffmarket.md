# Traefik HTTPS Local (ffmarket) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

> **Status:** Concluído em 2026-09-01, com host `ffxiv.local` (não `ffmarket`). O Traefik compartilhado real (`/var/www/traefik/traefik.yml`) ganhou entrypoint `websecure`/443 e `providers.file` apontando para `/var/www/traefik/dynamic/`, mantendo `providers.docker` para os labels de cada projeto. Redirect HTTP→HTTPS aplicado só ao router `ffxiv` via middleware nomeado, para não afetar Financas/PM/Portfólio.

**Goal:** Servir `https://ffmarket` com certificado mkcert confiável usando o Traefik compartilhado existente em `/var/www/Traefik/`.

**Architecture:** O Traefik compartilhado já gerencia múltiplos projetos via file provider em `/var/www/Traefik/dynamic/`. Vamos (1) habilitar o entrypoint 443, (2) conectar o Traefik à rede Docker do projeto FFXIV, (3) criar um arquivo de rota dinâmica para `ffmarket`, e (4) remover a exposição direta da porta 8888 do nginx.

**Tech Stack:** Traefik v3.4, mkcert, Docker Compose, nginx:alpine

---

## Mapa de Arquivos

| Ação | Arquivo |
|------|---------|
| Modificar | `/var/www/Traefik/docker-compose.yml` |
| Criar | `/var/www/Traefik/certs/ffmarket.pem` + `ffmarket-key.pem` (gerados por mkcert, não versionados) |
| Criar | `/var/www/Traefik/dynamic/ffmarket.yml` |
| Modificar | `/var/www/Projeto_FFXIV/docker-compose.yml` |

---

## Task 1: Instalar mkcert e gerar certificados

**Files:**
- Criar: `/var/www/Traefik/certs/` (diretório, não versionado)

- [ ] **Step 1: Instalar mkcert**

```bash
sudo dnf install mkcert -y
```

Esperado: sem erros. Verificar com:
```bash
mkcert --version
```

- [ ] **Step 2: Instalar a CA local do mkcert no sistema**

```bash
mkcert -install
```

Esperado: mensagem `The local CA is now installed in the system trust store!`

- [ ] **Step 3: Criar o diretório de certificados**

```bash
mkdir -p /var/www/Traefik/certs
```

- [ ] **Step 4: Gerar o certificado para o domínio ffmarket**

```bash
mkcert \
  -cert-file /var/www/Traefik/certs/ffmarket.pem \
  -key-file  /var/www/Traefik/certs/ffmarket-key.pem \
  ffmarket
```

Esperado:
```
Created a new certificate valid for the following names 📜
 - "ffmarket"
```

- [ ] **Step 5: Verificar os arquivos gerados**

```bash
ls -la /var/www/Traefik/certs/
```

Esperado: dois arquivos — `ffmarket.pem` e `ffmarket-key.pem`

- [ ] **Step 6: Adicionar certs ao .gitignore do Traefik (se houver git)**

```bash
cd /var/www/Traefik
git check-ignore -v certs/ 2>/dev/null || echo "certs/" >> .gitignore
```

---

## Task 2: Atualizar o Traefik compartilhado (443 + rede ffxiv)

**Files:**
- Modificar: `/var/www/Traefik/docker-compose.yml`

Estado atual do arquivo:
```yaml
services:
  traefik:
    image: traefik:v3.4
    container_name: traefik
    command:
      - --api.insecure=true
      - --providers.file.directory=/etc/traefik/dynamic
      - --providers.file.watch=true
      - --entrypoints.web.address=:80
    ports:
      - "80:80"
      - "9090:8080"
    volumes:
      - ./dynamic:/etc/traefik/dynamic:ro
    networks:
      - traefik-net
      - controlefinanceiro_financas_network

networks:
  traefik-net:
    external: true
  controlefinanceiro_financas_network:
    external: true
```

- [ ] **Step 1: Substituir o conteúdo de `/var/www/Traefik/docker-compose.yml`**

```yaml
services:
  traefik:
    image: traefik:v3.4
    container_name: traefik
    command:
      - --api.insecure=true
      - --providers.file.directory=/etc/traefik/dynamic
      - --providers.file.watch=true
      - --entrypoints.web.address=:80
      - --entrypoints.websecure.address=:443
    ports:
      - "80:80"
      - "443:443"
      - "9090:8080"
    volumes:
      - ./dynamic:/etc/traefik/dynamic:ro
      - ./certs:/etc/traefik/certs:ro
    networks:
      - traefik-net
      - controlefinanceiro_financas_network
      - projeto_ffxiv_ffxiv

networks:
  traefik-net:
    external: true
  controlefinanceiro_financas_network:
    external: true
  projeto_ffxiv_ffxiv:
    external: true
```

- [ ] **Step 2: Verificar sintaxe do arquivo**

```bash
cd /var/www/Traefik && docker compose config --quiet && echo "OK"
```

Esperado: `OK` sem erros

---

## Task 3: Criar rota dinâmica para ffmarket

**Files:**
- Criar: `/var/www/Traefik/dynamic/ffmarket.yml`

- [ ] **Step 1: Criar o arquivo de rota**

```yaml
http:
  middlewares:
    redirect-to-https:
      redirectScheme:
        scheme: https
        permanent: true

  routers:
    ffmarket-http:
      rule: "Host(`ffmarket`)"
      entryPoints:
        - web
      middlewares:
        - redirect-to-https
      service: ffmarket-svc

    ffmarket-https:
      rule: "Host(`ffmarket`)"
      entryPoints:
        - websecure
      tls: {}
      service: ffmarket-svc

  services:
    ffmarket-svc:
      loadBalancer:
        servers:
          - url: "http://ffxiv_webserver:80"

tls:
  certificates:
    - certFile: /etc/traefik/certs/ffmarket.pem
      keyFile: /etc/traefik/certs/ffmarket-key.pem
```

Salvar em `/var/www/Traefik/dynamic/ffmarket.yml`

- [ ] **Step 2: Verificar YAML válido**

```bash
python3 -c "import yaml; yaml.safe_load(open('/var/www/Traefik/dynamic/ffmarket.yml'))" && echo "YAML OK"
```

Esperado: `YAML OK`

---

## Task 4: Remover porta 8888 do FFXIV docker-compose

**Files:**
- Modificar: `/var/www/Projeto_FFXIV/docker-compose.yml`

- [ ] **Step 1: Remover o bloco `ports` do serviço `webserver`**

No arquivo `/var/www/Projeto_FFXIV/docker-compose.yml`, remover as linhas:

```yaml
    ports:
      - "8888:80"
```

do serviço `webserver`. O serviço fica sem exposição direta de porta — o acesso é feito apenas via Traefik.

- [ ] **Step 2: Verificar sintaxe**

```bash
cd /var/www/Projeto_FFXIV && docker compose config --quiet && echo "OK"
```

Esperado: `OK`

- [ ] **Step 3: Commit das mudanças no FFXIV**

```bash
cd /var/www/Projeto_FFXIV
git add docker-compose.yml
git commit -m "chore: remover porta 8888 — acesso via Traefik em https://ffmarket"
```

---

## Task 5: Recriar containers e verificar

- [ ] **Step 1: Recriar o Traefik com a nova configuração**

```bash
cd /var/www/Traefik && docker compose up -d --force-recreate
```

Esperado: container `traefik` recriado sem erros.

- [ ] **Step 2: Verificar que a porta 443 está sendo escutada**

```bash
docker ps --filter name=traefik --format "{{.Ports}}"
```

Esperado: `0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp, ...`

- [ ] **Step 3: Recriar o webserver FFXIV (para remover a porta 8888)**

```bash
cd /var/www/Projeto_FFXIV && docker compose up -d --force-recreate webserver
```

Esperado: container `ffxiv_webserver` recriado sem a porta 8888.

- [ ] **Step 4: Verificar que o Traefik está na rede projeto_ffxiv_ffxiv**

```bash
docker network inspect projeto_ffxiv_ffxiv --format '{{range .Containers}}{{.Name}} {{end}}'
```

Esperado: `traefik` e `ffxiv_webserver` (e outros containers do projeto) listados.

- [ ] **Step 5: Verificar logs do Traefik para erros de TLS**

```bash
docker logs traefik 2>&1 | grep -iE "error|tls|cert" | tail -20
```

Esperado: sem erros. Pode aparecer mensagem de configuração dos certs carregados.

- [ ] **Step 6: Testar redirect HTTP → HTTPS**

```bash
curl -v http://ffmarket 2>&1 | grep -E "Location|< HTTP"
```

Esperado:
```
< HTTP/1.1 301 Moved Permanently
< Location: https://ffmarket/
```

- [ ] **Step 7: Testar HTTPS**

```bash
curl -sv https://ffmarket 2>&1 | grep -E "< HTTP|subject:|issuer:"
```

Esperado:
```
* subject: CN=ffmarket
* issuer: CN=mkcert ...
< HTTP/2 200
```

- [ ] **Step 8: Confirmar que a porta 8888 não está mais exposta**

```bash
docker ps --filter name=ffxiv_webserver --format "{{.Ports}}"
```

Esperado: linha vazia ou sem `8888`.

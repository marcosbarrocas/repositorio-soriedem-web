#!/usr/bin/env bash
#
# Deploy do backend PHP para public_html/sistema via FTP.
#
# Empacota o codigo (sem arquivos locais e sem segredos), envia um unico zip
# e extrai no servidor. O source/Boot/config.local.php (DB + Omie de producao)
# NAO e enviado por aqui: fica no servidor e persiste entre deploys.
#
# Requer: rsync, zip, curl. Credenciais de FTP no .env (FTP_HOST/USER/PASS).
# Uso: ./deploy.sh
#
set -euo pipefail
cd "$(dirname "$0")"

[ -f .env ] || { echo "Falta .env com FTP_HOST/FTP_USER/FTP_PASS"; exit 1; }
set -a; source .env; set +a
: "${FTP_HOST:?defina FTP_HOST no .env}"
: "${FTP_USER:?defina FTP_USER no .env}"
: "${FTP_PASS:?defina FTP_PASS no .env}"

REMOTE="public_html/sistema"
BASE_URL="https://www.soriedem.com.br/sistema"
TS="$(date +%Y%m%d_%H%M%S)"
ZIP="release_${TS}.zip"
STAGE="$(mktemp -d)"
EXTRACTOR="$(mktemp /tmp/_deploy_extract.XXXX.php)"
TOKEN="$(head -c 16 /dev/urandom | od -An -tx1 | tr -d ' \n')"

cleanup() { rm -rf "$STAGE" "/tmp/${ZIP}" "$EXTRACTOR"; }
trap cleanup EXIT

echo ">> Empacotando codigo..."
rsync -a \
  --exclude '.git' --exclude '.env' --exclude '.gitignore' \
  --exclude 'router-local.php' --exclude 'deploy.sh' \
  --exclude 'source/Boot/config.local.php' \
  --exclude 'error_log' --exclude '.ftpquota' --exclude '*.log' \
  --exclude 'deploy/' --exclude '*.zip' \
  --exclude 'themes/admin/assets/video/*.mp4' \
  --exclude 'themes/admin/assets/video/*.webm' \
  ./ "$STAGE/"

echo ">> Compactando..."
( cd "$STAGE" && zip -rqX "/tmp/${ZIP}" . )
echo "   $(du -h "/tmp/${ZIP}" | cut -f1)"

# Extrator temporario, protegido por token de uso unico (apaga a si mesmo)
cat > "$EXTRACTOR" <<PHP
<?php
if (!isset(\$_GET['t']) || !hash_equals('${TOKEN}', \$_GET['t'])) { http_response_code(403); exit('forbidden'); }
\$zip = basename(\$_GET['zip'] ?? '');
\$z = new ZipArchive();
if (\$z->open(__DIR__ . '/' . \$zip) !== true) { http_response_code(500); exit('zip open fail'); }
\$z->extractTo(__DIR__);
\$z->close();
@unlink(__DIR__ . '/' . \$zip);
@unlink(__FILE__);
echo 'ok';
PHP

echo ">> Enviando extrator e zip..."
curl -sf -T "$EXTRACTOR" "ftp://${FTP_HOST}/${REMOTE}/_deploy_extract.php" --user "${FTP_USER}:${FTP_PASS}"
curl -sf -T "/tmp/${ZIP}" "ftp://${FTP_HOST}/${REMOTE}/${ZIP}" --user "${FTP_USER}:${FTP_PASS}"

echo ">> Extraindo no servidor..."
RESP="$(curl -s "${BASE_URL}/_deploy_extract.php?t=${TOKEN}&zip=${ZIP}")"
echo "   resposta: ${RESP}"
[ "$RESP" = "ok" ] || { echo "!! Extracao falhou"; exit 1; }

echo ">> Deploy concluido em ${BASE_URL}"

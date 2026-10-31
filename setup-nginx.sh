#!/usr/bin/env bash
# =============================================================================
#  setup-nginx.sh - Setup web server Nginx di Ubuntu 24.04 sampai online + HTTPS
#
#  Pemakaian:
#    sudo bash setup-nginx.sh -d domainanda.com -e email@anda.com [opsi]
#
#  Opsi:
#    -d, --domain DOMAIN     Domain utama (wajib), contoh: contoh.com
#    -e, --email EMAIL       Email untuk Let's Encrypt (wajib kecuali --no-ssl)
#    -u, --user USER         Pemilik file website (default: user yang menjalankan sudo)
#        --php               Install PHP-FPM dan aktifkan PHP di server block
#        --no-www            Jangan sertakan www.DOMAIN
#        --no-ssl            Lewati pemasangan SSL (HTTP saja)
#        --skip-dns-check    Lewati pengecekan DNS (mis. domain di-proxy Cloudflare)
#    -h, --help              Tampilkan bantuan
#
#  Script aman dijalankan ulang, dan bisa dijalankan lagi untuk domain lain.
# =============================================================================
set -euo pipefail

# ---------- helper ----------
GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; BLUE='\033[0;34m'; NC='\033[0m'
step() { echo -e "\n${BLUE}==> $*${NC}"; }
ok()   { echo -e "${GREEN}[OK]${NC} $*"; }
warn() { echo -e "${YELLOW}[PERINGATAN]${NC} $*"; }
die()  { echo -e "${RED}[GAGAL]${NC} $*" >&2; exit 1; }

usage() { sed -n '2,19p' "$0" | sed 's/^# \{0,1\}//'; exit 0; }

# ---------- argumen ----------
DOMAIN=""
EMAIL=""
OWNER="${SUDO_USER:-root}"
WITH_PHP=0
WITH_WWW=1
WITH_SSL=1
CHECK_DNS=1

while [[ $# -gt 0 ]]; do
  case "$1" in
    -d|--domain)      DOMAIN="${2:-}"; shift 2 ;;
    -e|--email)       EMAIL="${2:-}"; shift 2 ;;
    -u|--user)        OWNER="${2:-}"; shift 2 ;;
    --php)            WITH_PHP=1; shift ;;
    --no-www)         WITH_WWW=0; shift ;;
    --no-ssl)         WITH_SSL=0; shift ;;
    --skip-dns-check) CHECK_DNS=0; shift ;;
    -h|--help)        usage ;;
    *) die "Opsi tidak dikenal: $1 (lihat --help)" ;;
  esac
done

# ---------- validasi ----------
[[ $EUID -eq 0 ]] || die "Jalankan dengan sudo: sudo bash $0 -d domain.com -e email@domain.com"

DOMAIN="${DOMAIN,,}"
DOMAIN="${DOMAIN#http://}"; DOMAIN="${DOMAIN#https://}"; DOMAIN="${DOMAIN#www.}"; DOMAIN="${DOMAIN%/}"
[[ -n $DOMAIN ]] || die "Domain wajib diisi: -d domainanda.com"
[[ $DOMAIN =~ ^([a-z0-9]([a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,}$ ]] || die "Format domain tidak valid: $DOMAIN"

if [[ $WITH_SSL -eq 1 ]]; then
  [[ $EMAIL =~ ^[^@[:space:]]+@[^@[:space:]]+\.[^@[:space:]]+$ ]] \
    || die "Email wajib & valid untuk SSL: -e email@anda.com (atau pakai --no-ssl)"
fi

id "$OWNER" &>/dev/null || die "User '$OWNER' tidak ada"

# shellcheck disable=SC1091
. /etc/os-release
[[ ${ID:-} == "ubuntu" && ${VERSION_ID:-} == "24.04" ]] \
  || warn "Script ini dibuat untuk Ubuntu 24.04 (terdeteksi: ${PRETTY_NAME:-tidak diketahui})"

WEB_BASE="/var/www/${DOMAIN}"
WEB_ROOT="${WEB_BASE}/public_html"
NGINX_CONF="/etc/nginx/sites-available/${DOMAIN}"
SERVER_NAMES="$DOMAIN"
[[ $WITH_WWW -eq 1 ]] && SERVER_NAMES="$DOMAIN www.$DOMAIN"

echo "-----------------------------------------------------"
echo " Domain      : $SERVER_NAMES"
echo " Web root    : $WEB_ROOT"
echo " Pemilik file: $OWNER"
echo " PHP         : $([[ $WITH_PHP -eq 1 ]] && echo ya || echo tidak)"
echo " SSL         : $([[ $WITH_SSL -eq 1 ]] && echo "ya ($EMAIL)" || echo tidak)"
echo "-----------------------------------------------------"

export DEBIAN_FRONTEND=noninteractive
export NEEDRESTART_MODE=a   # cegah prompt "restart services" di Ubuntu 24.04
APT_OPTS=(-y -o Dpkg::Options::=--force-confdef -o Dpkg::Options::=--force-confold)

# ---------- 1. update sistem ----------
step "1. Update sistem"
apt-get update
apt-get "${APT_OPTS[@]}" upgrade
ok "Sistem ter-update"

# ---------- 2. install paket ----------
step "2. Install Nginx, UFW, Certbot, dan tools"
apt-get "${APT_OPTS[@]}" install nginx ufw curl dnsutils certbot python3-certbot-nginx
systemctl enable --now nginx
ok "Nginx $(nginx -v 2>&1 | cut -d/ -f2) terpasang & berjalan"

PHP_SOCK=""
if [[ $WITH_PHP -eq 1 ]]; then
  step "2b. Install PHP-FPM"
  # Sengaja php-fpm (bukan paket "php") agar Apache tidak ikut terinstall
  apt-get "${APT_OPTS[@]}" install php-fpm php-mysql php-curl php-mbstring php-xml php-zip php-gd php-intl
  PHP_VER="$(ls /etc/php | sort -V | tail -n1)"
  PHP_SOCK="/run/php/php${PHP_VER}-fpm.sock"
  systemctl enable --now "php${PHP_VER}-fpm"
  ok "PHP ${PHP_VER}-FPM berjalan (socket: $PHP_SOCK)"
fi

# ---------- 3. firewall ----------
step "3. Konfigurasi firewall (UFW)"
SSH_PORT="$(sshd -T 2>/dev/null | awk '/^port /{print $2; exit}' || true)"
SSH_PORT="${SSH_PORT:-22}"
ufw allow OpenSSH >/dev/null
ufw allow "${SSH_PORT}/tcp" >/dev/null       # jaga-jaga jika SSH bukan port 22
ufw allow 'Nginx Full' >/dev/null            # port 80 + 443
ufw --force enable >/dev/null
ok "Firewall aktif: SSH (${SSH_PORT}), HTTP (80), HTTPS (443) dibuka"

# ---------- 4. direktori website ----------
step "4. Membuat direktori website"
mkdir -p "$WEB_ROOT"

if ! compgen -G "${WEB_ROOT}/index.*" >/dev/null; then
  cat > "${WEB_ROOT}/index.html" <<EOF
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>${DOMAIN}</title>
  <style>
    body{font-family:system-ui,sans-serif;display:grid;place-items:center;min-height:100vh;margin:0;background:#0f172a;color:#e2e8f0}
    main{text-align:center;padding:2rem}
    h1{font-size:2rem;margin:0 0 .5rem}
    code{background:#1e293b;padding:.2rem .5rem;border-radius:4px}
  </style>
</head>
<body>
  <main>
    <h1>${DOMAIN} sudah online</h1>
    <p>Nginx berjalan. Ganti file ini di <code>${WEB_ROOT}</code></p>
  </main>
</body>
</html>
EOF
  ok "Halaman contoh dibuat: ${WEB_ROOT}/index.html"
else
  ok "File index sudah ada, tidak ditimpa"
fi

# Pemilik = user Anda (bisa upload tanpa sudo), grup = www-data (dibaca Nginx/PHP).
# setgid (2xxx) pada folder membuat file baru otomatis ikut grup www-data.
chown -R "${OWNER}:www-data" "$WEB_BASE"
find "$WEB_BASE" -type d -exec chmod 2755 {} +
find "$WEB_BASE" -type f -exec chmod 0644 {} +
ok "Permission diatur (${OWNER}:www-data, folder 2755, file 644)"

# ---------- 5. hardening global ----------
step "5. Konfigurasi global Nginx"
{
  echo "# Dibuat oleh setup-nginx.sh"
  echo "server_tokens off;            # sembunyikan versi Nginx"
  echo "client_max_body_size 64m;     # batas ukuran upload"
  if ! grep -Eq '^\s*gzip_types' /etc/nginx/nginx.conf; then
    echo "gzip_vary on;"
    echo "gzip_proxied any;"
    echo "gzip_comp_level 5;"
    echo "gzip_min_length 256;"
    echo "gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript image/svg+xml;"
  fi
} > /etc/nginx/conf.d/00-hardening.conf
ok "/etc/nginx/conf.d/00-hardening.conf"

# ---------- 6. server block ----------
step "6. Membuat server block untuk ${DOMAIN}"
INDEX="index.html index.htm"
TRY_FILES="\$uri \$uri/ =404"
PHP_BLOCK=""
if [[ $WITH_PHP -eq 1 ]]; then
  INDEX="index.php index.html index.htm"
  TRY_FILES="\$uri \$uri/ /index.php?\$query_string"
  PHP_BLOCK=$(cat <<EOF

    # Teruskan file .php ke PHP-FPM
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:${PHP_SOCK};
    }
EOF
)
fi

if [[ -f $NGINX_CONF ]]; then
  BACKUP="${NGINX_CONF}.bak.$(date +%Y%m%d%H%M%S)"
  cp "$NGINX_CONF" "$BACKUP"
  warn "Config lama di-backup ke $BACKUP"
fi

cat > "$NGINX_CONF" <<EOF
# Server block untuk ${DOMAIN} - dibuat oleh setup-nginx.sh
server {
    listen 80;
    listen [::]:80;
    server_name ${SERVER_NAMES};

    root ${WEB_ROOT};
    index ${INDEX};

    access_log /var/log/nginx/${DOMAIN}.access.log;
    error_log  /var/log/nginx/${DOMAIN}.error.log;

    # Header keamanan dasar
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files ${TRY_FILES};
    }
${PHP_BLOCK}

    # Cache file statis di browser
    location ~* \.(css|js|jpg|jpeg|png|gif|webp|avif|svg|ico|woff2?|ttf|eot|mp4|webm)\$ {
        expires 30d;
        access_log off;
    }

    # Blokir file tersembunyi (.git, .env, .htaccess) kecuali .well-known (dipakai SSL)
    location ~ /\.(?!well-known) {
        deny all;
    }
}
EOF

ln -sf "$NGINX_CONF" "/etc/nginx/sites-enabled/${DOMAIN}"
if [[ -L /etc/nginx/sites-enabled/default ]]; then
  rm -f /etc/nginx/sites-enabled/default
  ok "Site default Nginx dinonaktifkan"
fi

nginx -t || die "Konfigurasi Nginx error, periksa $NGINX_CONF"
systemctl reload nginx
ok "Server block aktif: $NGINX_CONF"

# ---------- 7. cek DNS ----------
CERT_DOMAINS=()
if [[ $WITH_SSL -eq 1 ]]; then
  step "7. Cek DNS domain"
  SERVER_IP="$(curl -4 -fsS --max-time 10 https://api.ipify.org || true)"

  resolve_a() { dig +short A "$1" @103.142.202.252 2>/dev/null | grep -E '^[0-9]+(\.[0-9]+){3}$' || true; }

  if [[ $CHECK_DNS -eq 0 ]]; then
    warn "Cek DNS dilewati (--skip-dns-check)"
    CERT_DOMAINS=("$DOMAIN"); [[ $WITH_WWW -eq 1 ]] && CERT_DOMAINS+=("www.$DOMAIN")
  elif [[ -z $SERVER_IP ]]; then
    warn "Tidak bisa mendeteksi IP publik server, lanjut tanpa cek DNS"
    CERT_DOMAINS=("$DOMAIN"); [[ $WITH_WWW -eq 1 ]] && CERT_DOMAINS+=("www.$DOMAIN")
  else
    echo "IP publik server: $SERVER_IP"
    for name in $SERVER_NAMES; do
      ips="$(resolve_a "$name" | tr '\n' ' ')"
      if grep -qw -- "$SERVER_IP" <<<"$ips"; then
        ok "$name -> $ips"
        CERT_DOMAINS+=("$name")
      else
        warn "$name -> ${ips:-(belum ada A record)} (seharusnya $SERVER_IP)"
      fi
    done
    if [[ ! " ${CERT_DOMAINS[*]} " =~ " ${DOMAIN} " ]]; then
      warn "DNS ${DOMAIN} belum mengarah ke server ini. SSL dilewati."
      warn "Atur A record dulu, tunggu propagasi, lalu jalankan ulang script ini."
      CERT_DOMAINS=()
    fi
  fi
fi

# ---------- 8. SSL Let's Encrypt ----------
SSL_DONE=0
if [[ ${#CERT_DOMAINS[@]} -gt 0 ]]; then
  step "8. Memasang SSL Let's Encrypt untuk: ${CERT_DOMAINS[*]}"
  CERT_ARGS=()
  for d in "${CERT_DOMAINS[@]}"; do CERT_ARGS+=(-d "$d"); done

  if certbot --nginx "${CERT_ARGS[@]}" --non-interactive --agree-tos -m "$EMAIL" --redirect; then
    SSL_DONE=1
    ok "SSL terpasang, HTTP otomatis redirect ke HTTPS"
    systemctl enable --now certbot.timer >/dev/null 2>&1 || true
    ok "Perpanjangan otomatis aktif (certbot.timer)"
  else
    warn "Certbot gagal. Cek: DNS, port 80 terbuka dari internet, firewall provider/router."
  fi
fi

# ---------- ringkasan ----------
PROTO="http"; [[ $SSL_DONE -eq 1 ]] && PROTO="https"
echo
echo -e "${GREEN}=====================================================${NC}"
echo -e "${GREEN} SELESAI${NC}"
echo -e "${GREEN}=====================================================${NC}"
echo " Website      : ${PROTO}://${DOMAIN}"
echo " Upload file  : ${WEB_ROOT}/"
echo " Config Nginx : ${NGINX_CONF}"
echo " Log          : /var/log/nginx/${DOMAIN}.access.log"
echo "                /var/log/nginx/${DOMAIN}.error.log"
[[ $SSL_DONE -eq 1 ]] && echo " Sertifikat   : /etc/letsencrypt/live/${DOMAIN}/"
[[ $WITH_PHP -eq 1 ]] && echo " PHP-FPM      : ${PHP_SOCK}"
echo
echo " Cek cepat    : curl -I ${PROTO}://${DOMAIN}"

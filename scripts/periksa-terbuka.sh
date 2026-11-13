#!/bin/sh
# Periksa file sensitif yang tidak boleh bisa diunduh publik.
# Jalankan hanya terhadap domain milik sendiri.
# Pemakaian: sh scripts/periksa-terbuka.sh http://localhost:8010

DOMAIN="${1:?Sertakan domain, contoh http://localhost:8010}"
DOMAIN=$(printf '%s' "$DOMAIN" | sed 's#/$##')

PATHS="/.env /.env.local /.env.production /.env.development /.env.backup /.env.bak /.env.old /.git/config /.git/HEAD /.gitignore /.DS_Store /.vscode/settings.json /.idea/workspace.xml /backup.zip /backup.tar.gz /db.sql /dump.sql /database.sql /database.sqlite /data.db /config.json /config.yml /docker-compose.yml /.npmrc /phpinfo.php /server-status /swagger.json /openapi.json /graphql /vendor/ /storage/logs/laravel.log"

GAGAL=0
for p in $PATHS; do
    KODE=$(curl -s -o /dev/null -w '%{http_code}' "$DOMAIN$p")
    if [ "$KODE" = "200" ]; then
        echo "BAHAYA  $p -> $KODE"
        GAGAL=1
    else
        echo "ok      $p -> $KODE"
    fi
done

if [ "$GAGAL" = "1" ]; then
    echo "Ada file sensitif yang bisa diakses publik."
    exit 1
fi
echo "Tidak ada file sensitif yang bisa diakses publik."

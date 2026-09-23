#!/bin/sh
set -eu

PORT="${PORT:-8080}"
# Railway assigns PORT dynamically. Make Apache listen on that port.
sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s#<VirtualHost \*:[0-9]+>#<VirtualHost *:${PORT}>#" /etc/apache2/sites-available/000-default.conf

# Import the schema once the Railway MySQL service is reachable.
# The app can still boot if DB initialization is temporarily unavailable;
# the next deployment/start will retry it.
if [ -n "${MYSQLHOST:-}" ] && [ -n "${MYSQLUSER:-}" ] && [ -n "${MYSQLDATABASE:-}" ]; then
  echo "[Mr Rashidi] Waiting for Railway MySQL..."
  i=0
  while [ "$i" -lt 30 ]; do
    if mysqladmin ping \
      -h"${MYSQLHOST}" \
      -P"${MYSQLPORT:-3306}" \
      -u"${MYSQLUSER}" \
      -p"${MYSQLPASSWORD:-}" \
      --silent >/dev/null 2>&1; then
      break
    fi
    i=$((i+1))
    sleep 2
  done

  if mysqladmin ping -h"${MYSQLHOST}" -P"${MYSQLPORT:-3306}" -u"${MYSQLUSER}" -p"${MYSQLPASSWORD:-}" --silent >/dev/null 2>&1; then
    echo "[Mr Rashidi] Initializing database schema..."
    mysql \
      -h"${MYSQLHOST}" \
      -P"${MYSQLPORT:-3306}" \
      -u"${MYSQLUSER}" \
      -p"${MYSQLPASSWORD:-}" \
      "${MYSQLDATABASE}" < /var/www/html/schema.sql || true
    echo "[Mr Rashidi] Database schema ready."
  else
    echo "[Mr Rashidi] MySQL not reachable yet; application will retry on next start."
  fi
fi

exec "$@"

#!/usr/bin/env bash

substitutions="\${XDEBUG_PORT} \${XDEBUG_IDE_KEY}"

if [ -f /etc/php/5.6/mods-available/xdebug.ini.template ]; then
    envsubst "${substitutions}" < /etc/php/5.6/mods-available/xdebug.ini.template > /etc/php/5.6/mods-available/xdebug.ini
    rm -f /etc/php/5.6/mods-available/xdebug.ini.template

    host_ip=$(/sbin/ip route|awk '/default/ { print $3 }')
    sed -i "s/xdebug\.remote_host\=.*/xdebug\.remote_host\=${host_ip}/g" /etc/php/5.6/mods-available/xdebug.ini
fi

"$@"

#!/bin/bash
# Azure Portal → Configuration → General settings → Startup Command:
#   bash /home/site/wwwroot/azure/startup.sh
set -e
CFG="/home/site/wwwroot/azure/nginx/default"
if [ -f "$CFG" ]; then
  cp "$CFG" /etc/nginx/sites-enabled/default
  service nginx reload || service nginx restart
fi

#!/bin/bash
DATE=$(date +'%Y%m%d')
TIME=$(date +'%H%M%S')
SCRIPT_DIR=$(cd $(dirname $0) && pwd)
SCRIPT_NAME=$(basename $0)

RLS_FILE=${SCRIPT_DIR}_${DATE}_${TIME}.tgz

cd ${SCRIPT_DIR}/..

tar \
  --exclude '*.zip' \
  --exclude '*.tar' \
  --exclude '*.tar.gz' \
  --exclude 'config/backup/*' \
  --exclude 'config/release/*' \
  --exclude 'config/work/administrators' \
  --exclude 'config/work/domains' \
  --exclude 'config/work/accounts' \
  --exclude 'config/work/addresses' \
  --exclude 'config/work/tests' \
  --exclude 'config/work/passwords' \
  --exclude 'config/*.txt' \
  --exclude 'config/*.cfg' \
  --exclude 'logs/*' \
  --exclude '*bak' \
  --exclude '.git*' \
  --exclude 'info.php' \
  -czvf ${RLS_FILE} $(basename ${SCRIPT_DIR})

echo ""
echo "===[ Release package ]==="
echo ""
echo "> ${RLS_FILE}"
echo ""

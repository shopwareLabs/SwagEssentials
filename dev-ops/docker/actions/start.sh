#!/usr/bin/env bash

echo "COMPOSE_PROJECT_NAME: ${COMPOSE_PROJECT_NAME}"

dev-ops/docker/containers/scriptcreator.sh
docker-compose build --parallel && docker-compose up -d
wait

echo "All containers started successfully"
echo "Web server IP: http://10.123.123.30"

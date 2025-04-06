#!/bin/bash

set -e

# Set variables from docker.env
set -a && source code/docker.env && set +a

docker-compose down -v

docker-compose up --no-start --build

docker-compose run --rm php /bin/bash -c 'composer install'

docker-compose up --build --wait elasticsearch

docker-compose cp create_index.json elasticsearch:/home/create_index.json
docker-compose exec elasticsearch curl -X PUT "http://localhost:9200/otus-shop" -H "Content-Type: application/json" --data-binary @/home/create_index.json

docker-compose cp books.json elasticsearch:/home/books.json
docker-compose exec elasticsearch curl -X POST "http://localhost:9200/_bulk" -H "Content-Type: application/json" --data-binary @/home/books.json

docker-compose start
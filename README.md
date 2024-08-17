# SevereHeadache/authservice

Authentication service

## Docker install
- Configure docker environment variables
- Build containers
```sh
docker compose up -d
```
- Create DB schema
```sh
docker exec authservice-php php bin/doctrine orm:schema-tool:create
```

## Application CLI
```sh
docker exec -it authservice-php php bin/app
```

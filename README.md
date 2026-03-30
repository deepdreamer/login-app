Ukol pro vytvoreni prihlasovaci/registracni aplikace.

## Zpusteni aplikace podrobne

Sestaveni kontejneru a zpusteni kontejneru:
```bash
docker compose up --build -d
```

Instalace zavyslosti:
```bash
docker compose run --rm app composer install
```
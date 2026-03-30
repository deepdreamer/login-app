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

Inicializace databaze (vytvoreni tabulek a vlozeni testovacich uzivatelu):
```bash
docker compose exec -T mysql mysql -uroot -prootsecret < database/setup.sql
```

Vsichni testovaci uzivatele maji heslo `secret123`.
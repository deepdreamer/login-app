Ukol pro vytvoreni prihlasovaci/registracni aplikace.

## Zpusteni aplikace podrobne

Vytvoreni konfiguracniho souboru pro prostredi:
```bash
cp .env.example .env
```

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

Hesla jsou hashovana algoritmem **Argon2id** pres `Nette\Security\Passwords`. Konfigurace je v `config/common.neon` (`security.passwords: Nette\Security\Passwords(::PASSWORD_ARGON2ID)`).

Regenerace autoloaderu (nutne po pridani/odstraneni PHP souboru):
```bash
docker compose exec app composer dump-autoload
```

Vstup do MySQL konzole:
```bash
docker compose exec mysql mysql -uroot -prootsecret
```
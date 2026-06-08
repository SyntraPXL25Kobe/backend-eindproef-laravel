# Setup

## Vereisten

| Tool     | Versie |
| -------- | ------ |
| PHP      | ≥ 8.2  |
| Composer | ≥ 2.x  |
| Node.js  | ≥ 20.x |
| pnpm     | ≥ 9.x  |
| MySQL    | ≥ 8.0  |

---

## Installatie

### 1. Repository klonen

```bash
git clone <repo-url>
cd <project-map>
```

### 2. PHP-afhankelijkheden installeren

```bash
composer install
```

### 3. Omgevingsbestand aanmaken

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database configureren

Pas `.env` aan:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<database_naam>
DB_USERNAME=<gebruikersnaam>
DB_PASSWORD=<wachtwoord>
```

### 5. Database migraties uitvoeren

```bash
php artisan migrate --seed
```

### 6. Frontend-afhankelijkheden installeren

```bash
pnpm install
```

### 7. Wayfinder routes genereren

```bash
php artisan wayfinder:generate --with-form
```

> Voer dit opnieuw uit na elke wijziging aan routes of controllers.

---

## Ontwikkeling

Start de Laravel-server en Vite-dev-server gelijktijdig:

````bash
composer run dev
---

## Veelgebruikte commando's

### Artisan

```bash
php artisan migrate          # Migraties uitvoeren
php artisan migrate:fresh --seed  # Database resetten + seeden
php artisan route:list       # Alle routes tonen
php artisan wayfinder:generate    # Type-safe routes regenereren
````

### pnpm

```bash
pnpm dev          # Vite dev-server starten
pnpm build        # Productie-build aanmaken
pnpm lint         # ESLint uitvoeren
pnpm format       # Prettier uitvoeren
pnpm typecheck    # TypeScript-typecheck uitvoeren
```

### Composer

```bash
composer lint     # Pint (PHP code stijl) uitvoeren
composer test     # Testsuite uitvoeren
```

---

## Productie-build

```bash
pnpm build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

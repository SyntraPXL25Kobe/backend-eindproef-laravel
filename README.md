# Local Event Crew Planner

Een full-stack webapplicatie voor kleine events, festivals en lokale organisaties om vrijwilligers, shifts en no-shows overzichtelijk te beheren.

## Doel van het project

Veel kleine eventorganisaties werken nog met Excel, WhatsApp en losse lijsten om vrijwilligers te plannen. Daardoor ontstaan fouten, dubbele afspraken en onduidelijkheid op de eventdag.

Deze applicatie lost dat op door:

- events, zones en shifts centraal te beheren
- vrijwilligers zich te laten inschrijven op open shifts
- coördinatoren goedkeuring en check-ins te laten opvolgen
- no-shows snel te kunnen vervangen
- rapportage en overzicht te bieden in een admin panel

## Functionaliteiten

- Authenticatie en rolgebaseerde toegang
- Rollenbeheer met admin, coordinator en crew
- Eventbeheer
- Zonebeheer per event
- Shiftbeheer met capaciteit en skills
- Vrijwilligersprofielen met skills
- Overzicht van open shifts
- Inschrijven op shifts
- Goedkeuren of afkeuren van aanvragen
- Definitieve assignment-flow
- Check-in board voor de eventdag
- No-show registratie en vervanging
- Notificaties bij wijzigingen
- KPI-overzicht en rapportage
- Demo data en tests voor presentatie

## Rollen

### Admin

- Gebruikers beheren
- Rollen en permissies beheren
- Globale rapportage bekijken
- Alles kunnen overzien via het admin panel

### Coordinator

- Events aanmaken en publiceren
- Zones en shifts beheren
- Aanvragen beoordelen
- Check-ins opvolgen
- No-shows markeren en vervanging activeren

### Crew member

- Eigen profiel beheren
- Skills toevoegen
- Open shifts bekijken
- Zich inschrijven op shifts
- Eigen planning en status opvolgen

## Tech stack

### Backend

- Laravel 13
- PHP 8.3
- Fortify voor authenticatie
- Spatie Laravel Permission voor rollen en permissies
- Wayfinder voor type-safe Laravel naar React routing
- Filament v5 voor admin panel

### Frontend

- React 19
- Inertia.js
- TypeScript
- Vite
- Tailwind CSS 4
- Radix UI
- Lucide icons

### Development tools

- Pest voor tests
- Laravel Pint voor code style
- ESLint en Prettier voor frontend formatting
- pnpm als package manager

## Projectstructuur

- app/ - Laravel business logic
- bootstrap/ - app bootstrap en providers
- config/ - configuratiebestanden
- database/ - migraties, factories en seeders
- resources/ - React frontend, styles en assets
- routes/ - web- en console routes
- tests/ - feature- en unit tests
- public/ - publieke assets en build output

## Installatie

### Vereisten

- PHP 8.3 of hoger
- Composer 2.x
- Node.js 20.x of hoger
- pnpm 11.5.2
- MySQL 8.0 of een andere ondersteunde database

### Stappen

1. Clone de repository

```bash
git clone <repo-url>
cd <project-map>
```

2. Installeer PHP dependencies

```bash
composer install
```

3. Maak een .env bestand aan

```bash
cp .env.example .env
php artisan key:generate
```

4. Configureer de database in .env

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<database_naam>
DB_USERNAME=<gebruikersnaam>
DB_PASSWORD=<wachtwoord>
```

5. Voer migraties uit

```bash
php artisan migrate --seed
```

6. Installeer frontend dependencies

```bash
pnpm install
```

7. Start de development server

```bash
composer run dev
```

## Handige commando's

### Laravel

```bash
php artisan migrate
php artisan migrate:fresh --seed
php artisan route:list
php artisan test
```

### Frontend

```bash
pnpm dev
pnpm build
pnpm lint
pnpm format
pnpm types:check
```

### Code style

```bash
composer lint
composer test
```

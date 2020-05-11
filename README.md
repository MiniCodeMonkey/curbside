# Curb Run

## Stack

* **Backend:** Laravel 7
* **Frontend:** Vue.js, Tailwind CSS (using components from Tailwind UI)
* **Infrastructure:** Hosted on AWS Lambda via Laravel Vapor

## Development

### Prerequisites

* PHP 7.2+
* MySQL 8+ or MariaDB
* [Composer](https://getcomposer.org)
* [Node.js v10+](https://nodejs.org/en/)

* [Twilio](https://www.twilio.com) account (for sending text messages)
* [Geocodio](https://www.geocod.io) account (for importing some grocery stores)
* Grovery store accounts: Harris Teeter, Albertsons (for being able to find pickup slots for these chains)

### Clone the repository

```
git clone git@github.com:MiniCodeMonkey/curbside.git
cd curbside
```

### Install dependencies

```
composer install
npm install
```

### Configure environment

```
cp .env.example .env
```

Edit `.env`. You will need to at least configure a database. Want to know more about env configuration? Check out the [Laravel docs](https://laravel.com/docs/7.x/configuration).

### Development server

```
# Run database migrations
php artisan migrate --seed
```

> Running this will also fetch stores from each individual chains website, so it will take a few minutes to run.

```
# Start development server and frontend asset watcher
php artisan serve
npm run watch
```

## Deployment

```
vapor deploy production
```

# Curb Run

## Stack

* **Backend:** Laravel 7
* **Frontend:** Vue.js, Tailwind CSS (using components from Tailwind UI)
* **Infrastructure:** Hosted on AWS Lambda via Laravel Vapor

## Development

### Install dependencies
```
composer install
npm install
```

### Development server
```
# Run database migrations
php artisan migrate --seed

# Start development server and frontend asset watcher
php artisan serve
npm run watch
```

## Deployment

```
vapor deploy production
```

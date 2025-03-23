<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

## Prerequisites
[Project Requirements](https://drive.google.com/drive/folders/1v66k-q8olO1BLjYQvcMehJ1VV8TUZXnO?usp=drive_link)

- **PHP **
- **Composer**
- **Node.js & npm**
- **PostgreSQL**

## Installation Guide

Follow these steps to set up the Expense Tracker System on your Windows machine:

1. **Clone the Repository:**

   Open the terminal and run:

   ```bash
   git clone https://github.com/Ggwepq/exts.git
   cd exts
   ```

2. **Install PHP Dependencies:**

   ```bash
   composer install
   ```

3. **Install NodeJS Dependencies:**

   ```bash
   npm install
   ```

4. Open PGAdmin and create a database


5. **Setup .env variables** (name, user, password depends on how you install postgres and create the database)

   ```bash
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=your_database_name
    DB_USERNAME=your_username
    DB_PASSWORD=your_password  
   ```

6. **Generate app key**

   ```bash
   php artisan key:generate
   ```


7. **Run database migrations**

   ```bash
   php artisan migrate:fresh --seed
   ```

8. **Serve the project**

   ```bash
   php artisan serve
   ```

8. **Open a new terminal and run**

   ```bash
   npm run dev
   ```

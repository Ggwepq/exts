<p align="center">
  <img src="public/img/sample-logo.png" alt="GastaBaby Logo" width="200"/>

</p>
<h2 align="center">GastaBaby</h2>

### 📋 Description

A personal expense tracker that empowers you to take control of your money. With features like budget planning, detailed financial reports, and AI-powered insights, GastaBaby helps you manage and monitor your daily expenses, grow your savings, and make informed financial decisions with ease.

---

### 💡 Features

1️⃣ **Transaction Management**

2️⃣ **Account Management**

3️⃣ **Budget Planning**

4️⃣ **AI Insights**

5️⃣ **Financial Reports**

---

### 🛠️ Prerequisites

> [!TIP]
> Requirements can be downloaded on this [folder](https://drive.google.com/drive/folders/1v66k-q8olO1BLjYQvcMehJ1VV8TUZXnO?usp=drive_link).

- PHP
- Composer
- Node.js & npm
- PostgreSQL



---

### 📦 Installation Guide

Follow these steps to set up the GastaBaby system on your machine:

1. **Clone the Repository**

   ```bash
   git clone https://github.com/Ggwepq/exts.git
   cd exts
   ```

2. **Install PHP Dependencies**

   ```bash
   composer install
   ```

3. **Install NodeJS Dependencies**

   ```bash
   npm install
   ```

4. **Create a PostgreSQL Database**

   Open PGAdmin and create a new database.

5. **Set Up Environment Variables**

   Update the `.env` file:

   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=your_database_name
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```
> [!TIP]
> Email variables are required. [Watch here](https://youtu.be/PeK_tD4T3Og?si=pZXji2BequAURexh) to create one.

6. **Generate App Key**

   ```bash
   php artisan key:generate
   ```

7. **Run Migrations and Seed the Database**

   ```bash
   php artisan migrate:fresh --seed
   ```

8. **Serve the Laravel Project**

   ```bash
   php artisan serve
   ```

9. **Compile Frontend Assets**

   Open a new terminal:

   ```bash
   npm run dev
   ```

---

<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
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

# Cinemaa API

Cinemaa API is a Laravel 8 based online movie platform where users can watch movies, comment, rate, and chat. The platform features a forum and allows registered users to submit movie links for moderation. An admin panel is available for admins to manage users, movies, and forum activities.

## Features

- **User Registration and Authentication**
  - Register new users
  - User login and logout
  - Password reset

- **Movie Management**
  - Watch movies
  - Submit movie links for moderation
  - Rate and comment on movies

- **Forum**
  - Create and participate in discussions
  - Comment on forum threads

- **Chat**
  - Real-time chat functionality

- **Admin Panel**
  - Manage users
  - Approve or deny submitted movie links
  - Moderate forum activities

## Technologies Used

- **Backend:** Laravel 8
- **Database:** MySQL
- **Authentication:** Laravel Sanctum
- **Real-time Communication:** Laravel Echo, Pusher
- **Frontend:** Vue.js
- **Others:** Composer, NPM, Webpack

## Installation

1. **Clone the repository:**

    ```bash
    git clone https://github.com/Easy987/cinemaa-api.git
    cd cinemaa-api
    ```

2. **Install dependencies:**

    ```bash
    composer install
    npm install
    ```

3. **Environment Setup:**

    Copy the `.env.example` file to `.env` and update the necessary environment variables, especially the database credentials.

    ```bash
    cp .env.example .env
    ```

4. **Generate application key:**

    ```bash
    php artisan key:generate
    ```

5. **Run migrations:**

    ```bash
    php artisan migrate
    ```

6. **Seed the database (optional):**

    ```bash
    php artisan db:seed
    ```

7. **Run the application:**

    ```bash
    php artisan serve
    ```

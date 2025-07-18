# Feature Request System

## Prerequisites
Before we dive in, ensure you have the following installed on your machine:

- Node.js and npm
- Composer
- PHP 8.x
- Laravel 12 installed globally via Composer

## Setup Instructions
Follow these steps to get the project up and running on your local machine.

### 1. Clone the Repository
`git clone https://github.com/thetpaingsoe/feature-request-system-be`


### 2. Backend Setup (Laravel)
Navigate into the backend directory:
`cd feature-request-system-be`

Install Composer Dependencies
`composer install`

Generate Application Key
`php artisan key:generate`

Run Database Migrations
This will create the necessary tables, including users and feature_requests.

`php artisan migrate`

Run Database Seeding
This will populate your users table with a default admin user and your feature_requests table with 50 dummy records.

`php artisan db:seed`

Default Admin Credentials:

Email: admin@gmail.com
Password: admin12345

If you ever need to reset your database and re-seed it:
`php artisan migrate:fresh --seed`

Start the Laravel Development Server
`php artisan serve`

### 3. Frontend Setup (Vue.js)
Open new terminal to setup Vue

Install Node.js Dependencies
`npm install`

Build and Serve the Frontend
For development:
`npm run dev`

## Running Tests
Backend Tests (Laravel)
`php artisan test`

Frontend Tests (Vue.js)
`npm run test:unit`
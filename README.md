# SmartBus Backend API

A Laravel-based REST API for managing smart bus transportation services, including bus tracking, route management, bookings, payments, and feedback systems.

## Features

- **User Authentication** - Registration and login with Laravel Sanctum
- **Bus Management** - Track buses, routes, and real-time status
- **Route & Stop Management** - Define routes with multiple stops and GPS coordinates
- **Booking System** - Seat reservations with fare calculation
- **Payment Integration** - Support for multiple payment methods and webhooks
- **Feedback System** - User ratings and comments for service quality
- **Role-Based Access** - Admin and passenger roles with different permissions

## Tech Stack

- **Framework:** Laravel 10.x
- **PHP:** ^8.1
- **Authentication:** Laravel Sanctum
- **Database:** MySQL
- **API:** RESTful JSON API

## Database Schema

- **users** - User accounts with roles (admin/passenger)
- **buses** - Bus fleet with capacity and status tracking
- **routes** - Bus routes with start/end locations and fares
- **stops** - Route stops with GPS coordinates
- **bookings** - Ticket reservations with seat assignments
- **payments** - Payment transactions and status
- **feedback** - User ratings and reviews

## Installation

1. Clone the repository
```bash
git clone <repository-url>
cd smartbus_backend
```

2. Install dependencies
```bash
composer install
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Update `.env` with your database credentials
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartbus
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Run migrations
```bash
php artisan migrate
```

6. Seed sample data (optional)
```bash
php artisan db:seed
```

7. Start the development server
```bash
php artisan serve
```

## API Endpoints

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout (auth required)

### Buses
- `GET /api/buses` - List all buses
- `GET /api/buses/{id}` - Get bus details
- `POST /api/buses` - Create bus (admin only)

### Routes
- `GET /api/routes` - List all routes
- `GET /api/routes/{id}` - Get route details
- `POST /api/routes` - Create route (admin only)

### Stops
- `GET /api/stops` - List all stops
- `GET /api/stops/route/{routeId}` - Get stops by route
- `POST /api/stops` - Create stop (admin only)

### Bookings
- `GET /api/bookings` - Get user bookings (auth required)
- `POST /api/bookings` - Create booking (auth required)
- `GET /api/bookings/{id}` - Get booking details (auth required)
- `PATCH /api/bookings/{id}/cancel` - Cancel booking (auth required)

### Payments
- `POST /api/payments` - Process payment (auth required)
- `GET /api/payments/{id}` - Get payment details (auth required)
- `PATCH /api/payments/{id}/status` - Update payment status (auth required)

### Feedback
- `GET /api/feedback` - List all feedback
- `POST /api/feedback` - Submit feedback (auth required)

## API Authentication

This API uses Laravel Sanctum for authentication. Include the token in requests:

```bash
Authorization: Bearer {your-token}
```

## Testing

Run the test suite:
```bash
php artisan test
```

## Development Tools

- **Laravel Pint** - Code style fixer
- **Laravel Sail** - Docker development environment
- **PHPUnit** - Testing framework

## CORS Configuration

CORS is configured to allow requests from frontend applications. Update `config/cors.php` for production settings.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

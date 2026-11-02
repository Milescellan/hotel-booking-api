# Hotel Booking API

A REST API for managing hotel rooms and guest bookings, built with Laravel 12. It prevents double-booking, finds rooms available for a date range, calculates the total price, and emails the guest a confirmation.

## Features

- **Rooms:** full CRUD with room number, type, nightly price, capacity, and status.
- **Availability search:** rooms free for a check-in/check-out range, filterable by type and minimum capacity.
- **Bookings:** full CRUD with filters for status, room, guest email, and date range.
- **Double-booking prevention:** a new booking is rejected if it overlaps an active booking for the same room.
- **Price calculation:** `total_price` is nights times the room's nightly rate, computed on the server.
- **Confirmation email** queued to the guest on every new booking.
- **Soft cancel:** `DELETE` marks a booking `cancelled` instead of removing it, so history is kept and the dates free up.
- **Validation** through Form Requests and consistent JSON through API Resources.

## Endpoints

| Method | Route | Purpose |
|---|---|---|
| GET | `/api/rooms` | List rooms |
| POST | `/api/rooms` | Create a room |
| GET | `/api/rooms/{id}` | Show a room |
| PUT/PATCH | `/api/rooms/{id}` | Update a room |
| DELETE | `/api/rooms/{id}` | Delete a room |
| GET | `/api/rooms/available?check_in=&check_out=&type=&capacity=` | Rooms free for a date range |
| GET | `/api/bookings?status=&room_id=&guest_email=&from=&to=` | List bookings |
| POST | `/api/bookings` | Create a booking |
| GET | `/api/bookings/{id}` | Show a booking |
| PUT/PATCH | `/api/bookings/{id}` | Update dates or status |
| DELETE | `/api/bookings/{id}` | Cancel a booking |

### Example: create a booking

```http
POST /api/bookings
Content-Type: application/json

{
  "room_id": 1,
  "guest_name": "Juan Dela Cruz",
  "guest_email": "juan@example.com",
  "check_in": "2026-12-20",
  "check_out": "2026-12-23"
}
```

Returns the booking with `status: "pending"` and `total_price` for 3 nights. If the room is already booked for any of those dates, it returns `422` with a `room_id` error.

## Tech stack

PHP 8.2, Laravel 12, Eloquent, SQLite (default) or MySQL, database queue, Laravel Mail.

## Run locally

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve          # http://localhost:8000
php artisan queue:work     # second terminal: sends the confirmation emails
```

`MAIL_MAILER=log` by default, so emails are written to `storage/logs/laravel.log` instead of being sent.

## Known limitations

- No authentication yet: every endpoint is public.
- Updating a booking's dates does not re-check for overlaps (only creating does).

SMARTBUS Backend - API routes
=============================

Base URL: /api

Auth
----
POST /api/auth/register
- Body: { name, email?, phone, password, role? }
- Response: user object + token

POST /api/auth/login
- Body: { email|phone, password }
- Response: token and user

POST /api/auth/logout
- Auth required (token)
- Response: 204

Buses & Routes
--------------
GET /api/buses
- Query: type (expressway|normal), route_id, q
- Returns list of buses (expressway flagged)

GET /api/buses/:id
- Returns details for a bus including route and stops

GET /api/routes
- Returns available routes

Bookings
--------
GET /api/bookings (auth)
- Returns bookings for logged-in passenger

POST /api/bookings
- Body: { user_id (or use token), bus_id, route_id, ticket_category, seat_number?, payment_method }
- If bus.type != 'expressway' and booking attempt from passenger UI that only allows expressway, return 400
- Response: booking object

GET /api/bookings/:id
- Return booking details

Feedback
--------
POST /api/feedback
- Body: { user_id (optional), bus_id, rating (1-5), comment }
- Returns saved feedback

Payments
--------
POST /api/payments/webhook
- Endpoint for payment gateways to notify payment status

POST /api/payments
- Body: { booking_id, amount, gateway }
- Initiates payment or records pay-on-bus

Notes and integration
---------------------
- Protect routes requiring authentication with Sanctum/Passport.
- Use JSON responses and consistent status codes.
- Enable CORS for the Next.js frontend origin (http://localhost:3000).
- Implement role-based access (drivers/admin) where necessary (e.g., updating bus location).

Example client flow
-------------------
1. Frontend logs in (POST /api/auth/login). It receives token (Sanctum cookie or Bearer token).
2. Frontend lists buses (GET /api/buses?type=expressway).
3. Passenger creates booking (POST /api/bookings).
4. On successful booking, frontend may call /api/payments (or record pay-on-bus).
5. Passenger can submit feedback via /api/feedback.


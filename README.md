# PHP JWT Auth API

A **simple, REST authentication API** built with PHP using **JWT-based token authentication** (no sessions).

Designed to be **stateless, secure and easy to extend**, without using any framework.

---

## Features (Implemented)

* User registration
* User login
* JWT access tokens
* Stateless authentication (no PHP sessions)
* Role-based access control (RBAC)
* Secure logout using token blacklist
* Rate limiting
* Audit logging
* Middleware-based authorization

---

## Tech Stack

* PHP (plain PHP, no framework)
* MySQL
* JWT (`firebase/php-jwt`)
* Apache (with `.htaccess`)
* REST API design

---

## Project Structure

```
.
├── index.php
├── src/
│   ├── AuditLogger.php
│   ├── AuthController.php
│   ├── Database.php
│   ├── JwtService.php
│   ├── Middleware.php
│   ├── RateLimiter.php
│   └── config.php
├── vendor/
├── composer.json
└── .htaccess
```

---

## Authentication Flow

1. User registers with email and password
2. User logs in with credentials
3. Server returns:

   * JWT access token
   * Refresh token (stored hashed in DB)
4. Client sends access token in every request using:

   ```
   Authorization: Bearer <access_token>
   ```
5. Logout revokes the access token using a blacklist

---

## API Endpoints

| Method | Endpoint                | Description               |
| ------ | ----------------------- | ------------------------- |
| POST   | `/api/v1/auth/register` | Register a new user       |
| POST   | `/api/v1/auth/login`    | Login                     |
| POST   | `/api/v1/auth/logout`   | Logout (token revocation) |
| GET    | `/api/v1/profile`       | Protected endpoint        |

---

## Authorization Header

All protected endpoints require:

```
Authorization: Bearer <access_token>
```

---

## Security Notes

* Passwords are hashed using `password_hash`
* JWTs are signed and verified server-side
* Tokens are validated before decoding
* Revoked tokens are blocked using a blacklist
* Rate limiting is applied per IP and endpoint
* Audit logs track security-sensitive actions

---

## Setup (Local)

```bash
composer install
```

* Configure database credentials in `src/config.php`
* Ensure Apache forwards the `Authorization` header (see `.htaccess`)
* Create required database tables before running

---

## Notes

* HTTPS enforcement is present but disabled for local development
* Refresh-token rotation endpoint is **not yet implemented**
* Designed to be extended into a full auth service if needed

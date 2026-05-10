You are a senior Laravel microservices architect and backend engineer.

Build a production-grade Authentication & Authorization microservice using Laravel 12, PostgreSQL, Redis, Queue Workers, and JWT (RS256 public/private key signing).

This service will be used by multiple independent microservices, including a Ticketing Service. The auth service must be fully standalone and scalable.

---

## TECH STACK

- Laravel
- PostgreSQL
- custom JWT with RS256
- REST API only
- No Blade/UI
- API-first architecture
- Clean architecture
- SOLID principles

---

## CORE REQUIREMENTS

Implement the following modules:

1. Authentication
2. Authorization
3. JWT Issuing & Validation
4. Refresh Tokens
5. Forgot Password with OTP
6. Role & Permission Management(later)
7. User Management
8. Secure Inter-service Communication

---

## AUTHENTICATION FEATURES

Implement APIs for:

- Register
- Login
- Logout
- Refresh Token
- Get Current User
- Change Password
- Forgot Password
- Verify OTP
- Reset Password

Use JWT Access Tokens with:

- RS256 signing
- Short-lived access token (15 minutes)
- Refresh token support

JWT payload should include:

- user_id
- email
- roles
- permissions
- issued_at
- expiry

Example:

{
"sub": 15,
"email": "[user@test.com](mailto:user@test.com)",
"roles": ["admin"],
"permissions": [
"ticket.create",
"ticket.reply"
]
}

---

## FORGOT PASSWORD + OTP FLOW

Implement secure OTP-based password reset.

Flow:

1. User requests forgot password
2. Generate 6-digit OTP
3. Store hashed OTP in DB
4. OTP expires in 5 minutes
5. Send OTP via queued email job
6. Verify OTP endpoint
7. Generate temporary reset token
8. Reset password endpoint
9. Revoke all old refresh tokens after password reset

Requirements:

- Never store plain OTP
- Limit OTP attempts
- Rate limit forgot password endpoint
- Do not reveal whether email exists
- Invalidate previous OTPs when new OTP created

Tables required:

password_reset_otps
password_reset_tokens

---

## AUTHORIZATION SYSTEM(Later)

Implement RBAC (Role Based Access Control).

Entities:

- users
- roles
- permissions
- role_user
- permission_role

Support:

- Multiple roles per user
- Multiple permissions per role
- Direct permission checks
- Middleware-based authorization

Example permissions:

- ticket.create
- ticket.reply
- ticket.forward
- ticket.reopen
- user.manage

Provide helper methods like:

$user->hasRole('admin')
$user->can('ticket.reply')

---

## MICROSERVICE REQUIREMENTS

This auth service will be consumed by external microservices.

Implement:

1. Public key endpoint for JWT verification
2. Internal service authentication
3. Service-to-service token support
4. JWT validation middleware

Ticketing Service must be able to:

- Validate JWT locally using public key
- Read user roles/permissions from JWT
- Never access auth database directly

Do NOT tightly couple services.

---

## DATABASE DESIGN

Use PostgreSQL.

Required tables:

- users
- roles
- permissions
- role_user
- permission_role
- refresh_tokens
- password_reset_otps
- password_reset_tokens
- audit_logs
- personal_access_tokens

Use UUIDs where appropriate.

Add proper indexes.

---

## SECURITY REQUIREMENTS

Implement:

- RS256 JWT signing
- HTTPS-ready config
- Rate limiting
- Secure password hashing
- Token revocation
- Queue-based email sending
- Input validation
- API throttling
- SQL injection protection
- XSS-safe API responses
- CSRF considerations for APIs
- Audit logging

Passwords must use Laravel Hash.

---

## AUDIT LOGGING

Create audit logs for:

- login
- logout
- password reset
- OTP verification
- failed login attempts
- permission changes
- token revocation

Store:

- user_id
- action
- ip_address
- user_agent
- metadata

---

## API REQUIREMENTS

Generate:

- RESTful APIs
- API Resources
- Form Requests
- Exception handling
- API versioning (/api/v1)
- Standard JSON response format

Example response:

{
"success": true,
"message": "Login successful",
"data": {}
}

---

## PROJECT STRUCTURE

Use clean architecture:

Separate business logic from controllers.

Controllers should remain thin.

---

---

## DELIVERABLES

Generate:

1. Full folder structure
2. Database migrations

3. Services

4. Controllers
5. Middleware
6. Events & listeners

7. API routes
8. Request validation classes

9. Environment configuration
10. Seeder data
11. Unit tests
12. Feature tests
13. Postman collection

- Two-factor authentication (TOTP)
- Device/session tracking
- Login history
- Email verification
- Account locking after failed attempts
- Multi-tenant readiness
- API Gateway readiness

---

## IMPORTANT ARCHITECTURAL RULES

- Never share database with other services
- Ticketing service only trusts JWT
- No password logic outside auth service
- Use events where useful
- Prefer async processing
- Keep system horizontally scalable
- Use cache where beneficial
- Make code production-ready

Generate complete implementation step-by-step with production-grade code quality.

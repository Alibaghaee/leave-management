Leave Management

An enterprise backend system for leave management built with Laravel.
It includes CRUD operations for leave requests, an approval pipeline (HR → Manager → CEO), audit logging, and a scalable daily reporting/aggregation mechanism.

⸻

Table of Contents

* Quick Start
* Architecture
* Authentication
* General Routes / Endpoints
* Approval Pipeline Rules
* Aggregation / Reporting
* Request / Response Examples
* Execution, Queues & Scheduling
* Testing & Linting
* Error Codes
* OpenAPI / Swagger
* Developer Notes / Contributing
* License

⸻

Quick Start

Requirements: PHP 8.x, Composer, Database (MySQL/Postgres/SQLite), Docker (optional)

git clone https://github.com/Alibaghaee/leave-management.git
cd leave-management
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve

Get a Sanctum Token

php artisan tinker
$user = \App\Models\User::first();
$token = $user->createToken('api')->plainTextToken;

Send the token with your requests:

Authorization: Bearer <TOKEN>

⸻

Architecture

* Layered architecture: Controllers → DTOs → Services → Repositories → Models
* API responses are based on JsonResource with the { data, meta, links } format
* Approval stages are configured using order, role, and min_days
* Logs are used for:
    * auditing
    * idempotency
    * multi-stage approval/rejection tracking

Models:

* Employee
* LeaveRequest
* Stage
* LeaveLog

Reporting is handled through summary tables:

* daily
* monthly
* yearly

⸻

Authentication

The entire API under /api/v1 is protected by the following middleware:

auth:sanctum

Clients must provide a Bearer Token.

⸻

General Routes / Endpoints

GET    /api/v1/employees
POST   /api/v1/employees
GET    /api/v1/employees/{id}
PUT    /api/v1/employees/{id}
DELETE /api/v1/employees/{id}
GET    /api/v1/leave-requests
POST   /api/v1/leave-requests
GET    /api/v1/leave-requests/{id}
PUT    /api/v1/leave-requests/{id}
DELETE /api/v1/leave-requests/{id}
POST   /api/v1/leave-requests/{id}/approve
POST   /api/v1/leave-requests/{id}/reject
GET    /api/v1/stages
POST   /api/v1/stages
GET    /api/v1/stages/{id}
PUT    /api/v1/stages/{id}
DELETE /api/v1/stages/{id}
GET    /api/v1/leave-logs
POST   /api/v1/leave-logs
GET    /api/v1/employee-leave-summaries/daily
POST   /api/v1/employee-leave-summaries/daily/aggregate

⸻

Approval Pipeline Rules

Roles and Restrictions

* HR → can always approve
* Manager → can only approve requests for employees where employee.manager_id == manager.id
* CEO → can only approve when days_count >= min_days configured for the CEO stage

Error Types According to Tests

* If the user is completely unauthorized → 403 Forbidden
* If a manager attempts to approve a request belonging to an employee who is not their subordinate → 422 Unprocessable Entity

Stages

Each stage contains:

* name
* role
* order
* min_days
* next_stage_id

Idempotency

idempotency_key is used to prevent duplicate approve/reject operations.

If the same key has already been consumed, the operation returns without making any changes.

⸻

Aggregation / Reporting

* Aggregation tables → daily/monthly/yearly
* Manual execution:

php artisan leave:aggregate-employee-leave-daily --date=2025-11-16

API:

POST /api/v1/employee-leave-summaries/daily/aggregate

Query:

GET /api/v1/employee-leave-summaries/daily?employee_id=1&date=2025-12-01

Aggregation is designed to be safe, idempotent, and queue-based.

⸻

Request / Response Examples

Create a Daily Leave Request

POST /api/v1/leave-requests
{
  "leave_type": "annual",
  "start_date": "2025-12-01",
  "end_date": "2025-12-03",
  "reason": "Vacation"
}

Response:

{
  "data": {
    "id": 12,
    "employee_id": 3,
    "employee_name": "Ali",
    "leave_type": "annual",
    "days_count": 3,
    "status": "pending_hr",
    "current_stage_id": 1
  }
}

Create an Hourly Leave Request (Half Day)

{
  "leave_type": "hourly",
  "start_date": "2025-12-10",
  "end_date": "2025-12-10",
  "start_time": "09:00",
  "end_time": "13:00",
  "reason": "Doctor appointment"
}

days_count = (hours / 8) → the example above = 0.5

Multi-Stage Approval

POST /api/v1/leave-requests/12/approve
{
  "comment": "OK",
  "idempotency_key": "hr-12-approve"
}

⸻

Execution, Queues & Scheduling

Queue Worker

php artisan queue:work

Cron for Scheduler

* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1

Recommendation: Use two separate Docker services for the worker and scheduler.

⸻

Testing & Linting

php artisan test
php artisan test --parallel

If available:

composer cs-check
composer phpstan

⸻

Error Codes

Status	Description
200	Success
201	Created
204	Deleted
401	Authentication failed
403	Insufficient permissions
422	Validation / business rule error
500	Server error

Example 422 error:

{
  "message": "The given data was invalid.",
  "errors": {
    "start_date": ["required"]
  }
}

⸻

OpenAPI / Swagger

Main file:

openapi.yaml

Compatible with Swagger UI / Redoc.

Includes:

* Bearer token security scheme
* Complete schema definitions
* Routes for all resources

⸻

Developer Notes / Contributing

* Follow PSR-12
* Create Pull Requests with accompanying tests
* Do not change approval pipeline behavior without updating tests
* Run the complete test suite before merging:

php artisan test

⸻

License
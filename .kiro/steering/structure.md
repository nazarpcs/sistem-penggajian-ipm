# Project Structure

## Architecture Pattern
Service Layer + Domain Layer on top of Laravel MVC.

- **Controllers** — Handle HTTP, delegate to Services. Thin, no business logic.
- **Services** (`app/Services/`) — Business logic, orchestration, transactions, audit logging.
- **Domain** (`app/Domain/`) — Pure PHP classes with no Laravel dependencies. Core business rules.
- **Models** (`app/Models/`) — Eloquent models with relationships and casts.
- **Requests** (`app/Http/Requests/`) — Form validation via FormRequest classes.

## Folder Layout
```
app/
├── Domain/              # Pure domain logic (no framework deps)
│   ├── Document/        # PDF generators (slip gaji, invoice)
│   ├── Payroll/         # Salary calculator (KalkulatorGaji)
│   └── Validation/      # Input validators (AbsensiValidator)
├── Exports/             # Maatwebsite Excel export classes
├── Http/
│   ├── Controllers/
│   │   ├── Admin/       # Admin-only controllers
│   │   ├── Auth/        # Login, password reset
│   │   ├── Karyawan/    # Employee self-service
│   │   └── Owner/       # Owner dashboard & invoice approval
│   ├── Middleware/       # CheckRole, SanitizeInput, ThrottleLogin
│   └── Requests/        # FormRequest validation classes
├── Jobs/                # Queued jobs (import, bulk PDF)
├── Models/              # Eloquent models
├── Notifications/       # Email notifications
├── Observers/           # Model observers (auto-create user on karyawan create)
├── Policies/            # Authorization policies
├── Services/            # Business logic layer
└── Traits/              # Shared traits (HasAuditLog)

resources/views/
├── admin/               # Admin pages
├── auth/                # Login, password reset
├── components/          # Reusable Blade components
├── karyawan/            # Employee pages
├── layouts/             # App and guest layouts
├── owner/               # Owner pages
└── pdf/                 # PDF templates
```

## Conventions
- **Naming**: Indonesian (Bahasa) for models, tables, routes, views, and business terms
- **Strict types**: All PHP files use `declare(strict_types=1)`
- **Interfaces**: Domain classes implement interfaces, bound in AppServiceProvider
- **Audit logging**: Use `HasAuditLog` trait in Services for critical operations
- **Route grouping**: Prefixed by role (`/admin`, `/owner`, `/karyawan`) with middleware
- **Controllers**: Grouped by role under `Controllers/{Role}/`
- **Database tables**: Snake_case Indonesian names (e.g., `karyawan`, `slip_gaji`, `pt_klien`)

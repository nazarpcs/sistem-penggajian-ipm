# Product: Sistem Penggajian PT Indah Permata Mandiri (IPM)

A payroll management system for PT Indah Permata Mandiri, an outsourcing company that manages employees placed at various client companies (PT Klien).

## Core Capabilities
- Employee management (CRUD) with auto-generated login accounts
- Attendance tracking (manual entry + Excel import)
- Salary calculation: Gaji_Bersih = Gaji_Pokok + Tunjangan + Lembur - Potongan
- Payslip (Slip Gaji) generation as PDF
- Invoice generation for client companies
- Reports and analytics (attendance, payroll, invoices) with PDF/Excel export

## User Roles
- **Admin**: Full system access — manages employees, attendance, payroll, invoices, audit logs
- **Pemilik PT (Owner)**: Views and approves/rejects invoices, views reports
- **Karyawan (Employee)**: Views own profile, attendance history, and payslips

## Business Context
- Each employee belongs to one PT Klien (client company)
- Salary configuration (allowances, overtime rates, deductions) is per PT Klien
- Invoices are generated per PT Klien per payroll period
- All critical operations are recorded in an audit log
- Language: Indonesian (Bahasa Indonesia) for all UI, models, and business logic naming

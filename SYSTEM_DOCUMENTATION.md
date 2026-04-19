# Gondal ERP — System Documentation

> **Stack:** Laravel 11.22 · PHP 8.4 · MySQL 8 · Nwidart Laravel Modules · Spatie Permission

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Architecture](#2-architecture)
3. [Modules](#3-modules)
   - [Accounting](#31-accounting-module)
   - [HRM](#32-hrm-system)
   - [Milk Collection (POS)](#33-milk-collection-pos)
   - [Center Operations](#34-center-operations)
   - [Logistics](#35-logistics)
   - [OSS (Outreach & Sales Service)](#36-oss-outreach--sales-service)
   - [Extension](#37-extension)
   - [Cooperatives](#38-cooperatives)
   - [Reports](#39-reports)
   - [Sponsor Portal](#310-sponsor-portal)
   - [Landing Page](#311-landing-page)
4. [Core Financial System](#4-core-financial-system)
5. [Double-Entry GL & AccountingService](#5-double-entry-gl--accountingservice)
6. [Roles & Permissions](#6-roles--permissions)
7. [Database Schema](#7-database-schema)
8. [Key Workflows](#8-key-workflows)
9. [Multi-Tenancy Pattern](#9-multi-tenancy-pattern)
10. [Payment Gateway Integrations](#10-payment-gateway-integrations)
11. [Configuration & Settings](#11-configuration--settings)
12. [Setup & Deployment](#12-setup--deployment)
13. [Developer Reference](#13-developer-reference)

---

## 1. System Overview

Gondal ERP is a comprehensive, multi-tenant Enterprise Resource Planning platform built for agribusiness operations — specifically dairy/milk collection cooperatives and agricultural input supply chains. It combines:

- **Finance & Accounting** — AR/AP, GL, budgets, bank reconciliation, expense claims
- **Human Resource Management** — Employees, payroll, leaves, attendance, appraisals
- **Milk Collection** — Daily milk intake tracking per MCC (Milk Collection Centre)
- **Logistics** — Transport trips and rider management
- **Cooperatives** — Farmer cooperative registration and membership
- **Extension Services** — Field agent visits, training events, follow-up tasks
- **OSS** — Agricultural input product sales and inventory
- **Center Operations** — MCC operational cost approval workflow
- **CRM** — Leads, deals, pipeline, contracts
- **Project Management** — Projects, tasks, milestones, timesheets
- **Reports** — Executive dashboards, financial reports, operational metrics

The application is designed for organizations that manage geographically distributed operations (Milk Collection Centres — Mayo, Yola, Jabbi Lamba, Mubi, Sunkani) with centralized finance control and role-separated field operations.

---

## 2. Architecture

### Technology Stack

| Layer | Technology |
|---|---|
| Backend Framework | Laravel 11.22.0 |
| Language | PHP 8.4.6 |
| Database | MySQL 8 |
| Module System | Nwidart Laravel Modules |
| Permissions | Spatie Laravel Permission |
| Frontend Assets | Vite + Bootstrap 5 + ApexCharts + Feather Icons + Tabler Icons |
| File Storage | Local / AWS S3 |
| Excel | Maatwebsite Excel |
| Auth | Laravel Sanctum |

### Directory Structure

```
sebore-main/
├── app/
│   ├── Http/Controllers/       # Core application controllers
│   ├── Models/                 # Eloquent models (Invoice, Employee, etc.)
│   ├── Services/
│   │   └── AccountingService.php  # Centralized GL posting service
│   └── Providers/
├── Modules/                    # Nwidart Laravel Modules
│   ├── Accounting/
│   ├── CenterOperations/
│   ├── Cooperatives/
│   ├── Extension/
│   ├── LandingPage/
│   ├── Logistics/
│   ├── OSS/
│   ├── Reports/
│   └── SponsorPortal/
├── resources/views/            # Core Blade views
├── routes/web.php              # Core routes
├── modules_statuses.json       # Module enable/disable registry
└── bootstrap/cache/
    └── modules.php             # Runtime module provider cache (gitignored)
```

### Module Anatomy

Every module follows the same structure:

```
Modules/{Name}/
├── module.json                          # Module manifest (name, alias, providers)
├── Providers/
│   ├── {Name}ServiceProvider.php        # Registers views, migrations, sub-providers
│   ├── RouteServiceProvider.php         # Loads Routes/web.php
│   └── EventServiceProvider.php        # Event listeners
├── Http/Controllers/                    # Module controllers
├── Models/                              # Module-specific Eloquent models
├── Database/Migrations/                 # Module migrations
├── Resources/views/                     # Blade views (namespaced as `name::`)
└── Routes/web.php                       # Module routes
```

> **Important:** `bootstrap/cache/modules.php` is gitignored. After any `git pull`, run `php artisan optimize:clear` to regenerate it, otherwise modules will not load.

---

## 3. Modules

### 3.1 Accounting Module

**Path:** `Modules/Accounting/`  
**Route prefix:** `/accounting`  
**Route namespace:** `accounting.`

The Accounting module is the finance team's dedicated workspace. It provides a unified view of all financial activity across the ERP system.

#### Controllers

| Controller | Responsibility |
|---|---|
| `AccountingDashboardController` | Finance KPI dashboard |
| `BudgetController` | Budget CRUD and activation workflow |
| `ExpenseClaimController` | Employee expense reimbursement lifecycle |
| `ReconciliationController` | Bank statement reconciliation |

#### Finance Dashboard (`GET /accounting`)

Displays:
- **KPI Cards** — Total cash position (sum of bank account opening balances), total AR outstanding, total AP outstanding, pending expense claim count
- **Revenue vs Expense Chart** — ApexCharts bar chart for last 6 months (from `revenues` and `payments` tables)
- **AR Aging** — Outstanding invoices bucketed: Current / 1–30 days / 31–60 / 61–90 / 90+ days overdue
- **AP Aging** — Same aging buckets for outstanding bills
- **Bank Accounts** — All bank accounts with balances
- **Active Budget Preview** — Budget line items with % of budget consumed
- **Quick Actions** — One-click links to create invoice, bill, revenue, payment, journal entry, expense claim, reconciliation
- **Recent Revenue** — Last 5 revenue transactions
- **Recent Payments** — Last 5 payment transactions

#### Budget Management (`/accounting/budget`)

Budgets allow the finance team to set spending targets per Chart of Account across a fiscal year.

- Create a budget with name, fiscal year, start/end date, status (`draft` / `active` / `closed`)
- Add budget lines: one line per Chart of Account with monthly allocations (Jan–Dec columns)
- **Activate** a budget — sets it to `active`, demotes any other active budget to `draft`
- **Budget vs Actual** — each line computes actual spend from `transaction_lines` where `account_id` = line's `chart_account_id` and `date` is within budget period
- **Variance** = budgeted amount − actual spend

> Tables: `acct_budgets`, `acct_budget_lines` (renamed from `budgets`/`budget_lines` to avoid conflict with the existing HRM budget table)

#### Bank Reconciliation (`/accounting/reconciliation`)

Matches bank statement line items against GL transaction lines to ensure books match the bank.

- Create a reconciliation for a bank account with statement date, opening and closing balances
- Enter statement items (date, description, debit/credit, amount, reference)
- Match each statement item to a GL `TransactionLine` for that bank account's chart account
- Unmatch items if incorrectly matched
- **Finalize** — blocked if any statement items remain unmatched; sets status to `reconciled`

> Tables: `reconciliations`, `reconciliation_items`

#### Expense Claims (`/accounting/expense-claims`)

Manages employee out-of-pocket expense reimbursements.

**Status Lifecycle:**

```
draft → submitted → approved → paid
                 ↘ rejected
```

- Employee creates a claim with line items (date, description, chart account, amount, receipt upload)
- Submits for approval
- Finance manager approves or rejects (with rejection reason)
- On approval, finance marks as paid → **GL entry posted** via `AccountingService::post()` for each line item
  - Debit: line item's `chart_account_id`
  - Credit: default cash account (from settings)

> Tables: `expense_claims`, `expense_claim_items`

---

### 3.2 HRM System

**Path:** `app/Http/Controllers/` (no separate module — core app)

#### Employee Management

Full employee lifecycle:
- Employee master: personal details, contact, bank account, designation, department, branch
- Documents: upload and manage employee documents
- Employment dates, salary type (monthly/hourly)
- Biometric integration via `biometric_emp_id`

#### Payroll

1. **Set Salary** — Configure base salary per employee with effective date
2. **Allowances** — Add allowance types and amounts per employee
3. **Deductions** — Configure deduction options (e.g., pension, tax)
4. **Loans** — Salary advance loan tracking with installment deductions
5. **Overtime** — Record overtime hours
6. **Generate Payslips** — Compute:
   ```
   Gross = Base Salary + Allowances + Commissions + Overtime
   Net   = Gross − Deductions − Loan Installments
   ```
7. **Approve & Pay** — On `paysalary()`:
   - Creates `EmployeePayslip` record with `net_payble`
   - Looks up the bank account used
   - Posts GL via `AccountingService::postPayroll()`:
     - **Debit:** Salary Expense Account
     - **Credit:** Bank Account

#### Leave Management

- Leave types with annual allocation
- Leave application → Manager approval
- Leave balance tracking

#### Attendance

- Manual check-in/check-out recording
- Bulk attendance import
- Biometric device integration support
- Monthly attendance reports

#### Other HR Modules

Awards, Transfers, Resignations, Training, Travel, Promotions, Complaints, Warnings, Terminations, Announcements, Appraisals, Goal Tracking, Performance Reviews, Holiday Calendar, Company Policies, Job Postings, Interviews.

---

### 3.3 Milk Collection (POS)

**Path:** `app/Http/Controllers/DashboardController@pos_dashboard_index`

The Milk Collection dashboard tracks daily milk intake across all MCCs.

- **Daily Collection** — Litres collected per MCC per day
- **Weekly/Monthly Aggregation** — Trend charts and totals
- **Farmer Payments** — Bulk payment processing via `PaySlipFarmerController`
  - Payment status workflow: initialise → authorise → approve → generate → resend token
- **MC Officer Payments** — Separate payment workflow for milk collection officers
- **Reports:**
  - Collection Centre report
  - Daily Collection report
  - Monthly Collection report

**MCCs Tracked:** Mayo · Yola · Jabbi Lamba · Mubi · Sunkani

---

### 3.4 Center Operations

**Path:** `Modules/CenterOperations/`

Tracks and approves operational costs incurred at each Milk Collection Centre.

#### Cost Categories

Labour · Cleaning · Maintenance · Utilities · Rent · Miscellaneous

#### Status Workflow

```
draft → submitted → approved → paid
               ↘ rejected
```

#### GL Integration

When a cost is marked **paid**, `AccountingService::postCenterCost()` fires:
- **Debit:** Operating Expense Account (`default_ops_expense_account` setting)
- **Credit:** Cash Account (`default_cash_account` setting)

#### Audit Logging

All status transitions are recorded in `FinancialAuditLog` with user, timestamp, and previous/new status.

---

### 3.5 Logistics

**Path:** `Modules/Logistics/`

Manages milk transport trips from MCCs to processing facilities.

#### Trip Lifecycle

```
pending → in_transit → completed
        ↘ cancelled
```

#### Key Data Points Per Trip

- Rider (driver) assigned
- Source MCC
- Date
- Litres transported
- Cost per litre

#### Reporting

Monthly metrics per MCC: total litres transported, number of trips, average cost per litre, total transport cost.

---

### 3.6 OSS (Outreach & Sales Service)

**Path:** `Modules/OSS/`

Manages the agricultural input sales programme — selling seeds, fertilizers, tools, and chemicals to farmers through agents.

#### Components

| Component | Purpose |
|---|---|
| Products | Catalog with auto-generated codes, units, price, reorder level |
| Inventory | Stock levels by location |
| Sales | Sales transactions via agents |
| Agent Distribution | Track inputs distributed to field agents |

#### GL Integration

On sale creation, `AccountingService::postOssSale()` fires:
- **Debit:** Cash Account (`default_cash_account`)
- **Credit:** OSS Revenue Account (`default_oss_revenue_account`)

#### Reorder Alerts

Products with `quantity < reorder_level` are flagged for restocking.

---

### 3.7 Extension

**Path:** `Modules/Extension/`

Tracks agricultural extension advisory activities performed by field agents.

#### Components

**Extension Agents** — Agent profiles with assigned territory, targets, and activity stats.

**Field Visits** — Each visit records:
- Agent, date, MCC/center
- Farmers visited (linked to farmer/vender records)
- Topics covered: Productivity · Health · Nutrition · Pest Control · Post-Harvest · Market Linkage
- Photos uploaded
- Follow-up tasks created

**Training Events** — Group training sessions with:
- Event details (date, location, topics)
- Attendee list
- Training materials uploaded

---

### 3.8 Cooperatives

**Path:** `Modules/Cooperatives/`

Manages farmer cooperative societies and their membership.

- Cooperative master: name, registration number, leader
- Farmer membership: link farmers to cooperatives
- Export cooperatives and farmer lists to Excel
- Bulk import via Excel upload
- API endpoints for mobile/external system integration

---

### 3.9 Reports

**Path:** `Modules/Reports/`

Executive and operational dashboards aggregating data from all modules.

#### Executive Dashboard

- Active farmer count
- Daily/monthly litres by MCC
- Financial inclusion percentage
- Centre operational status
- Weekly trend data from `WeeklyReport` model

#### Financial Reports (core app)

| Report | Controller Method |
|---|---|
| Profit & Loss | `ReportController@profitLoss` |
| Balance Sheet | `ReportController@balanceSheet` |
| Trial Balance | `ReportController@trialBalance` |
| Receivables Aging | `ReportController@receivableReport` |
| Payables Aging | `ReportController@payableReport` |
| Invoice Summary | `ReportController@invoiceSummary` |
| Bill Summary | `ReportController@billSummary` |
| Income vs Expense | `ReportController@incomeVsExpense` |
| Account Statement | `ReportController@accountStatement` |
| Cash Flow | `ReportController@cashFlow` |
| Stock Report | `ReportController@stockReport` |
| Tax Report | `ReportController@taxReport` |

---

### 3.10 Sponsor Portal

**Path:** `Modules/SponsorPortal/`

A separate portal for external sponsors/donors to monitor projects they fund.

- Admin backend to manage sponsors and assign them to projects
- Sponsor-facing login and dashboard
- Project progress reporting per sponsor
- Project–Center and Project–Agent association views

---

### 3.11 Landing Page

**Path:** `Modules/LandingPage/`

Public-facing marketing website with a CMS for managing:
- Hero section, features, pricing plans
- Testimonials, FAQs, screenshots
- Custom pages, Join Us forms

---

## 4. Core Financial System

### Invoice (Accounts Receivable)

**Model:** `App\Models\Invoice`  
**Controller:** `App\Http\Controllers\InvoiceController`

```
Statuses: 0=Draft · 1=Sent · 2=Unpaid · 3=Partially Paid · 4=Paid
```

- Line items with product, quantity, price, discount, tax
- Automatic tax calculation
- Partial payment tracking via `invoice_payments`
- `getDue()` = total (with tax) − sum of payments
- Send invoice by email
- Public invoice link for customer view
- Convert to credit note

### Bill (Accounts Payable)

**Model:** `App\Models\Bill`  
**Controller:** `App\Http\Controllers\BillController`

```
Statuses: 0=Draft · 1=Sent · 2=Unpaid · 3=Partially Paid · 4=Paid
```

- Linked to vendor (also used for farmer supplier bills)
- Line items with expense account distribution
- Partial payment via `bill_payments`
- Duplicate bill function
- Send bill by email

### Revenue

**Model:** `App\Models\Revenue`  
**Controller:** `App\Http\Controllers\RevenueController`

Direct income recording (not tied to an invoice):
- Amount, date, customer, bank account, category
- Payment method: cash, cheque, bank transfer, other
- Reference number tracking

### Payment

**Model:** `App\Models\Payment`  
**Controller:** `App\Http\Controllers\PaymentController`

Direct expense payment (not tied to a bill):
- Amount, date, vendor, bank account, chart account, category
- Reference number

### Journal Entry

**Model:** `App\Models\JournalEntry` + `App\Models\JournalItem`  
**Controller:** `App\Http\Controllers\JournalEntryController`

Manual double-entry GL posting:
- Header: reference number, date, description
- Lines: account, debit, credit
- Validates debit total = credit total

### Chart of Accounts

**Model:** `App\Models\ChartOfAccount`  
**Controller:** `App\Http\Controllers\ChartOfAccountController`

Hierarchical account structure:
```
Type (Asset / Liability / Equity / Revenue / Expense)
  └── Sub-Type
        └── Account (leaf node)
```

`balance()` method computes net balance from `journal_items` (credit − debit or debit − credit depending on normal balance type).

---

## 5. Double-Entry GL & AccountingService

### TransactionLines Table

The central GL ledger. Every financial event produces two rows — one debit, one credit.

```sql
CREATE TABLE transaction_lines (
    id              BIGINT PRIMARY KEY,
    account_id      BIGINT,          -- chart_of_accounts.id
    reference       VARCHAR(255),    -- 'Invoice', 'Bill', 'Payroll', 'Center Cost', 'OSS Sale', etc.
    reference_id    BIGINT,          -- source record ID
    reference_sub_id BIGINT,         -- sub-item ID (line item) or 0
    date            DATE,
    debit           DECIMAL(15,2),
    credit          DECIMAL(15,2),
    created_by      BIGINT
);
```

### AccountingService

**Path:** `app/Services/AccountingService.php`

A static service providing a clean API for posting GL entries from anywhere in the application.

#### Core Method

```php
AccountingService::post(
    int    $debitAccountId,   // Chart of Account to debit
    int    $creditAccountId,  // Chart of Account to credit
    float  $amount,
    string $reference,        // e.g. 'Payroll'
    int    $referenceId,      // e.g. payslip ID
    int    $referenceSubId,   // e.g. 0 or line item ID
    string $date              // 'Y-m-d'
): void
```

Uses upsert (updateOrCreate) to prevent duplicate GL entries on retry.

#### Specialized Methods

| Method | Debit | Credit | Trigger |
|---|---|---|---|
| `postPayroll($amount, $payslipId, $bankChartId, $date)` | Salary Expense Account | Bank Chart Account | `PaySlipController::paysalary()` |
| `postCenterCost($amount, $costId, $date)` | Ops Expense Account | Cash Account | `CenterOperationsController::markPaid()` |
| `postOssSale($amount, $saleId, $date)` | Cash Account | OSS Revenue Account | `OssSalesController::store()` |
| `reverse($reference, $referenceId)` | — | — | Deletes matching GL rows |

#### Default Account Settings

Configured in **Settings → Company → Accounting Settings**:

| Setting Key | Purpose |
|---|---|
| `default_salary_expense_account` | Salary/wage expense GL account |
| `default_ops_expense_account` | MCC operational expense GL account |
| `default_oss_revenue_account` | Agricultural sales revenue GL account |
| `default_cash_account` | Default cash/bank GL account |

---

## 6. Roles & Permissions

### Permission System

Uses **Spatie Laravel Permission**. Permissions are stored in the `permissions` table and assigned to roles via `role_has_permissions`, or directly to users via `model_has_permissions`.

### Built-in Roles

| Role | Typical User |
|---|---|
| `super admin` | Platform superadmin |
| `company` | Workspace owner (sees all data) |
| `system_admin` | IT/System administrator |
| `Admin` / `admin` | General admin |
| `HR & Admin Manager` | HR team lead |
| `finance_officer` | Finance team member |
| `accountant` / `Accountant II` | Accounting staff |
| `employee` | Regular staff (self-service HR) |
| `center_manager` | MCC centre manager |
| `field_delivery_lead` | Logistics field lead |
| `cooperative_leader` | Cooperative chairman/secretary |
| `extension_agent` | Field extension agent |
| `executive_director` | Executive oversight |
| `board_member` | Board-level read access |
| `client` | External customer (invoice portal) |

### Permission Domains

| Domain | Key Permissions |
|---|---|
| **Accounting** | `manage accounting`, `manage invoice`, `manage bill`, `manage revenue`, `manage payment`, `manage journal entry`, `manage chart of account`, `manage bank account` |
| **Budgets** | `manage budget`, `create budget`, `edit budget`, `delete budget` |
| **Reconciliation** | `manage reconciliation`, `create reconciliation`, `reconcile bank` |
| **Expense Claims** | `manage expense claim`, `create expense claim`, `approve expense claim`, `pay expense claim` |
| **HRM** | `manage employee`, `manage pay slip`, `manage leave`, `manage attendance`, `show hrm dashboard` |
| **Reports** | `manage report`, `income report`, `expense report`, `loss & profit report`, `trial balance report`, `tax report`, `invoice report`, `bill report` |
| **Center Operations** | `manage center operations`, `create center cost`, `approve accounts requisition` |
| **OSS** | `manage oss products`, `manage oss sales`, `manage oss inventory` |
| **Extension** | `manage extension agents` |
| **Logistics** | `manage logistics`, `create logistics trip` |
| **Cooperatives** | `manage cooperative` |
| **System** | `manage user`, `manage role`, `manage company settings`, `manage system settings` |

### Checking Permissions

In controllers:
```php
if (!Auth::user()->can('manage accounting')) {
    return redirect()->back()->with('error', __('Permission denied.'));
}
```

In Blade views:
```blade
@can('create invoice')
    <a href="{{ route('invoice.create', 0) }}">New Invoice</a>
@endcan
```

---

## 7. Database Schema

### Core Financial Tables

#### `invoices`
```
id, invoice_id (formatted), customer_id, issue_date, due_date,
ref_number, status (0-4), category_id, created_by
```

#### `invoice_products`
```
id, invoice_id, product_id, quantity, price, discount, tax_id
```

#### `invoice_payments`
```
id, invoice_id, date, amount, payment_method, reference
```

#### `bills`
```
id, bill_id (formatted), vender_id, currency, bill_date, due_date,
order_number, status (0-4), category_id, created_by
```

#### `revenues`
```
id, date, amount, account_id (bank_account), customer_id,
category_id, recurring, payment_method, reference, created_by
```

#### `payments`
```
id, date, amount, account_id (bank_account), chart_account_id,
vender_id, description, category_id, payment_method, reference, created_by
```

#### `transaction_lines` (GL Ledger)
```
id, account_id, reference, reference_id, reference_sub_id,
date, debit, credit, created_by
```

#### `journal_entries` + `journal_items` (Manual GL)
```
journal_entries: id, reference, description, created_by
journal_items:   id, journal_id, account, debit, credit, description
```

#### `chart_of_accounts`
```
id, name, code, type, sub_type, parent, is_enabled, description, created_by
```

#### `bank_accounts`
```
id, bank_name, account_number, opening_balance, chart_account_id, created_by
```

### HRM Tables

#### `employees`
```
id, user_id, name, dob, gender, phone, address, email,
employee_id, branch_id, department_id, designation_id, company_doj,
account_holder_name, account_number, bank_name, bank_identifier_code,
salary_type, biometric_emp_id, created_by
```

#### `payslips`
```
id, employee_id, month, year, gross_amount, net_amount, status, created_by
```

#### `set_salaries`
```
id, employee_id, basic_salary, effective_from, created_by
```

#### `allowances` / `deductions` / `loans`
```
id, employee_id, type_id, amount, created_by
```

### Module Tables

#### Center Operations: `center_costs`
```
id, cost_entry_id, mcc, category, amount, description, receipt_path,
status, submitted_by, approved_by, rejected_by, paid_by,
submitted_at, approved_at, rejected_at, paid_at,
rejection_reason, created_by
```

#### Logistics: `logistics_trips`
```
id, rider_id, trip_date, mcc_source, destination, litres_transported,
cost_per_litre, status, created_by
```

#### OSS: `oss_products` / `oss_inventory`
```
oss_products: id, product_code, name, category, unit, price, reorder_level, created_by
oss_inventory: id, product_id, location, quantity, created_by
```

#### Accounting: `acct_budgets` / `acct_budget_lines`
```
acct_budgets: id, budget_id, name, fiscal_year, start_date, end_date, status, description, created_by
acct_budget_lines: id, budget_id, chart_account_id, description, jan, feb, mar, apr, may, jun, jul, aug, sep, oct, nov, dec
```

#### Accounting: `reconciliations` / `reconciliation_items`
```
reconciliations: id, reconciliation_id, bank_account_id, statement_date, opening_balance, closing_balance, status, reconciled_at, reconciled_by, created_by
reconciliation_items: id, reconciliation_id, date, description, type (debit/credit), amount, reference, transaction_line_id, is_matched
```

#### Accounting: `expense_claims` / `expense_claim_items`
```
expense_claims: id, claim_id, employee_id, claim_date, title, description, total_amount, status, approved_by, approved_at, paid_by, paid_at, rejection_reason, created_by
expense_claim_items: id, expense_claim_id, date, description, chart_account_id, amount, receipt_path
```

#### Cooperatives: `cooperatives`
```
id, name, registration_number, leader_id, created_by
```

#### Extension: `field_visits` / `training_events`
```
field_visits: id, agent_id, visit_date, center, created_by
training_events: id, name, date, location, created_by
```

### System Tables

| Table | Purpose |
|---|---|
| `users` | All system users (multi-type) |
| `customers` | Invoice customers |
| `venders` | Bill vendors / farmers |
| `products` | Product/service catalog |
| `product_service_categories` | Income/expense categories |
| `taxes` | Tax rates |
| `settings` | Key-value system settings |
| `roles` / `permissions` | Spatie permission tables |
| `model_has_roles` | User → Role assignments |
| `model_has_permissions` | User → Direct permission grants |

---

## 8. Key Workflows

### 8.1 Invoice → Payment (AR)

```
1. Create Invoice (status: Draft)
   └── Add customer, due date, line items (product, qty, price, tax)

2. Send Invoice (status: Sent)
   └── Email sent to customer with public payment link

3. Record Payment (status: Unpaid → Partially Paid → Paid)
   └── Each payment logged in invoice_payments
   └── getDue() = total - sum(payments)

4. GL Impact (via existing invoice payment flow)
   └── Debit: Bank Account
   └── Credit: Revenue Account
```

### 8.2 Bill → Payment (AP)

```
1. Create Bill (status: Draft)
   └── Select vendor, due date, expense line items with COA

2. Record Payment (status: Unpaid → Partially Paid → Paid)
   └── Each payment in bill_payments
   └── getDue() = total - sum(payments)

3. GL Impact
   └── Debit: Expense Account (from bill line items)
   └── Credit: Bank Account
```

### 8.3 Payroll Cycle

```
1. Set Salary → Configure base salary per employee

2. Configure Allowances, Deductions, Loans, Overtime

3. Generate Payslips
   └── Net = Base + Allowances + Commission + Overtime
           - Deductions - Loan Installments

4. Approve Payslips

5. Pay Salary (paysalary() in PaySlipController)
   └── Marks EmployeePayslip as paid
   └── AccountingService::postPayroll() fires
       ├── Debit:  Salary Expense Account
       └── Credit: Bank Account (chart_account_id)
```

### 8.4 Center Cost Approval

```
1. MCC Manager creates cost (draft)
   └── Fields: MCC, category, amount, description, receipt

2. Submit → Status: submitted

3. Finance reviews → Approve or Reject
   └── Rejection requires reason

4. Mark Paid → Status: paid
   └── AccountingService::postCenterCost() fires
       ├── Debit:  Ops Expense Account (default_ops_expense_account)
       └── Credit: Cash Account (default_cash_account)
   └── FinancialAuditLog record created
```

### 8.5 Expense Claim

```
1. Employee creates claim (draft)
   └── Add line items: date, description, chart account, amount, receipt

2. Submit → Status: submitted

3. Finance Manager approves or rejects
   └── Rejection stores rejection_reason

4. Pay → Status: paid
   └── For each line item, AccountingService::post() fires
       ├── Debit:  Line item's chart_account_id
       └── Credit: Default cash account
```

### 8.6 Bank Reconciliation

```
1. Create reconciliation for a bank account
   └── Enter statement date, opening balance, closing balance

2. Enter statement items from bank statement
   └── Each item: date, description, debit/credit type, amount

3. Match items to GL TransactionLines
   └── System shows GL lines for that bank account's chart account
   └── Select matching GL line for each statement item

4. Unmatch if incorrect

5. Finalize
   └── Blocked if any items unmatched
   └── Sets status: reconciled, records reconciled_by and reconciled_at
```

### 8.7 Budget vs Actual

```
1. Create budget with fiscal year and date range

2. Add budget lines — one per Chart of Account
   └── Set monthly amounts (Jan–Dec)

3. Activate budget
   └── Previous active budget demoted to draft

4. Actual spend = sum of transaction_lines.debit
   WHERE account_id = line.chart_account_id
   AND date BETWEEN budget.start_date AND budget.end_date

5. Variance = Annual Budget − Actual Spend
```

---

## 9. Multi-Tenancy Pattern

The application uses **workspace-level isolation** via a `created_by` field on every data table.

```php
// User model
public function creatorId(): int
{
    // If the user IS the workspace owner (company/super admin), return own ID
    // Otherwise return the ID of the user who created them (their workspace)
    return in_array($this->type, ['company', 'super admin'])
        ? $this->id
        : $this->created_by;
}
```

Every data query is scoped:
```php
Invoice::where('created_by', Auth::user()->creatorId())->get();
```

This means:
- A `company` user sees only their own data
- An `employee` or `finance_officer` sees data belonging to the company that created them
- `super admin` can access all workspaces

---

## 10. Payment Gateway Integrations

The application supports 20+ payment gateways for invoice payment collection:

| Gateway | Controller |
|---|---|
| PayPal | `PaypalController` |
| Stripe | `StripeController` |
| Razorpay | `RazorpayController` |
| Flutterwave | `FlutterwaveController` |
| Mollie | `MollieController` |
| Skrill | `SkrillController` |
| Coingate | `CoingateController` |
| Paystack | `PaystackController` |
| Paytm | `PaytmController` |
| Paytab | `PaytabController` |
| Iyzipay | `IyzipayController` |
| PayFast | `PayFastController` |
| PayHere | `PayHereController` |
| Toyyibpay | `ToyyibpayController` |
| MercadoPago | `MercadopagoController` |
| Easebuzz | `EasebuzzController` |
| Cashfree | `CashfreeController` |
| Monnify | `MonnifyController` |
| PaymentWall | `PaymentWallController` |
| Khalti | `KhaltiController` |

All gateways integrate via public invoice links — customers click **Pay Now** on their invoice and are redirected to their chosen payment provider.

---

## 11. Configuration & Settings

### System Settings (stored in `settings` table, cached in `Utility::settings()`)

#### Company
| Key | Purpose |
|---|---|
| `company_name` | Company display name |
| `company_address`, `company_city`, `company_state`, `company_zipcode`, `company_country` | Address |
| `company_logo` | Logo path |
| `site_currency` | Currency code (e.g. NGN) |
| `site_currency_symbol` | Symbol (e.g. ₦) |
| `site_date_format` | Date format for display |

#### Numbering Prefixes
| Key | Example |
|---|---|
| `invoice_prefix` | `INV-` |
| `bill_prefix` | `BIL-` |
| `journal_prefix` | `JRN-` |
| `proposal_prefix` | `PRO-` |

#### Accounting Defaults
| Key | Purpose |
|---|---|
| `default_salary_expense_account` | Chart of Account ID for payroll expense |
| `default_ops_expense_account` | Chart of Account ID for MCC operational costs |
| `default_oss_revenue_account` | Chart of Account ID for OSS sales revenue |
| `default_cash_account` | Chart of Account ID for default cash/bank |

Configure at: **Settings → Company → Accounting Settings tab**

---

## 12. Setup & Deployment

### First-Time Installation

```bash
# Clone
git clone https://github.com/mohammedaliyu136/gondalfulbe-dev.git
cd gondalfulbe-dev

# Dependencies
composer install
npm install && npm run build

# Environment
cp .env.example .env
php artisan key:generate

# Configure database in .env, then:
php artisan migrate

# Seed permissions (run after first login as super admin)
php artisan db:seed --class=PermissionTableSeeder
```

### After Every Git Pull

```bash
php artisan optimize:clear   # Regenerates bootstrap/cache/modules.php
php artisan migrate          # Runs any new migrations
```

### Automated Post-Pull Hook (one-time setup per developer)

```bash
git config core.hooksPath .githooks
```

After this, `php artisan optimize:clear` runs automatically on `git pull`.

### Useful Commands

```bash
# List all modules and enabled/disabled status
php artisan module:list

# Run migrations for a specific module only
php artisan migrate --path=Modules/Accounting/Database/Migrations

# Grant all permissions to a user
php artisan tinker
>>> $user = User::where('email','...')->first();
>>> $user->syncPermissions(Permission::all()->pluck('name'));

# Clear permission cache
php artisan cache:clear
```

### Environment Variables (key ones in `.env`)

```env
APP_NAME="Gondal ERP"
APP_URL=http://127.0.0.1:8001

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=gondal_erp
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_FROM_ADDRESS=no-reply@gondal.test
```

---

## 13. Developer Reference

### Adding a New Module

```bash
php artisan module:make MyModule
```

Then:
1. Add `"MyModule": true` to `modules_statuses.json`
2. Delete `bootstrap/cache/modules.php` (or run `php artisan optimize:clear`)
3. Add sidebar entry in `resources/views/partials/admin/menu.blade.php`
4. Seed permissions in a migration using `Spatie\Permission\Models\Permission::firstOrCreate()`

### Posting a GL Entry from Any Module

```php
use App\Services\AccountingService;

// Generic double-entry post
AccountingService::post(
    debitAccountId:  $debitChartAccountId,
    creditAccountId: $creditChartAccountId,
    amount:          150000.00,
    reference:       'My Reference',
    referenceId:     $record->id,
    referenceSubId:  0,
    date:            now()->toDateString()
);

// Reverse (delete) GL entries for a record
AccountingService::reverse('My Reference', $record->id);
```

### Getting System Settings

```php
use App\Models\Utility;

$settings = Utility::settings();
$defaultCashAccount = $settings['default_cash_account'];
```

### Scoping Queries to Current Workspace

```php
// Always use creatorId() — never Auth::id() — for data queries
$data = MyModel::where('created_by', Auth::user()->creatorId())->get();
```

### Module View Namespacing

Views in `Modules/Accounting/Resources/views/dashboard/index.blade.php` are referenced as:

```php
return view('accounting::dashboard.index', compact(...));
```

### Common Permission Guard Pattern

```php
public function index()
{
    if (!Auth::user()->can('manage mymodule')) {
        return redirect()->back()->with('error', __('Permission denied.'));
    }
    // ...
}
```

---

## Appendix: MCC Reference

| MCC Name | Role in System |
|---|---|
| Mayo | Milk collection, center costs, logistics |
| Yola | Milk collection, center costs, logistics |
| Jabbi Lamba | Milk collection, center costs, logistics |
| Mubi | Milk collection, center costs, logistics |
| Sunkani | Milk collection, center costs, logistics |

---

*Last updated: April 2026. Maintained by the Gondal ERP development team.*

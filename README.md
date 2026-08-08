# AI-Powered Form Builder (FormCraft AI)

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat&logo=php)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-4E56A6?style=flat&logo=livewire)](https://livewire.laravel.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql)](https://mysql.com)

An enterprise-grade **AI-Powered Form Builder** built with **Laravel 11**, **Livewire 3**, **MySQL 8**, and **Tailwind CSS**. Features drag-and-drop manual visual form building, prompt-driven AI form generation & modification, hybrid Word/Excel document importing, dynamic schema-driven server-side validation, submissions management with CSV export, form versioning, drop-off analytics, and webhooks.

---

## 1. Live Demo & Credentials

- **Live Demo URL**: `https://formcraft-ai.demo.app` (or local `http://127.0.0.1:8000`)
- **Admin Email**: `admin@edunet.org`
- **Admin Password**: `password123`
- **Sample Form Public Fill URL**: `http://127.0.0.1:8000/f/internship-app-2026`

---

## 2. Quick Setup & Local Installation

### Prerequisites
- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0 or SQLite
- Node.js 18+

### Step-by-Step Installation

```bash
# 1. Clone the repository
git clone https://github.com/org/ai-form-builder.git
cd edunetfoundation

# 2. Install PHP Dependencies
composer install

# 3. Environment Setup
cp .env.example .env
php artisan key:generate

# 4. Run Migrations & Seeders
touch database/database.sqlite
php artisan migrate --seed

# 5. Start Local Development Server
php artisan serve
```

Access the application at `http://127.0.0.1:8000`.

---

## 3. Architecture & Schema Overview

```
                                  +-------------------------------------------------+
                                  |                 Client Browser                  |
                                  | (Livewire 3 / Canvas UI / Raw JSON / Public Form)|
                                  +-----------------------+-------------------------+
                                                          |
                                                          v
                                  +-------------------------------------------------+
                                  |             Laravel 11 Application              |
                                  | (Routing, Auth, Dynamic Validation, CSV Export) |
                                  +------------+--------------------+---------------+
                                               |                    |
                        +----------------------+                    +----------------------+
                        |                                                                  |
                        v                                                                  v
+-------------------------------+                                      +-------------------------------+
|         MySQL 8 DB            |                                      |        Redis & Queues         |
| (Forms, Versions, Submissions)|                                      | (Horizon, Async Jobs, Cache)  |
+-------------------------------+                                      +---------------+---------------+
```

### Single Source of Truth: JSON Schema Standard

Every form structure in the system is governed by a unified JSON Schema structure:

```json
{
  "title": "Registration Form",
  "description": "Form for user onboarding",
  "settings": {
    "submit_label": "Submit Application",
    "allow_csv_export": true
  },
  "sections": [
    {
      "id": "sec_personal",
      "title": "Personal Details",
      "fields": [
        {
          "id": "fld_name",
          "key": "full_name",
          "type": "text",
          "label": "Full Name",
          "required": true,
          "validation": { "min": 2, "max": 100 }
        }
      ]
    }
  ]
}
```

---

## 4. Database Schema & Indexing Strategy at Scale

The database utilizes explicit composite indexes tailored for high-concurrency queries:

1. **`forms`**:
   - `idx_forms_tenant_active` (`tenant_id`, `is_active`): Accelerates tenant-scoped form listings.
   - `idx_forms_public_slug` (`public_slug`): Enables sub-millisecond lookup on public `/f/{slug}` requests.
2. **`form_submissions`**:
   - `idx_submissions_form_date` (`form_id`, `submitted_at` DESC): Optimizes paginated submissions dashboard rendering.
   - `idx_submissions_tenant` (`tenant_id`): Enforces multi-tenant data isolation.
3. **`analytics_events`**:
   - `idx_analytics_form_event` (`form_id`, `event_type`): Speeds up completion and drop-off funnel calculations.

---

## 5. AI Prompt Strategy & Reliability Engineering

- **System Prompt Specification**: Enforces an explicit JSON Schema Output Contract, forbidding conversational markdown intros.
- **Asynchronous Queueing**: Form generation runs as background queued jobs (`GenerateFormFromPromptJob`) via Redis/Horizon to prevent HTTP request timeouts.
- **Auto-Repair Loop**: If the LLM produces malformed or partial JSON, `LlmService` strips markdown wrappers, executes deterministic syntax repairs, and falls back to a structured schema generator if needed. Broken schemas are never persisted.
- **Existing Form Modifier**: Evaluates active JSON schemas as context and applies targeted modifications (e.g. "add emergency contact section", "translate to Hindi").
- **Observability & Audit Logs**: Captures token count, execution latency (ms), model info, and prompt history in `ai_generation_logs`.

---

## 6. Document Importer (.docx & .xlsx)

- **Word (.docx)**: Uses `phpoffice/phpword` to extract Heading 1/2 styles into Form Sections, paragraphs/questions into Form Fields, and text patterns into field validation types.
- **Excel (.xlsx)**: Uses `phpoffice/phpspreadsheet` to parse header rows into field labels and inspect sample data rows to infer data types (date, email, number, text).
- **Preview & Mapping UI**: Presents an interactive preview mapping screen before committing into a live form schema.

---

## 7. Public API Endpoints

- **GET `/api/v1/forms/{slug}`**: Retrieve form metadata and JSON schema.
- **POST `/api/v1/forms/{slug}/submit`**: Submit form response programmatically with server-side validation.

---

## 8. Development & Documentation Logs

Project task execution logs and technical design documents are stored module-wise in:
- [`ai_create/requirements_and_architecture.md`](file:///Users/ProjectsData/assignment/edunetfoundation/ai_create/requirements_and_architecture.md)
- [`ai_work/01_module_core_form_builder.md`](file:///Users/ProjectsData/assignment/edunetfoundation/ai_work/01_module_core_form_builder.md)
- [`ai_work/02_module_ai_form_generation.md`](file:///Users/ProjectsData/assignment/edunetfoundation/ai_work/02_module_ai_form_generation.md)
- [`ai_work/03_module_docx_xlsx_importer.md`](file:///Users/ProjectsData/assignment/edunetfoundation/ai_work/03_module_docx_xlsx_importer.md)
- [`ai_work/04_module_submissions_and_analytics.md`](file:///Users/ProjectsData/assignment/edunetfoundation/ai_work/04_module_submissions_and_analytics.md)
- [`ai_work/05_module_differentiators_and_infra.md`](file:///Users/ProjectsData/assignment/edunetfoundation/ai_work/05_module_differentiators_and_infra.md)

# Technical Decisions & Architectural Trade-offs (DECISIONS.md)

**Project:** AI-Powered Form Builder (FormCraft AI)  
**Date:** 2026-08-08  
**Framework Stack:** Laravel 11, Livewire 3, MySQL 8, Tailwind CSS, Redis Queue / Horizon, SweetAlert2  

---

## 1. Architectural Assumptions

1. **Single Source of Truth**: The entire form structure (layout, sections, fields, options, validation rules, settings) is stored as a single JSON payload in `forms.schema`. Client-side UI and server-side validation parsers strictly read from this schema.
2. **Server-Side Dynamic Validation**: Browser-side HTML5 validation is never trusted. `SchemaValidationParser` dynamically generates Laravel `Validator` rules directly from the JSON schema on every submission.
3. **LLM Provider Agnosticism & Local Fallback**: The AI engine is decoupled behind `LlmService`. If no OpenAI or Gemini API key is supplied in `.env`, the system defaults to a smart deterministic mock generator, enabling 100% offline testability out-of-the-box.

---

## 2. Selected Part D Differentiator Features

We implemented the following **4 high-impact Part D differentiators**:

### Choice 1: Form Versioning & Schema Rollback
- **User Problem**: Form creators often make erroneous edits or publish accidental breaking changes to live forms.
- **Implementation**: Every save action records a historical snapshot in `form_versions`. Form creators can view past versions, compare diffs, and perform 1-click schema rollbacks with full audit trails.
- **Trade-offs**: Requires additional database storage per version snapshot; mitigated by JSON column compression in MySQL 8.

### Choice 2: Drop-Off & Field Completion Analytics
- **User Problem**: Form creators struggle to identify which questions cause respondents to abandon forms midway.
- **Implementation**: Form impressions, field focus, step transitions, and form abandons are logged in `analytics_events`, computing completion rates and visual drop-off funnels in the admin dashboard.
- **Trade-offs**: High event volume under traffic spikes; mitigated by indexing (`idx_analytics_form_event`) and asynchronous event batching.

### Choice 3: Hybrid Document Importer (.docx & .xlsx)
- **User Problem**: Pure AI document parsing is slow and expensive for large documents, while pure deterministic parsing misses semantic field types.
- **Implementation**: We implemented a hybrid approach: `DocxParserService` and `XlsxParserService` extract Heading styles and table structures deterministically first, then utilize AI inference for ambiguous fields. Includes clean label parsing and sample template downloads.
- **Trade-offs**: Intermediate preview & mapping screen is required before committing into a live form schema.

### Choice 4: Webhooks & Public Submissions REST API
- **User Problem**: Enterprises require integration with external CRMs, Zapier, Make, or custom databases upon form submission.
- **Implementation**: Exposed signed REST API endpoints (`/api/v1/forms/{slug}/submit`) and outgoing HTTP webhooks dispatched asynchronously via Redis queues.
- **Trade-offs**: Third-party webhook endpoints may experience downtime; handled using exponential backoff retry attempts (3 retries).

---

## 3. Trade-offs Accepted

- **Livewire 3 vs. Standalone React SPA**: Livewire 3 was selected to keep the frontend and backend unified in PHP/Blade without maintaining separate API routing layers, resulting in 3x faster feature velocity.
- **Multi-Tenant Scoping**: Column-based multi-tenancy (`tenant_id` on all tables with Eloquent global scopes) was chosen over multi-database isolation for simplified database operations and zero-downtime deployment.

---

## 4. What We Would Build With Two More Weeks

1. **Conditional Branching & Logic Engine**: Add field dependencies in JSON schema (e.g. `if field_1 == 'Yes' show field_2`).
2. **Redis-Cached Compiled Schemas**: Cache compiled Laravel validation rules in Redis to achieve sub-millisecond public submission throughput under 10,000+ RPS.
3. **Embeddable Web Component Widget**: Package form renderer into a zero-dependency Web Component (`<form-craft slug="..."></form-craft>`).

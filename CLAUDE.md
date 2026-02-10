# CLAUDE.md

## Project Overview

**euronics45** is a Moodle 4.5.3 local plugin (`local_euronics_preinserimento`) for **Euronics**. It provides a form for HR users of partner companies ("soci") to pre-register new employees on the platform, enabling timely enrolment in mandatory safety courses.

**Target platform:** Moodle 4.5.3 (requires >= 2024100700)
**License:** GPL v3

## Repository Structure

```
euronics45/
├── README.md
├── CLAUDE.md
└── local/euronics_preinserimento/      # Moodle local plugin
    ├── version.php                      # Plugin version & Moodle compatibility
    ├── lib.php                          # Navigation hooks & helper functions
    ├── index.php                        # Main page: form display, user creation, enrolment
    ├── settings.php                     # Admin settings (course IDs, company field, support email)
    ├── styles.css                       # Euronics-branded CSS (blue #0050d8 / yellow)
    ├── db/
    │   └── access.php                   # Capability: local/euronics_preinserimento:insertuser
    ├── classes/form/
    │   └── insert_user_form.php         # Moodle form (moodleform): name, surname, fiscal code, checkboxes
    └── lang/
        ├── en/local_euronics_preinserimento.php   # English strings
        └── it/local_euronics_preinserimento.php   # Italian strings
```

## Plugin Functionality

1. **Access control** — Only users with `local/euronics_preinserimento:insertuser` capability (granted to `manager` archetype) can access the form
2. **Company detection** — Reads the HR user's company from a custom profile field (configurable) or the standard `institution` field
3. **User creation** — Creates a Moodle user with:
   - Username = fiscal code (lowercase)
   - Auth method = manual
   - Institution = HR's company
   - Placeholder email = `{fiscalcode}@placeholder.local`
4. **Course enrolment** — Enrols the new user via the manual enrolment plugin in:
   - Sicurezza Specifica (if checkbox selected)
   - Sicurezza Aggiornamento (if checkbox selected)
   - Sicurezza Generale (automatic, if self-enrolment rule is active for the company)
5. **Feedback** — Shows success message with username, reminders about the data file and processing schedule (14:00 / 20:00), or error message with support email

## Admin Settings

Configured at: **Site administration > Plugins > Local plugins > Euronics - Inserimento utenti**

| Setting | Config key | Description |
|---------|-----------|-------------|
| Course Sicurezza Specifica | `course_sic_spec` | Moodle course ID |
| Course Sicurezza Aggiornamento | `course_sic_agg` | Moodle course ID |
| Course Sicurezza Generale | `course_sic_gen` | Moodle course ID |
| Support email | `support_email` | Shown in error messages |
| Company profile field | `company_field` | Shortname of custom profile field for company |

## Moodle Development Context

- **PHP** is the primary language; Moodle 4.5.3 requires PHP 8.1+
- **Forms** use the `moodleform` API (`lib/formslib.php`)
- **Strings** are in `lang/{en,it}/local_euronics_preinserimento.php`
- **Capabilities** are defined in `db/access.php`
- **Navigation** is extended via `local_euronics_preinserimento_extend_navigation()` in `lib.php`
- **Styling** uses `styles.css` (auto-loaded by Moodle for local plugins)
- Coding style follows [Moodle Coding Style](https://moodledev.io/general/development/policies/codingstyle)

## Commands

No local build/test commands configured yet. On the Moodle server:

- `php admin/cli/upgrade.php` — Run after deploying plugin changes
- `php admin/cli/purge_caches.php` — Purge Moodle caches
- `vendor/bin/phpunit` — PHPUnit tests (if configured)
- `vendor/bin/phpcs --standard=moodle local/euronics_preinserimento/` — PHP CodeSniffer

## Conventions for AI Assistants

- Read existing files before making changes
- Keep changes minimal and focused on what is requested
- Follow Moodle coding standards (PHPDoc, `defined('MOODLE_INTERNAL') || die()`, etc.)
- All user-visible strings must go through the `lang/` files (Italian + English)
- Do not create unnecessary files or documentation beyond what is asked
- Commit messages should be concise and descriptive
- The plugin is in **alpha** stage — expect iterative refinements

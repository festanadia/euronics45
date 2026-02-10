# CLAUDE.md

## Project Overview

**euronics45** is a Moodle-based project. The repository is in early-stage setup with no source code implemented yet. The README references the [Moodle](https://moodle.org) open-source learning platform (GPL v3 licensed).

## Repository Structure

```
euronics45/
├── README.md       # Project README (Moodle template + project identifier)
└── CLAUDE.md       # This file
```

## Current State

- Single initial commit by `nadiafesta <nadia.festa@ariadne.it>`
- No source code, build system, or dependencies configured
- No CI/CD pipeline
- No test infrastructure

## Moodle Development Context

If this project develops into a Moodle plugin or customization:

- **Moodle plugins** follow a specific directory structure (`mod/`, `local/`, `block/`, `auth/`, etc.)
- **PHP** is the primary language; Moodle requires PHP 8.1+
- **JavaScript** uses ES modules and AMD; Moodle uses Grunt for JS/CSS build tasks
- **Database** changes use XMLDB (`db/install.xml`, `db/upgrade.php`)
- **Strings** go in `lang/en/*.php` for localization
- **Capabilities** are defined in `db/access.php`
- **Version** metadata is declared in `version.php`
- Coding style follows [Moodle Coding Style](https://moodledev.io/general/development/policies/codingstyle)

## Commands

No build, test, or lint commands are configured yet. When the project matures, expect:

- `php admin/cli/install.php` — Moodle CLI installation
- `vendor/bin/phpunit` — PHPUnit tests (if configured)
- `npx grunt` — JavaScript/CSS build tasks (if Grunt is adopted)
- `vendor/bin/phpcs --standard=moodle` — PHP CodeSniffer with Moodle rules

## Conventions for AI Assistants

- Read existing files before making changes
- Keep changes minimal and focused on what is requested
- Follow Moodle coding standards if PHP/JS code is added
- Do not create unnecessary files or documentation beyond what is asked
- Commit messages should be concise and descriptive

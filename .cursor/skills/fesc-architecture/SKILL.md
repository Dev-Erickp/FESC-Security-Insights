---
name: fesc-architecture
description: >-
  Modular monolith architecture for FESC Security Insights — domains, planned
  modules, SOLID/KISS boundaries and anti-overengineering. Use when designing
  features, placing code, or deciding module boundaries.
---

# FESC Architecture

## Shape

- Modular monolith, shared DB multi-tenancy.
- Planned domains under `app/Modules/` only when implementation starts.
- Keep Laravel conventional paths (`Models`, `Http`, `routes`, `resources/views`).

## Planned domains (not implemented yet)

Organizations, Users, Frameworks, Assessments, Controls, Evidence, Findings, Reports, Dashboard, Audit.

## Rules

1. Do not invent APIs/modules/migrations for future domains.
2. Prefer extending existing auth/tenancy foundations.
3. Record durable decisions in `docs/AI/DECISIONS.md`.
4. Use Project Intelligence Index to choose what to read.

## Read first

- `docs/ARCHITECTURE.md`
- `docs/AI/ARCHITECTURE_MAP.md`
- `app/Modules/README.md`

---
name: fesc-git-flow
description: >-
  Mandatory Git Flow for FESC Security Insights — permanent branches main/qa/develop,
  one requirement one branch, Conventional Commits, PR gates. Use before any
  code change, branch creation, commit, push or pull request.
---

# FESC Git Flow

## Permanent branches

| Branch | Role |
| --- | --- |
| `main` | Production-stable |
| `qa` | Validation |
| `develop` | Integration |

Agents must **never** commit directly to `main`, `qa`, or `develop`.

## Requirement branches

- From updated `develop`: `<tipo>/FSI-XXX-descripcion-corta`
- Types: `feature/`, `fix/`, `hotfix/`, `refactor/`, `test/`, `docs/`, `chore/`, `security/`
- Flow: requirement branch → PR → `develop` → PR → `qa` → PR → `main`

## Agent hard rules

1. Identify requirement + ID before coding.
2. Confirm own branch from `develop`.
3. No commit/push/PR/merge/delete without explicit user authorization.
4. Hotfix from `main`, then back-port to `qa` and `develop`.

## Read first

- `docs/AI/GIT_WORKFLOW.md`
- `docs/AI/WORK_ITEMS.md`

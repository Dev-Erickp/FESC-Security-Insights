# Skills Registry

## Herramientas de IA detectadas

| Herramienta | Estado | Notas |
| --- | --- | --- |
| Cursor | Activa | Rules + project skills en `.cursor/` |
| OpenAI Codex CLI | Disponible (`codex`) | Usar `AGENTS.md` como entrada |
| GitHub CLI | Disponible (`gh`) | PRs/protección con autorización |
| GitHub Copilot | No configurado en el repo | Sin kit local detectado |
| Claude Code CLI | No detectado en PATH | — |

## Skills de proyecto (creadas)

| Name | Path | Propósito | Cuándo usar |
| --- | --- | --- | --- |
| `fesc-laravel-conventions` | `.cursor/skills/fesc-laravel-conventions/` | Convenciones Laravel 13 | Código PHP/Laravel |
| `fesc-multitenancy-security` | `.cursor/skills/fesc-multitenancy-security/` | Tenancy + seguridad | `organization_id`, permisos, IDOR |
| `fesc-livewire-blade` | `.cursor/skills/fesc-livewire-blade/` | UI Livewire/Blade/Flux | Vistas y estilos |
| `fesc-testing` | `.cursor/skills/fesc-testing/` | Pest y calidad | Tests |
| `fesc-git-flow` | `.cursor/skills/fesc-git-flow/` | Git Flow obligatorio | Antes de editar/commit/PR |
| `fesc-architecture` | `.cursor/skills/fesc-architecture/` | Monolito modular | Diseño y ubicación de código |

Fuente: creadas en este repositorio (no paquetes externos no verificados).

## Skills / capacidades built-in relevantes (Cursor)

| Name | Fuente | Uso en este proyecto |
| --- | --- | --- |
| `create-skill` / `create-rule` | Cursor built-in | Mantener skills/rules |
| `review-security` | Cursor built-in | Revisión de seguridad bajo pedido |
| `review-bugbot` | Cursor built-in | Review de cambios bajo pedido |
| Project Intelligence Index | Repo (`project:query`) | Elegir qué leer |

## Reglas always-on

| Rule | Path |
| --- | --- |
| Project Intelligence | `.cursor/rules/project-intelligence.mdc` |
| FESC Agent Workflow | `.cursor/rules/fesc-agent-workflow.mdc` |

No se instalaron skills de terceros no verificadas.

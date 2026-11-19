<!-- BEGIN AI_MATE_AGENTS_IMPORT -->
@AGENTS.md
<!-- END AI_MATE_AGENTS_IMPORT -->

## Spécifique Claude Code

- Serveurs MCP du projet (`.mcp.json`) : `chrome-devtools` et `playwright`, pour piloter l'app dans un navigateur.
- Skills projet (`.claude/skills/`) : `design-system` (design system « Paper ») et les skills de diagnostic `mate-*`.
- Vue d'ensemble du pipeline forge : `/forge:help`.

## Skills disponibles

Deux plugins structurent le travail sur ce dépôt :

### Plugin `forge` — pipeline de développement

Marketplace `gabrielmustiere/forge`. Quatre phases (0 décor, 1 cadrage, 2 implémentation, 3 clôture), trois tracks (feature, refacto, tech). Artifacts dans `forge/story/NNN-<f|r|t>-<slug>/`.

| Skill                                                                  | Usage                                               |
|------------------------------------------------------------------------|-----------------------------------------------------|
| `/forge:help`, `/forge:status`                                         | Vue d'ensemble du pipeline, point de situation      |
| `/forge:vision`, `/forge:product-backlog`, `/forge:stack`              | Phase 0 : vision, backlog, stack                    |
| `/forge:feature-interview`, `/forge:feature-pitch`, `/forge:feature-plan`, `/forge:feature-implem` | Track feature |
| `/forge:refactor-plan`, `/forge:refactor-implem`                       | Track refacto                                       |
| `/forge:tech-plan`, `/forge:tech-implem`                               | Track technique                                     |
| `/forge:review`, `/forge:sync`, `/forge:user-doc`, `/forge:commit`     | Clôture                                             |
| `/forge:release`                                                       | Release versionnée                                  |
| `/forge:test-scenario`                                                 | Jouer un scénario utilisateur (Playwright/DevTools) |
| `/forge:adr`, `/forge:grill`, `/forge:estimate`, `/forge:doc-feature`, `/forge:glossary` | Transverses                       |
| `/forge:agents-md`, `/forge:rules`, `/forge:backfill-metadata`, `/forge:migrate-forge` | Maintenance des instructions et metadata |

### Plugin `symfony` — recettes framework

Marketplace `gabrielmustiere/skills`. À invoquer quand on touche au domaine concerné (controllers, doctrine, forms, events, messenger, validation, etc.).

| Domaine        | Skills                                                                                          |
|----------------|-------------------------------------------------------------------------------------------------|
| HTTP           | `/symfony:routing-define`, `/symfony:controller-action`                                         |
| Doctrine       | `/symfony:doctrine-entity`, `/symfony:doctrine-migration`, `/symfony:doctrine-query`            |
| Forms          | `/symfony:form-type`, `/symfony:form-render`, `/symfony:form-handle`, `/symfony:form-advanced`  |
| Events         | `/symfony:event-dispatch`, `/symfony:event-listen`, `/symfony:event-subscribe`                  |
| Services / DI  | `/symfony:service-define`, `/symfony:service-wire`, `/symfony:service-tags`                     |
| Validation     | `/symfony:validation-constraints`, `/symfony:validation-groups`, `/symfony:validation-use`      |
| HTTP Client    | `/symfony:http-client-request`, `/symfony:http-client-async`, `/symfony:http-client-response`, `/symfony:http-client-test` |
| Messenger      | `/symfony:messenger-async`                                                                      |
| Serializer / Mapper | `/symfony:serializer-use`, `/symfony:object-mapper`                                        |

Préfèrer ces skills aux conventions ad hoc — elles encodent les patterns retenus pour ce template.

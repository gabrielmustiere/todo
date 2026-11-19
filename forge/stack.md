# Stack technique — Todo

> Dernière mise à jour : 2026-11-19 — cartographie factuelle de la stack. Chaque entrée est prouvée par un fichier du dépôt (source entre parenthèses) ou marquée _non renseigné_.

## Vue d'ensemble

Monolithe Symfony rendu côté serveur (Twig + Turbo/Stimulus), en PHP 8.5, avec une base SQLite fichier. Il tourne **en local uniquement**, sur la machine de l'utilisateur, derrière le proxy HTTPS de la Symfony CLI (`todo.wip`). Le projet a été amorcé depuis le template `gabrielmustiere/symfony-template`.

| Couche | Techno principale |
|---|---|
| Langage(s) | PHP ≥ 8.5, TypeScript (tests E2E uniquement) |
| Backend | Symfony 8.1 |
| Frontend | Twig + AssetMapper + Stimulus / UX Turbo, Tailwind CSS 4, Flowbite 4 |
| Données | SQLite (`var/data.db`), Doctrine ORM 3 |
| Ops | Docker (Mailpit de dev uniquement), exécution locale |
| DevOps | aucune CI ; Makefile + Mago |

## Langages & runtimes

- **PHP** `>=8.5`, version de travail 8.5 — sources : `composer.json`, `.php-version`
- **TypeScript** — limité aux tests E2E Playwright — source : `playwright.config.ts`, `package.json`
- **Node.js / npm** — outillage front et Playwright, version non épinglée — source : `package.json`, `package-lock.json`

## Backend

- **Framework** : Symfony `8.1.*` (Flex, `extra.symfony.require: 8.1.*`, `allow-contrib: false`) (`composer.json`)
- **ORM / données** : Doctrine ORM `^3.7.5`, DoctrineBundle, Doctrine Migrations (`composer.json`, `config/packages/doctrine.yaml`)
- **Libs structurantes** : Form, Validator, Serializer, Security, Mailer, Notifier, HttpClient, Translation, Intl, Monolog (`composer.json`)
- **Messagerie asynchrone** : Symfony Messenger, transport Doctrine (`MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0` dans `.env`, `symfony/doctrine-messenger`). Un worker `messenger:consume async` est déclaré dans `.symfony.local.yaml`.
- **Authentification** : firewall `main` avec `/login` public et `^/` réservé à `ROLE_USER` (`config/packages/security.yaml`). Voir « Contraintes & dette ».

## Frontend

- **Rendu** : Twig, `twig/extra-bundle`, `twig/html-extra`, `tales-from-a-dev/twig-tailwind-extra` (`composer.json`)
- **Bundler / build** : AssetMapper `8.1.*` + `importmap.php`, sans bundler JS (`composer.json`, `importmap.php`)
- **Interactivité** : Stimulus (`symfony/stimulus-bundle ^3.6.0`), UX Turbo, UX Live Component, UX Icons (`composer.json`)
- **CSS** : Tailwind CSS `^4.3.3` compilé par `symfonycasts/tailwind-bundle` (`package.json`, `composer.json`, `config/packages/symfonycasts_tailwind.yaml`)
- **UI kit** : Flowbite `^4.0.2` (`flowbite-bundle`), UX Toolkit, design system « Paper » dont les tokens `@theme` sont dans `assets/styles/app.css` (`package.json`, `composer.json`, `DESIGN.md`)
- **TypeScript** : non pour l'application, uniquement pour les tests E2E.

## Données & stockage

- **Base de données** : SQLite fichier `var/data.db` (`DATABASE_URL` dans `.env` et `.env.dev`)
- **Cache / sessions** : configuration Symfony par défaut (`config/packages/cache.yaml`), pas de service dédié
- **File / queue** : table Doctrine via Messenger (voir Backend)
- **Recherche, stockage objet** : aucun

## Ops / Infrastructure

- **Conteneurisation** : Docker, utilisé **uniquement en dev** pour Mailpit (`axllent/mailpit:latest`, SMTP `127.0.0.1:1027`, UI `127.0.0.1:8027`) (`compose.yaml`). L'application elle-même n'est pas conteneurisée.
- **Orchestration / IaC** : aucune
- **Serveur d'application** : Symfony CLI avec proxy HTTPS `*.wip`, domaine `todo.wip` (`.symfony.local.yaml`)
- **Hébergement de production** : aucun, usage local uniquement — _source : déclaratif utilisateur (2026-11-19)_
- **CDN / reverse proxy, gestion des secrets de déploiement** : sans objet en local. `APP_SECRET` propre au poste dans `.env.dev` (`.env` le laisse vide).
- **Environnements** : `dev` et `test` (`.env`, `.env.dev`, `.env.test`) ; pas de staging ni de prod

## DevOps / CI-CD

- **Pipeline CI** : aucun (pas de `.github/`, ni autre configuration CI)
- **Tests** : PHPUnit (suites Unit + Functional, `phpunit.dist.xml`), Playwright pour l'E2E (`playwright.config.ts`, dossier `e2e/`)
- **Analyse statique / style** : Mago (formatage, lint, analyse, guard d'architecture) via `mago.toml` ; PHP Insights isolé dans `tools/phpinsights` (`Makefile`, `phpinsights.php`)
- **Hooks git / automatisation deps** : aucun détecté
- **Déploiement** : sans objet (usage local)

## Monitoring / observabilité

- **Erreurs / métriques / traces** : aucun outil externe, sans objet en local
- **Logs** : Monolog (`config/packages/monolog.yaml`)
- **Diagnostic de dev** : Web Profiler et Debug bundle (`composer.json`, `require-dev`)

## Outillage de développement local

- **Commandes QA / build** (`Makefile`) :
  - installation : `make init` (dépendances + base + fixtures), `make install`, `make update`
  - exécution : `make serve`, `make serve-d`, `make stop`
  - base : `make migrate`, `make migration`, `make db-reset`, `make fixtures`
  - tests : `make phpunit`, `make phpunit-filter`, `make playwright`
  - qualité : `make quality` (fmt + lint + analyse + guard), `make lint`, `make qa-full`
  - assets : `make build`
- **Services de dev** : `docker compose up -d` pour Mailpit (`compose.yaml`)
- **Assistance IA** : serveur MCP `symfony-ai-mate` (dossier `mate/`, `.mcp.json`, dépendances `symfony/ai-mate*`) et skills projet dans `.claude/skills` et `.agents/skills`

## Contraintes & dette de stack connues

- **Authentification en contradiction avec la vision** : le template impose une connexion (`ROLE_USER` sur tout `^/`), alors que `forge/vision.md` pose « pas de comptes ni de connexion » en anti-objectif. À trancher et à retirer via une story (`/tech-plan` ou `/feature-pitch`) ; rien n'est modifié ici.
- **Pas de CI** : aucun garde-fou automatique hors exécution manuelle de `make quality` et `make phpunit`.
- **PHP ≥ 8.5 requis** : aucune marge de compatibilité avec une version antérieure (`composer.json`).
- **Dépendances installées non résolues** : les versions ci-dessus sont des contraintes (`composer.json`, `package.json`). Les versions résolues sont dans `composer.lock` et `package-lock.json`, non lus ici.
- **Image Mailpit** : tag `latest`, non épinglé (`compose.yaml`).

## Changelog

- 2026-11-19 — Création — inventaire initial, amorcé depuis gabrielmustiere/symfony-template

# Changelog

Toutes les modifications notables de ce projet sont documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/),
et ce projet adhère au [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Chaque version porte un **titre** et distingue les **évolutions fonctionnelles**
(perceptibles à l'usage) des **évolutions techniques** (internes, outillage, plomberie).

## [Unreleased]

## [0.1.0] - 2026-11-19 — Ma liste de tâches

### ✨ Fonctionnel
- **Liste de tâches unique** — la page d'accueil devient ta todolist : tu tapes un texte, tu appuies sur Entrée, et la tâche apparaît en haut de la liste.
- **Cocher et décocher** — un clic marque une tâche comme faite : elle est barrée et passe sous les tâches à faire. Un second clic la remet à faire.
- **Supprimer** — un clic retire définitivement une tâche, sans confirmation.
- **Tâches conservées** — elles sont toujours là quand tu rouvres le navigateur.
- **Saisie encadrée** — un texte vide est ignoré, et au-delà de 255 caractères un message s'affiche sous le champ.

### 🔧 Technique
- **Socle applicatif** — Symfony 8.1, base SQLite, Tailwind CSS 4 et design system « Paper ».
- **Interface en Live Component** — la liste se met à jour sans rechargement de page (ADR-0001).
- **Tests** — PHPUnit (22 tests) et Playwright (7 scénarios, qui suppriment leurs propres tâches).
- **Qualité et pipeline forge** — Mago (format, lint, analyse, architecture) et documentation de la story dans `forge/`.

[Unreleased]: https://github.com/gabrielmustiere/todo/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/gabrielmustiere/todo/releases/tag/v0.1.0

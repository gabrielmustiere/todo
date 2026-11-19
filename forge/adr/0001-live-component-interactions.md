# ADR-0001 — Live Component pour les interactions dans la page

- **Statut** : accepted
- **Date** : 2026-11-19
- **Déciders** : l'utilisateur (mainteneur unique)
- **Story liée** : `forge/story/001-f-liste-de-taches/`

## Contexte

La story 001 (liste de tâches) crée la première interface interactive du projet. On y ajoute, coche et supprime des tâches sans quitter la page, au clavier, avec un champ qui garde le focus. Le projet hérite d'un template qui installe à la fois UX Live Component et Turbo 8, sans qu'aucun écran ne les utilise encore. Les écrans suivants (modification d'une tâche, listes multiples, dates…) reprendront le mécanisme choisi ici. La vision impose que l'ajout se fasse en « taper + Entrée » et la coche en un clic, et qu'aucune fonctionnalité superflue ne soit embarquée. Il faut donc un mécanisme d'interaction par défaut unique, plutôt qu'un choix refait à chaque écran.

**Périmètre** : la décision couvre les actions qui modifient des données **sans quitter la page**. Elle ne couvre pas la navigation entre pages, qui reste sur Turbo Drive. Elle ne couvre pas non plus les écrans-formulaires dédiés (connexion, réglages), où un contrôleur avec formulaire reste admis.

## Decision drivers

- **Driver 1 — Le moins de code possible** : principe « rien qui ne serve pas » de la vision et principe de simplicité de l'`AGENTS.md`.
- **Driver 2 — Une interaction instantanée au clavier** : pas de rechargement visible, focus conservé après l'ajout (principe 1 de la vision).
- **Driver 3 — Réutiliser la stack installée** : aucune nouvelle dépendance.
- **Driver 4 — Une logique testable en PHPUnit, sans navigateur.**

## Options considérées

### Option A — Live Component

Un composant PHP (`#[AsLiveComponent]`) porte l'état de l'écran et expose des `#[LiveAction]`. Chaque action délègue au service métier, puis le composant se re-rend par morphing. La validation passe par `ValidatableComponentTrait` et des contraintes sur les `LiveProp`.

- Aligne avec **Driver 1** : oui. Une classe et un template par écran, sans route, sans FormType et sans CSRF par action.
- Aligne avec **Driver 2** : oui. Le morphing conserve l'élément focalisé, et le champ peut être lié sans re-rendu à la frappe. Le focus reste un point à vérifier en E2E.
- Aligne avec **Driver 3** : oui. `symfony/ux-live-component` est installé et activé (`composer.json`, `assets/controllers.json`).
- Aligne avec **Driver 4** : oui. `InteractsWithLiveComponents` permet d'appeler les actions et d'inspecter le rendu en PHPUnit.
- Coût / trade-off : rien ne fonctionne sans JavaScript, chaque action fait un aller-retour serveur et re-rend tout le composant, et le point d'entrée HTTP est implicite (endpoint générique du bundle).

### Option B — Contrôleurs + formulaires + redirection + morphing Turbo 8

Une route POST par action, un FormType, du CSRF, une redirection vers la page après traitement (PRG), et un rafraîchissement par morphing Turbo.

- Aligne avec **Driver 1** : partiel. Il faut trois actions de contrôleur et un FormType pour la seule liste de tâches.
- Aligne avec **Driver 2** : partiel. Il faut rétablir le focus après chaque redirection.
- Aligne avec **Driver 3** : oui. Turbo est installé.
- Aligne avec **Driver 4** : oui. `WebTestCase` permet de tester chaque route.
- Coût / trade-off : fonctionne sans JavaScript et les routes sont explicites, mais ce n'est pas un driver retenu.

### Option C — Contrôleurs + réponses Turbo Stream

Routes POST qui répondent par des fragments `append`, `replace` ou `remove`, sans re-rendu global.

- Aligne avec **Driver 1** : non. Il faut des routes et un template de stream par type de mise à jour.
- Aligne avec **Driver 2** : oui. Les mises à jour sont ciblées et le champ n'est pas re-rendu.
- Aligne avec **Driver 3** : oui.
- Aligne avec **Driver 4** : partiel. On teste des réponses de fragments, pas l'état de l'écran.
- Coût / trade-off : le gain de performance est nul à l'échelle d'un outil mono-utilisateur.

## Décision

**Option retenue : A — Live Component**

C'est la seule option qui satisfait pleinement le Driver 1 (moins de code) tout en tenant le Driver 2 (interaction au clavier sans rechargement). Elle n'ajoute aucune dépendance (Driver 3) et reste testable en PHPUnit (Driver 4). L'option B paie en code et en gestion du focus une capacité « sans JavaScript » qui n'est pas un driver. L'option C optimise une performance qui n'est pas un enjeu.

**Règle qui en découle** : un Live Component est un point d'entrée, au même titre qu'un contrôleur. Il valide, appelle un service, et ne porte **aucune logique métier ni requête** (chaîne point d'entrée → Service → Repository de l'`AGENTS.md`). Tout plan qui choisit un autre mécanisme pour une interaction dans la page justifie l'écart dans ses `### Alternatives écartées`.

**Condition de révision** : cet ADR est à rouvrir si un écran rend le re-rendu complet perceptible, ou si une fonctionnalité doit marcher sans JavaScript.

## Conséquences

**Positives**

- Aucune route, aucun FormType, aucun jeton CSRF à écrire par action.
- La validation et l'affichage des erreurs se font dans le même composant.
- L'état de l'écran et ses actions sont testés en PHPUnit, et l'E2E se limite au ressenti (focus, clavier).

**Négatives / coûts assumés**

- L'application ne fonctionne pas sans JavaScript.
- Chaque action fait un aller-retour serveur et re-rend tout le composant.
- Le focus et l'état du DOM dépendent du morphing. Si un cas résiste, la parade est un petit contrôleur Stimulus.
- Les points d'entrée n'apparaissent pas dans la table de routage (`debug:router`). Il faut lire les classes de `src/Twig/Components/`.
- Tant que l'authentification existe, les requêtes du composant passent sous `access_control ^/` : une session expirée redirige les actions vers `/login`.

**Suites obligatoires**

_(aucune)_ : la story 001 applique déjà la décision.

## Links

- Artifact source : `forge/story/001-f-liste-de-taches/plan.md`
- ADR superseded : aucun
- ADR liés : aucun
- Références externes : documentation Symfony UX Live Component, documentation Turbo 8 (morphing, streams)

# Plan technique — Noter, cocher et supprimer mes tâches dans une liste unique

> **But** : figer le comment technique de la feature — architecture, périmètre de code, ordre d'exécution.
> **Registre** : technique
> **Story** : `forge/story/001-f-liste-de-taches/`
> **Amont** : `pitch.md`
> **ADR** : `forge/adr/0001-live-component-interactions.md`

## Approche retenue

La liste est portée par un **Live Component `TaskList`**, rendu par la page `/` existante (`PageController::index`). Le composant joue le rôle de point d'entrée : il expose trois LiveActions (ajouter, basculer, supprimer), valide la saisie et délègue toute règle métier à un service `TaskManager`. Ce service trimme le texte, ignore le vide, horodate via `ClockInterface`, bascule `completedAt` et écrit en base via l'`EntityManagerInterface`. Les lectures passent par deux méthodes nommées de `TaskRepository` : tâches à faire triées par création décroissante, tâches faites triées par complétion décroissante, avec l'id décroissant en second critère car les dates sont stockées à la seconde. On respecte ainsi la chaîne imposée par l'`AGENTS.md` (point d'entrée → Service → Repository → Entity) : ni logique dans l'entité, ni QueryBuilder hors repository.

Après chaque action, le composant se re-rend en entier par morphing. La liste ne dépassera jamais quelques centaines de lignes pour un seul utilisateur, donc ce re-rendu global ne coûte rien. Il évite aussi de maintenir des fragments partiels. Le champ de saisie est un `<input>` natif lié au composant sans re-rendu à la frappe (`data-model` posé sur `<twig:Input>` serait capté par Live comme liaison de composant enfant), et le morphing conserve l'élément focalisé. Après un ajout réussi, la propriété est remise à vide. Une tâche ciblée par une action mais introuvable (supprimée dans un autre onglet) est ignorée silencieusement, et le re-rendu montre l'état réel.

### Mécanismes mobilisés

- **Live Component (`#[AsLiveComponent]`, `DefaultActionTrait`, `#[LiveAction]`, `#[LiveArg]`)** : ajout, coche et suppression sans rechargement, sans route ni FormType dédiés. Déjà installé et activé (`assets/controllers.json`).
- **`ValidatableComponentTrait` + `#[Assert\Length(max: 255, normalizer: 'trim')]`** sur la `LiveProp` du texte, appelé en `validate(throw: false)` puis `isValid()` : erreur affichée sous le champ, avec le texte conservé (règle 4 du pitch), sans réponse 422, ce qui garde le cas testable via `InteractsWithLiveComponents`. Le `normalizer: 'trim'` mesure la longueur après suppression des espaces, comme la donnée stockée.
- **`ClockInterface`** (`symfony/clock`, présent via framework-bundle) : `createdAt` et `completedAt` sont déterministes en test grâce à `MockClock`.
- **Repository Doctrine** (`ServiceEntityRepository`) : `findTodo()` et `findDone()`, conformément à la règle « tout accès aux données via une méthode nommée du repository ».
- **Composants Twig du design system** (`Input`, `Checkbox`, `Button`, `templates/components/`) : rendu conforme au design system « Paper ».

### Alternatives écartées

| Alternative | Pourquoi écartée |
|---|---|
| Controller + 3 routes POST + FormType + PRG + morphing Turbo 8 | Trois actions, un FormType et le CSRF en plus, pour le même résultat. Il faudrait aussi rétablir le focus après la redirection. |
| Controller + réponses Turbo Stream | Plusieurs templates de stream à maintenir (append, replace, remove) pour un gain de performance nul à cette échelle. |
| Booléen `done` + `completedAt` | Deux champs à garder synchronisés, avec un état incohérent possible. `completedAt` non nul suffit à dire « faite ». |
| Index sur `created_at` / `completed_at` | Écart assumé avec le radar Symfony : pour un seul utilisateur et quelques centaines de lignes en SQLite, le tri est négligeable. À ajouter si la volumétrie change. |

## Modèle de données

### Nouvelle structure `Task`

`src/Entity/Task.php` (table `task`) :

| Champ | Type | Nullable | Contrainte |
|---|---|---|---|
| `id` | `integer`, auto | non | |
| `title` | `string(255)` | non | Texte déjà trimmé, non vide (garanti par le service), 255 caractères au plus |
| `createdAt` | `datetime_immutable`, colonne `created_at` | non | Posé à la création via `ClockInterface` |
| `completedAt` | `datetime_immutable`, colonne `completed_at` | oui | Non nul ⇔ tâche faite. Remis à `null` quand on décoche |

`Task` n'a **aucune relation avec `User`** : l'application est mono-utilisateur et la vision exclut les comptes. Pas de cloisonnement, pas de traduction, pas d'index (voir Alternatives écartées). Le constructeur reçoit `title` et `createdAt`, et un setter expose `completedAt`. L'entité ne porte aucune logique.

## Périmètre

### Fichiers à créer

| Fichier | Rôle |
|---|---|
| `src/Entity/Task.php` | Entité tâche : titre, date de création, date de complétion nullable. |
| `src/Repository/TaskRepository.php` | `findTodo()` (`completedAt IS NULL`, `createdAt DESC, id DESC`) et `findDone()` (`completedAt IS NOT NULL`, `completedAt DESC, id DESC`). |
| `src/Service/TaskManager.php` | `add(string)` (trim, vide → rien, horodatage), `toggle(int)` (pose ou efface `completedAt`), `delete(int)`. Un id introuvable est ignoré. |
| `src/Twig/Components/TaskList.php` | Live Component : LiveProp `title` validée, LiveActions `add`, `toggle`, `delete`, et exposition des tâches à faire et faites au template. |
| `templates/components/TaskList.html.twig` | Formulaire de saisie (Entrée → `add`), erreur sous le champ, section à faire, section faites (barrées), état vide, sélecteurs `data-test`. |
| `migrations/Version<horodatage>.php` | Création de la table `task`, générée par `symfony console make:migration`. |
| `tests/Service/TaskManagerTest.php` | Functional (`KernelTestCase`, base de test, `MockClock`) : trim, texte vide ou blanc ignoré, `createdAt` = horloge, coche puis `completedAt` = horloge, décoche puis `null`, suppression, id introuvable sans effet. |
| `tests/Repository/TaskRepositoryTest.php` | Functional (base de test) : ordre de `findTodo()` et de `findDone()`, et séparation à faire / faites. |
| `tests/Twig/Components/TaskListTest.php` | Functional (`InteractsWithLiveComponents`, `actingAs` admin) : ajout, vide ignoré, 256 caractères → erreur et texte conservé, 255 après trim accepté, bascule, suppression, état vide, id introuvable. |
| `tests/e2e/tasks.spec.ts` | E2E auto-nettoyant (titres `e2e-<aléa>`) : enchaînement de 3 ajouts au clavier avec focus conservé, coche, décoche et ordre, suppression, persistance après rechargement. |

### Fichiers à modifier

| Fichier | Modification |
|---|---|
| `templates/page/index.html.twig` | Remplacer tout le faux tableau de bord par un h1 « Tâches » et `<twig:TaskList />`. Titre de page « Tâches ». |
| `templates/base.html.twig` | Lien de sidebar vers `app_page` : libellé « Tableau de bord » → « Tâches » (icône adaptée). La topbar n'est pas touchée. |
| `tests/Controller/SecurityControllerTest.php` | `testLoginWithValidCredentials` : attendre « Tâches » au lieu de « Tableau de bord ». |
| `tests/e2e/login.spec.ts` | Attendre « Tâches » au lieu de « Tableau de bord ». |
| `tests/e2e/logout-prefetch.spec.ts` | Attendre « Tâches » au lieu de « Tableau de bord ». |

## Hors scope

- **Nettoyage du layout** (recherche, notifications et menu profil factices de la topbar) : hors pitch.
- **Retrait de l'authentification** : story technique séparée (voir `forge/stack.md` §Contraintes).
- **Isolation de la base E2E** (serveur dédié en `APP_ENV=test`) : remplacée par des tests auto-nettoyants. À reconsidérer dans une story technique si les données E2E posent problème.
- **Protection des vraies tâches contre `make db-reset` / `make fixtures`** : voir Risques.
- **Index de tri** : volumétrie trop faible pour les justifier.
- **Tâches d'exemple dans `AppFixtures`** : volontairement absentes, `AppFixtures` n'est pas modifié.

## Impacts transverses

- **Cloisonnement des données** : aucun. `Task` n'a ni relation ni filtre d'appartenance (mono-utilisateur).
- **Déclinaisons / thèmes** : non. Un seul rendu, design system « Paper » via les composants Twig existants.
- **Traduction / i18n** : non. Libellés en français en dur, comme le reste des templates du projet (pas de `|trans` dans le dépôt). Le pitch exclut l'i18n.
- **API / exposition externe** : non. Seul l'endpoint interne du Live Component est appelé.
- **Droits d'accès** : mécanisme existant suffisant. Les requêtes du Live Component passent sous `^/` et exigent donc `ROLE_USER` (`config/packages/security.yaml`). Pas de voter.
- **Emails / notifications** : non.
- **Migration de données** : création de la table `task` uniquement, sans reprise de l'existant. Le `down()` supprime la table.
- **Comportement par défaut** : `/` affiche la liste (vide au départ) au lieu du faux tableau de bord.

## Stratégie de test

| Code | Type | Ce qu'on vérifie |
|---|---|---|
| `src/Service/TaskManager.php` | functional | `add` trimme et horodate avec `MockClock`. `add('')` et `add('   ')` ne persistent rien. `toggle` pose `completedAt` = horloge, puis le remet à `null`. `delete` retire la tâche. `toggle`/`delete` sur un id introuvable sont sans effet. Repository et EntityManager réels sur la base de test (`KernelTestCase`) : Mago impose `enforce-class-finality`, et un repository `final` ne peut pas être doublé. La table `task` est purgée en `setUp`. |
| `src/Repository/TaskRepository.php` | functional | Avec des dates explicites : `findTodo()` renvoie uniquement les tâches non faites, de la plus récente à la plus ancienne. `findDone()` renvoie uniquement les tâches faites, de la plus récemment cochée à la plus ancienne. À date de création égale, la dernière créée vient en premier. La table `task` est purgée en `setUp`. |
| `src/Twig/Components/TaskList.php` | functional | Ajout → tâche en tête et champ vidé. Champ vide ou blanc → aucune tâche, aucune erreur. 256 caractères → erreur rendue, texte conservé, rien en base. 255 caractères après trim → accepté. Coche → section faites. Décoche → retour parmi les tâches à faire. Suppression → disparue. État vide affiché. Id introuvable → pas d'exception. La table `task` est purgée en `setUp`. |
| `tests/e2e/tasks.spec.ts` | E2E | 3 ajouts enchaînés au clavier (le champ garde le focus, la dernière tâche est en haut). Coche → barrée sous les tâches à faire. Décoche → retour à sa place. Suppression sans confirmation. Rechargement → tâches et états conservés. Ne cible que les titres `e2e-<aléa>` et supprime ses tâches en fin de test, via l'UI. |
| `tests/Controller/SecurityControllerTest.php`, `tests/e2e/login.spec.ts`, `tests/e2e/logout-prefetch.spec.ts` | functional / E2E | Mis à jour : la page d'atterrissage après connexion affiche « Tâches ». |

**Hors scope tests** :

- Pas de test unitaire de `Task` : l'entité n'a pas de logique.
- Pas de test de concurrence (deux onglets qui agissent en même temps) : un utilisateur unique, et le cas « id introuvable » est couvert.
- Pas de test sur le texte exact du message d'erreur de longueur : on vérifie sa présence, le libellé reste celui du validateur.
- Pas de nouveau test de disponibilité : `ApplicationAvailabilityFunctionalTest` couvre déjà `/`.

## Ordre d'exécution

1. [x] **Entité, repository et migration**
   - Objectif : la table `task` existe, et les deux requêtes de lecture sont disponibles.
   - Fichiers : `src/Entity/Task.php`, `src/Repository/TaskRepository.php`, `migrations/Version<horodatage>.php` (via `symfony console make:migration`).
   - Vérification : `symfony console doctrine:migrations:migrate -n`, `make db-validate`, puis `doctrine:migrations:migrate prev` pour vérifier le `down()`, et re-migrer.
   - Commitable seule : oui.

2. [x] **Service `TaskManager` + tests fonctionnels**
   - Objectif : règles métier (trim, vide ignoré, horodatage, bascule, suppression, id introuvable) testées.
   - Fichiers : `src/Service/TaskManager.php`, `tests/Service/TaskManagerTest.php`.
   - Vérification : `make phpunit-filter TaskManagerTest`.
   - Commitable seule : oui.

3. [x] **Tests fonctionnels du repository**
   - Objectif : les deux ordres de tri sont verrouillés.
   - Fichiers : `tests/Repository/TaskRepositoryTest.php`.
   - Vérification : `make phpunit-filter TaskRepositoryTest`.
   - Commitable seule : oui.

4. [x] **Live Component `TaskList` + page d'accueil**
   - Objectif : la liste est utilisable sur `/`, la sidebar affiche « Tâches », et les tests existants sont réalignés.
   - Fichiers : `src/Twig/Components/TaskList.php`, `templates/components/TaskList.html.twig`, `templates/page/index.html.twig`, `templates/base.html.twig`, `tests/Twig/Components/TaskListTest.php`, `tests/Controller/SecurityControllerTest.php`.
   - Vérification : `make phpunit` vert, puis contrôle manuel sur `https://todo.wip` (ajout, Entrée, focus, coche, suppression).
   - Commitable seule : oui.

5. [x] **E2E Playwright**
   - Objectif : parcours clavier, focus et persistance vérifiés dans un vrai navigateur, sans laisser de données.
   - Fichiers : `tests/e2e/tasks.spec.ts`, `tests/e2e/login.spec.ts`, `tests/e2e/logout-prefetch.spec.ts`.
   - Vérification : `make playwright` vert, et aucune tâche `e2e-…` restante dans la liste.
   - Commitable seule : oui.

6. [x] **QA finale**
   - Objectif : qualité et suite de tests conformes au projet.
   - Fichiers : aucun nouveau (corrections éventuelles).
   - Vérification : `make quality` (fmt + lint + analyze + guard), `make phpunit`, `make playwright`.
   - Commitable seule : oui.

## Critères de sortie

- [x] La table `task` est créée par une migration générée, et son `down()` la supprime proprement.
- [x] `make db-validate` signale un mapping et un schéma synchronisés.
- [x] `Task` n'a aucune relation avec `User`, et `AppFixtures` n'est pas modifié.
- [x] Aucune requête ni QueryBuilder hors `App\Repository`, aucune logique métier dans `Task`, `TaskRepository` ou le composant (`make guard` vert, relecture).
- [x] `make phpunit` vert, nouveaux tests compris.
- [x] `make playwright` vert, et aucune tâche `e2e-…` ne subsiste dans la base de dev après exécution.
- [x] `make quality` vert.

## Risques et mitigations

| Risque | Probabilité | Mitigation |
|---|---|---|
| Le focus du champ est perdu après le re-rendu du Live Component | moyenne | Champ lié sans re-rendu à la frappe, et l'élément focalisé est conservé par le morphing. Assertion `toBeFocused()` dans l'E2E. Si l'assertion échoue, rétablir le focus via un petit contrôleur Stimulus, et le noter comme écart. |
| `make db-reset` / `make fixtures` purgent la base de dev, qui contient les vraies tâches | élevée | Hors périmètre du code, mais signalé ici. Ne pas lancer ces commandes sur la base de dev une fois l'usage réel commencé. À traiter dans une story technique (base de dev distincte de la base d'usage, ou sauvegarde). |
| Un E2E qui échoue en cours de route laisse des tâches `e2e-…` dans la base réelle | faible | Préfixe reconnaissable, nettoyage en `afterEach` via l'UI, suppression manuelle possible en un clic. |
| Session expirée : les actions Live reçoivent une redirection vers `/login` | faible | Comportement du firewall existant, disparaît avec le retrait de l'authentification. Pas de traitement spécifique. |
| Les tests fonctionnels partagent `var/data_test.db` sans rollback automatique | moyenne | Purge de la table `task` en `setUp` des tests qui en dépendent. Aucun ordre d'exécution supposé. |

## Questions ouvertes

_(aucune)_ — Accumulation des tâches faites (question ouverte du pitch) → tranché : tout garder visible, selon la recommandation (a) du pitch.

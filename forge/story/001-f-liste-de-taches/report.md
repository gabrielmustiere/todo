# Report — Noter, cocher et supprimer mes tâches dans une liste unique

> **But** : garder la trace de ce qui a dérivé entre l'intention et le livré, et de ce qui reste à faire.
> **Registre** : factuel
> **Story** : `forge/story/001-f-liste-de-taches/`
> **Amont** : `pitch.md` · `plan.md` · `review.md`

## Synthèse

- **Écarts** : 6 — 6 réalignés dans `plan.md`, 0 laissé tel quel.
- **Critères** : 12 / 12 atteints, cochés dans `pitch.md` ; 7 / 7 critères de sortie cochés dans `plan.md`.
- **Non livré** : aucun.
- **Dette** : 3 items, dont 1 critique.

La livraison suit l'approche du plan (Live Component, service, repository). Les écarts viennent tous de contraintes d'outillage découvertes à l'implémentation : finalité imposée par Mago, liaison `data-model` de Live, tests Live sans capture d'exception, précision des dates SQLite. Reste en dette un invariant de longueur absent du service, un contrôle visuel jamais fait, et le risque d'effacement de la base réelle.

## Écarts avec le plan

### Écarts volontaires

| Prévu | Réalisé | Raison | Réaligné dans |
|---|---|---|---|
| `TaskManagerTest` unitaire, repository en `createStub()` et EntityManager en `createMock()` | Test fonctionnel `KernelTestCase` : repository et EntityManager réels, `MockClock` injectée | `mago.toml` `enforce-class-finality = true` : `TaskRepository` est `final`, PHPUnit ne peut pas le doubler | `plan.md` §Périmètre, §Stratégie de test, §Ordre d'exécution (étape 2) |
| Tri `findTodo()` par `createdAt DESC`, `findDone()` par `completedAt DESC` | Second critère `id DESC` sur les deux requêtes | Colonnes `DATETIME` à la seconde : des ajouts rapides au clavier auraient un ordre indéterminé | `plan.md` §Approche retenue, §Périmètre |
| `ValidatableComponentTrait` avec `validate()` (réponse 422 en cas d'erreur) | `validate(throw: false)` puis `isValid()` | `InteractsWithLiveComponents` désactive la capture d'exceptions : la 422 sortait du test au lieu d'un rendu. Le comportement visible est identique | `plan.md` §Mécanismes mobilisés |
| Champ de saisie via le composant `Input` du design system | `<input>` natif qui reprend les classes du composant `Input` | `data-model` posé sur `<twig:Input>` est capté par Live comme liaison de composant enfant (erreur 500 au rendu) | `plan.md` §Approche retenue |

### Non implémenté

| Élément prévu | Raison | Action requise |
|---|---|---|
| Aucun | — | — |

### Ajouts non prévus

| Élément ajouté | Raison | Réaligné dans |
|---|---|---|
| Mise à jour de `tests/e2e/logout-prefetch.spec.ts` | Troisième test qui attendait « Tableau de bord » sur `/`, non repéré au plan | `plan.md` §Périmètre, §Stratégie de test, §Ordre d'exécution (étape 5) |
| Cas « 255 caractères après trim accepté » dans `TaskListTest` | Borne de la règle 4 du pitch, ajoutée en regroupant les tests de longueur (seuil `too-many-methods` de Mago) | `plan.md` §Périmètre, §Stratégie de test |

## Dette technique identifiée

1. **Base de dev = base d'usage réelle** — `make db-reset` et `make fixtures` purgent `var/data.db`, qui contient désormais les vraies tâches (risque consigné au plan, hors périmètre). Traiter dans une story technique : base de dev distincte, ou sauvegarde. **Critique** dès le premier usage réel.
2. **[ROBUSTESSE] La limite de 255 caractères ne vit que dans le composant** — `src/Service/TaskManager.php:20` — `TaskManager::add()` porte les autres règles (trim, vide ignoré), mais accepte un texte de longueur quelconque, et SQLite n'applique pas `VARCHAR(255)`. Le composant est le seul appelant aujourd'hui, donc il n'y a pas de bug, mais un futur appelant (commande, autre écran) contournerait la règle 4 du pitch. Placer l'invariant côté service, ou une contrainte sur l'entité, si un second appelant apparaît.
3. **Rendu visuel non contrôlé** (point « Hors review ») — vérifier la page « Tâches » en mode clair et sombre (barré, survol de la poubelle, état d'erreur) sur `https://todo.wip`.

## Leçons apprises

- **Repositories `final` imposés par Mago** : dans ce projet, un service qui dépend d'un repository se teste en `KernelTestCase` sur la base de test, jamais avec un double du repository. Un plan qui prévoit `createStub(XxxRepository)` est irréalisable.
- **`data-model` ne passe pas par un composant Twig** : sur `<twig:…>`, Live l'interprète comme une liaison parent-enfant. Un champ lié à une `LiveProp` doit être un élément HTML natif.
- **Validation Live et tests** : avec `InteractsWithLiveComponents`, `validate()` lève une exception au lieu de rendre l'erreur. `validate(throw: false)` + `isValid()` rend le cas testable sans changer l'UX.
- **Dates SQLite à la seconde** : tout tri chronologique a besoin d'un critère secondaire stable (`id`).

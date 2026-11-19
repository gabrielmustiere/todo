# Review — Noter, cocher et supprimer mes tâches dans une liste unique

> **But** : juger le diff au regard de l'intention — dire si on commite, et ce qui bloque.
> **Registre** : technique
> **Story** : `forge/story/001-f-liste-de-taches/`
> **Amont** : `plan.md` · `pitch.md`
> **Diff examiné** : `src/` et `templates/` de la story (projet hors git, fichiers relus en entier) — 5 créés (`Task`, `TaskRepository`, `TaskManager`, `TaskList.php`, `TaskList.html.twig`), 2 modifiés (`page/index.html.twig`, `base.html.twig`), ~280 lignes. Tests et migration hors périmètre demandé.

## Synthèse

- **Bloquants restants** : 0 / 0
- **Importants restants** : 0 / 0
- **Mineurs restants** : 1 / 3
- **Statut** : **PRÊT À COMMITER**

[A11Y] et [STYLE] sont corrigés. [ROBUSTESSE] reste en dette, par arbitrage utilisateur (un seul appelant). Prochaine étape : `/forge:sync`, puis `/forge:commit`.

## Bloquants

_(aucun)_

## Importants

_(aucun)_

## Mineurs

- [x] **[A11Y] Message d'erreur de longueur non annoncé aux lecteurs d'écran** — `templates/components/TaskList.html.twig:23` — le `<p>` apparaît après un re-rendu alors que le focus reste dans le champ, et un `aria-describedby` ajouté après coup n'est pas relu. Corrigé : `role="alert"` ajouté sur le message.
- [ ] **[ROBUSTESSE] La limite de 255 caractères ne vit que dans le composant** — `src/Service/TaskManager.php:20` — `TaskManager::add()` porte les autres règles (trim, vide ignoré), mais accepte un texte de longueur quelconque, et SQLite n'applique pas `VARCHAR(255)`. Le composant est le seul appelant aujourd'hui, donc il n'y a pas de bug, mais un futur appelant (commande, autre écran) contournerait la règle 4 du pitch. Placer l'invariant côté service, ou une contrainte sur l'entité, si un second appelant apparaît.
- [x] **[STYLE] Filtre des sections calculé deux fois** — `templates/components/TaskList.html.twig:27,31` — `sections|filter(tasks => tasks is not empty)` est évalué pour l'état vide puis pour la boucle. Corrigé : le filtre est appliqué une fois, à la déclaration de `sections`.

## Points positifs

- **Chaîne Controller → Service → Repository respectée** : le composant ne fait qu'orchestrer, toutes les écritures passent par `TaskManager`, toutes les lectures par deux méthodes nommées du repository, et l'entité n'a aucune logique.
- **Tri déterministe** : le `addOrderBy('t.id', 'DESC')` couvre des dates stockées à la seconde. Sans lui, trois ajouts rapides au clavier s'afficheraient dans un ordre aléatoire.
- **Un seul état pour « faite »** : `completedAt` nullable, sans booléen redondant. Aucun état incohérent n'est possible, et la North Star reste calculable.
- **Accessibilité de base soignée** : label `sr-only` sur le champ, case cochable via un `<label for>` qui porte le titre, bouton de suppression nommé « Supprimer « titre » », `aria-invalid` relié au style d'erreur.
- **Le seul commentaire est justifié** (`TaskList.html.twig:7`) : il documente une contrainte externe de Live Component que le code ne peut pas exprimer.

## Hors review (à vérifier en environnement réel)

- Rendu visuel de la page « Tâches » en mode clair et sombre (barré, survol de la poubelle, état d'erreur). Aucun test ne regarde l'apparence : ouvrir `https://todo.wip` et contrôler à l'œil.

# Vision — Todo

> Pitch en une phrase : une todolist personnelle dans le navigateur, pour mon propre usage au poste de travail, qui me donne un outil à moi, réduit à l'essentiel, en faisant de l'ajout d'une tâche un simple « taper + Entrée ».

_Document vivant — enrichi au fil du cycle de vie, refondu lors d'un pivot stratégique. Date de dernière mise à jour : 2026-11-19._

## Changelog

Historique des évolutions structurantes (création, enrichissements, éditions ciblées, pivots). Lecture du haut vers le bas = ordre chronologique. Détails fins dans `git log`.

| Date | Nature | Axe | Motif |
|------|--------|-----|-------|
| 2026-11-19 | Création | — | Vision initiale |
| 2026-11-19 | Éditer | Notes pour les features à venir | Pointeur MVP porté par une story — sync post-livraison de la story 001-f-liste-de-taches |

## Le problème

Pas d'irritant concret à l'origine du projet : la méthode actuelle de gestion des tâches ne pose pas de problème identifié. Le projet naît de l'envie d'avoir **sa propre** todolist, construite et maîtrisée de bout en bout, plutôt que d'utiliser celle d'un autre.

**Comment c'est résolu aujourd'hui** : méthode actuelle de l'utilisateur (outil existant, papier ou notes), sans insatisfaction déclarée.
**Pourquoi c'est insuffisant** : rien de bloquant. La motivation tient à la maîtrise de l'outil, pas à un manque. C'est la fragilité principale de la vision (voir hypothèse H1).
**Ampleur** : usage quotidien, sur chaque journée de travail.

## L'audience

### Utilisateur principal

- **Persona** : moi, développeur, au poste de travail, navigateur ouvert toute la journée.
- **Volume cible** : 1 utilisateur.
- **Ce qui le bloque aujourd'hui** : rien de précis, et c'est assumé.

### Utilisateurs secondaires

Aucun.

### Hors cible explicite

- Toute autre personne : pas de partage, pas d'équipe, pas de public. L'app n'a pas vocation à être distribuée.
- L'usage sur téléphone : le contexte d'usage est le navigateur de l'ordinateur.

## La proposition de valeur

### Bénéfice utilisateur

Un outil à moi, sans rien de superflu : noter une tâche ne coûte qu'une frappe et la touche Entrée, et cocher une tâche faite ne coûte qu'un clic.

### Pourquoi nous, plutôt qu'eux

Les alternatives (Todoist, Apple Rappels, Google Tasks…) font le travail. Celle-ci se distingue en étant **la mienne** : je décide de chaque fonctionnalité, et aucune n'est là si je ne m'en sers pas.

### Unfair advantage

Sans objet : outil personnel, aucun enjeu de concurrence.

## Métriques de succès

### North Star

**Jours d'usage** : nombre de jours ouvrés où au moins une tâche est ajoutée ou cochée. Mesurable directement à partir des données de l'app (dates de création et de complétion des tâches).

### Métriques secondaires

- **Acquisition** : sans objet (utilisateur unique).
- **Activation** : première tâche ajoutée le jour même de la mise en service du MVP.
- **Rétention** : la North Star elle-même, suivie semaine par semaine.
- **Monétisation** : sans objet.

### Seuils

- À 4 semaines après la mise en service du MVP : au moins 5 jours ouvrés sur 7, chaque semaine, 4 semaines d'affilée.
- À 6 mois : l'app reste l'unique endroit où je note mes tâches de travail.
- À 1 an / 3 ans : sans objet à ce stade, à reconsidérer si l'usage tient.

### Signal d'arrêt

Retour à l'ancienne méthode dans les 2 semaines suivant la mise en service du MVP.

## Principes produit

1. **Ajouter une tâche = taper + Entrée** : aucune fonctionnalité ne doit rallonger l'ajout ni la validation d'une tâche. Une date, une priorité ou une liste peuvent exister, à condition d'être facultatives et absentes du chemin par défaut. Ce principe tranche, par exemple, contre un formulaire d'ajout à plusieurs champs obligatoires.
2. **Rien qui ne serve pas** : une fonctionnalité n'entre que si je l'aurais utilisée la semaine précédente. Tout ajout est justifié par l'usage réel, pas par ce que font les autres apps.

## Anti-objectifs

Ce qu'on **refuse explicitement** de faire, et pourquoi :

- **Comptes et connexion** : un seul utilisateur, donc pas d'authentification ni de gestion de comptes.

## Hypothèses critiques

| # | Hypothèse | Comment l'invalider | Statut |
|---|-----------|---------------------|--------|
| H1 | Je l'adopterai au quotidien alors qu'aucun irritant ne m'y pousse. | North Star sur les 4 semaines suivant la mise en service du MVP ; invalidée si le signal d'arrêt se déclenche. | À tester |
| H2 | Ajouter, cocher et supprimer suffisent pour démarrer l'usage. | Noter pendant les 4 premières semaines chaque besoin manqué ; invalidée si un même manque bloque l'usage plusieurs fois par semaine. | À tester |

## Risques externes

Aucun identifié : pas de dépendance à un service tiers, pas d'enjeu réglementaire.

## Horizons

### 3-6 mois

- **MVP** : ajouter, cocher, supprimer une tâche, avec des tâches qui persistent à la fermeture du navigateur.
- 4 semaines d'usage pour tester H1 et H2.
- Ensuite, selon l'usage réel : modifier le texte d'une tâche, dates, listes multiples, priorités. L'ordre sera fixé par `/product-backlog` d'après les manques observés.

### 1 an

Sans objet à ce stade : dépend du résultat de H1.

### 3 ans

Sans objet.

## Notes pour les features à venir

- **MVP** : ajout, complétion, suppression, persistance. → story `001-f-liste-de-taches`
- **Candidates post-MVP** (sans ordre) : modification du texte, dates d'échéance, listes multiples, priorités.
- **À trancher hors vision** : où tourne l'app (en local sur la machine ou hébergée), à décider avec `/stack`. Par défaut, du local, cohérent avec un usage personnel au poste.

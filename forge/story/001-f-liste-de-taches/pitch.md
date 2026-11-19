# Noter, cocher et supprimer mes tâches dans une liste unique

> **But** : figer l'intention métier de la feature — ce qu'on livre et pour qui, jamais comment.
> **Registre** : fonctionnel
> **Story** : `forge/story/001-f-liste-de-taches/`
> **Amont** : aucun

La page d'accueil devient ma todolist : une liste unique où j'ajoute une tâche en tapant son texte puis Entrée, où je la coche d'un clic quand elle est faite, et où je la supprime d'un clic. Les tâches restent là d'une session de navigateur à l'autre.

## Contexte

L'application n'a aujourd'hui aucune fonction de todolist. On y trouve une page d'accueil vide, une page de démonstration du design system et un écran de connexion, tous hérités du modèle de départ. Je ne peux donc rien noter : l'outil n'existe pas encore.

Cette story correspond au MVP de la vision. Sans elle, l'hypothèse d'adoption au quotidien ne peut pas être testée, et la période de 4 semaines d'observation ne peut pas commencer. L'idée de départ était de « créer une liste pour y ajouter des éléments ». Elle a été recadrée sur une **liste unique et implicite**, sans étape de création ni nom de liste, parce que la vision réserve les listes multiples à l'après-MVP.

## Alignement vision

- **Problème adressé** : disposer de *sa propre* todolist, maîtrisée de bout en bout. Cette story est le socle sans lequel l'outil n'existe pas.
- **Audience servie** : l'utilisateur unique, au poste de travail, navigateur ouvert toute la journée.
- **Principes respectés** : « Ajouter une tâche = taper + Entrée », puisque l'ajout se fait avec un seul champ, sans autre champ ni étape de création de liste. « Rien qui ne serve pas » : seuls l'ajout, la coche et la suppression sont livrés. Pas de modification du texte, pas de date, pas de priorité.
- **Hypothèse testée** : H2 (« ajouter, cocher et supprimer suffisent pour démarrer l'usage ») directement, et H1 (adoption au quotidien) par la mise en service du MVP.
- **Impact North Star** : direct. La North Star (« jours où au moins une tâche est ajoutée ou cochée ») ne devient mesurable qu'avec cette story, à condition que chaque tâche conserve sa date de création et de complétion.

## Utilisateurs concernés

- **Moi, utilisateur unique** (au poste de travail, dans le navigateur) — je peux enfin noter, cocher et supprimer mes tâches.
- Aucun autre rôle : l'application n'a pas vocation à être partagée.

## User Stories

- En tant qu'**utilisateur**, je veux ajouter une tâche en tapant son texte puis Entrée afin de la noter sans interrompre ce que je fais.
- En tant qu'**utilisateur**, je veux enchaîner plusieurs ajouts sans reprendre la souris afin de vider ma tête d'un coup.
- En tant qu'**utilisateur**, je veux voir ma dernière tâche ajoutée juste sous le champ de saisie afin de vérifier immédiatement ce que j'ai noté.
- En tant qu'**utilisateur**, je veux cocher une tâche d'un clic afin de marquer qu'elle est faite.
- En tant qu'**utilisateur**, je veux que les tâches faites restent visibles mais passent sous les tâches à faire afin de garder en tête ce qui reste sans perdre de vue ce que j'ai accompli.
- En tant qu'**utilisateur**, je veux décocher une tâche afin de rattraper un clic par erreur.
- En tant qu'**utilisateur**, je veux supprimer une tâche d'un clic afin de retirer ce qui n'a plus lieu d'être.
- En tant qu'**utilisateur**, je veux retrouver mes tâches après avoir fermé le navigateur afin de m'y fier d'un jour à l'autre.
- En tant qu'**utilisateur**, je veux NE PAS créer de tâche vide quand j'appuie sur Entrée par erreur.

## Règles métier

1. Il existe **une seule liste**, implicite : elle n'a pas de nom et ne se crée pas, elle est là dès l'ouverture de l'application.
2. Une tâche se compose d'un **texte**, d'un **état** (à faire / faite), d'une **date de création** et, si elle est faite, d'une **date de complétion**.
3. Le texte est débarrassé de ses espaces de début et de fin. Un texte vide ou composé uniquement d'espaces n'est pas ajouté : Entrée ne fait rien, et aucun message ne s'affiche.
4. Le texte fait **255 caractères au plus**. Au-delà, la tâche n'est pas ajoutée et un message s'affiche sous le champ. Le texte saisi est conservé pour pouvoir le corriger.
5. Deux tâches peuvent avoir le même texte : pas de dédoublonnage.
6. Après un ajout réussi, le champ se vide et garde le focus.
7. **Ordre d'affichage** : les tâches à faire sont en haut, de la plus récemment créée à la plus ancienne. Les tâches faites suivent, de la plus récemment cochée à la plus ancienne.
8. Cocher une tâche la passe à l'état « faite », enregistre sa date de complétion et l'affiche barrée dans la section des tâches faites.
9. Décocher une tâche la repasse à l'état « à faire » et efface sa date de complétion. Elle reprend sa place parmi les tâches à faire selon sa date de création.
10. La suppression est **immédiate et définitive**, sans confirmation ni annulation.
11. Les tâches persistent après la fermeture du navigateur.
12. Toutes les tâches faites restent affichées : il n'y a ni purge ni archivage (voir Questions ouvertes).

## Critères d'acceptation

- [x] À l'ouverture de l'application, je vois un champ de saisie et la liste de mes tâches, sans avoir rien à créer au préalable.
- [x] Liste vide : un message indique qu'il n'y a encore aucune tâche.
- [x] Je tape un texte, j'appuie sur Entrée : la tâche apparaît en tête des tâches à faire, le champ est vidé et garde le focus.
- [x] J'enchaîne trois ajouts au clavier sans toucher la souris : les trois tâches apparaissent, la dernière en haut.
- [x] J'appuie sur Entrée avec un champ vide ou rempli d'espaces : aucune tâche n'est ajoutée et aucun message n'apparaît.
- [x] Je saisis un texte de plus de 255 caractères : la tâche n'est pas ajoutée, un message s'affiche sous le champ et mon texte reste dans le champ.
- [x] Les espaces de début et de fin d'un texte saisi n'apparaissent pas dans la tâche ajoutée.
- [x] Je coche une tâche d'un clic : elle s'affiche barrée et passe en tête des tâches faites, sous toutes les tâches à faire.
- [x] Je décoche une tâche faite : elle redevient non barrée et reprend sa place parmi les tâches à faire selon sa date de création.
- [x] Je supprime une tâche d'un clic : elle disparaît sans demande de confirmation et ne réapparaît pas après rechargement.
- [x] Je ferme le navigateur puis rouvre l'application : mes tâches sont toujours là, avec leur état.
- [x] Chaque tâche conserve sa date de création, et sa date de complétion quand elle est faite, ce qui permet de compter les jours d'usage (North Star).

## Hors scope

- **Plusieurs listes, création ou nommage d'une liste** : l'idée de départ, recadrée. Les listes multiples restent une candidate post-MVP de la vision.
- **Modifier le texte d'une tâche** : candidate post-MVP de la vision. Pour corriger, on supprime puis on ressaisit.
- **Dates d'échéance, priorités, réordonnancement manuel** : candidates post-MVP, ou non demandées.
- **Annuler une suppression, confirmation de suppression** : écartées au profit de la suppression en un clic.
- **Purge ou archivage des tâches faites** : voir Questions ouvertes.
- **Retrait de l'écran de connexion** : la vision exclut les comptes, mais la connexion héritée du modèle de départ reste en place dans cette story. Son retrait fera l'objet d'une story technique séparée.
- **Affichage des statistiques d'usage (North Star)** : la story conserve les dates nécessaires au calcul, mais ne les affiche pas.
- **Usage sur téléphone** : hors cible de la vision.

## Impacts transverses

- **Traduction / langues** : non. L'interface est en français uniquement, pour un seul utilisateur.
- **Droits d'accès** : inchangé. La liste est accessible là où l'application l'est déjà, derrière la connexion existante, en attendant la story qui la retire.
- **Cloisonnement des données** : non. Il n'y a qu'un seul utilisateur, et les tâches ne sont rattachées à personne en particulier.
- **Apparence / déclinaisons** : non. Un seul affichage, selon le design system « Paper » du projet.
- **Exposition à des tiers** : non.
- **Emails / notifications** : non.
- **Données existantes** : non, aucune tâche n'existe aujourd'hui.
- **Comportement par défaut** : la page d'accueil actuelle, vide, est remplacée par la liste.

## Questions ouvertes

- **Accumulation des tâches faites** : avec le temps, les tâches cochées s'empilent sous les tâches à faire. Options : (a) tout garder visible, (b) masquer les tâches faites depuis plus de N jours, (c) section repliable. Recommandé : (a) pour ce MVP. Le principe « rien qui ne serve pas » demande d'attendre un manque réellement observé pendant les 4 semaines de test de H2.

---

## Annexe — Pistes pour le plan

Pistes à confirmer, non contractuelles :

- Aucune entité de tâche n'existe encore. Il faudra une structure portant le texte, l'état (ou la date de complétion seule, dont la présence vaut « faite ») et la date de création. Cette structure ne doit pas être liée à `User` (mono-utilisateur, la vision exclut les comptes).
- La route `/` (`PageController::index`, `templates/page/index.html.twig`) est vide. C'est le point d'entrée naturel de la liste.
- Ajout, coche et suppression sans rechargement complet de page : Turbo (Frames/Streams) ou un Live Component, tous deux déjà installés. La conservation du focus après l'ajout est un point d'attention.
- Les composants Twig `Input` et `Checkbox` du design system existent déjà dans `templates/components/`.
- Sélecteurs `data-test` à prévoir pour les tests Playwright (champ, tâche, case à cocher, bouton de suppression, message d'état vide, message d'erreur).
- Le firewall impose `ROLE_USER` sur `^/` (`config/packages/security.yaml`) : les tests fonctionnels et E2E devront se connecter avec l'utilisateur de test tant que la story de retrait n'est pas livrée.

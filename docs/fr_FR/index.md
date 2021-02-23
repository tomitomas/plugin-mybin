# Plugin My Bin

Petit plugin pour Jeedom facilitant la gestion des poubelles domestiques

# Configuration

## Configuration du plugin

Le plugin **My Bin** ne nécessite aucune configuration spécifique et doit seulement être activé après l'installation.

Les données sont vérifiées toutes les 5 minutes.

## Configuration des équipements

Pour accéder aux différents équipements **My Bin**, dirigez-vous vers le menu **Plugins → Organisation → My Bin**.

Sur la page de l'équipement, renseignez les jours et heures de collecte de vos poublles ainsi que le moment pour être notifié.

# Utilisation

Chaque équipement cré 9 commandes:
- une pour chaque poubelle indiquant si il faut la sortir (à 1 dans ce cas)
- une commande 'ack' pour chaque poubelle remettant le statut à 0. Ces commabdes sont automatiquement appelées à l'heure de collecte
- Rrefresh : refresh quoi... :)

Un template de widget est disponible qui offre une indication visuelle (poubelle colorée), ainsi qu'un calendrier.

# Contributions

Ce plugin gratuit est ouvert à contributions (améliorations et/ou corrections). N'hésitez pas à soumettre vos pull-requests sur <a href="https://github.com/hugoKs3/plugin-mybin" target="_blank">Github</a>

# Disclaimer

-   Ce plugin ne prétend pas être exempt de bugs.
-   Ce plugin vous est fourni sans aucune garantie. Bien que peu probable, si il venait à corrompre votre installation Jeedom, l'auteur ne pourrait en être tenu pour responsable.

# ChangeLog
Disponible [ici](./changelog.html).

# Plugin My Bin

Petit plugin pour Jeedom facilitant la gestion des poubelles domestiques

# Configuration

## Configuration du plugin

Sur la page de configuration du plugin, vous pouvez choisir d'utiliser un widget global qui affichera un calendrier de rammasage de vos différentes poubelles ainsi qu'une icône pour chaque poubelle devant être sortie.

Vous pouvez également spécifier l'objet parent pour ce widget.

Les données sont vérifiées toutes les 5 minutes.

## Configuration des équipements

Pour accéder aux différents équipements **My Bin**, dirigez-vous vers le menu **Plugins → Organisation → My Bin**.

Sur la page de l'équipement, renseignez les jours et heures de collecte de votre poubelle, sa couleur, ainsi que le moment pour être notifié.

Vous pouvez également spécifier des actions qui seront exécutées au ramassage et à la notification.

Un compteur est également disponible (manuel ou automaique) ainsi qu'un seuil : une fois que le compteur atteint la valeur spécifiée dans ce paramètre, les notifications seront désactivées. 

# Utilisation

Chaque équipement crée 4 commandes :
- une indiquant si il faut sortir la poubelle (à 1 dans ce cas)
- une commande 'ack' remettant le statut à 0. Cette commabdes et automatiquement appelée à l'heure de ramassage
- une commande 'compteur' qui s'incrémente à chaque ack (en fonction de la configuration du compteur)
- une commande 'reset' pour réinitialiser le compteur

# Contributions

Ce plugin gratuit est ouvert à contributions (améliorations et/ou corrections). N'hésitez pas à soumettre vos pull-requests sur <a href="https://github.com/hugoKs3/plugin-mybin" target="_blank">Github</a>

# Disclaimer

-   Ce plugin ne prétend pas être exempt de bugs.
-   Ce plugin vous est fourni sans aucune garantie. Bien que peu probable, si il venait à corrompre votre installation Jeedom, l'auteur ne pourrait en être tenu pour responsable.

# ChangeLog
Disponible [ici](./changelog.html).

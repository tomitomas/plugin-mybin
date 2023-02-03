# Plugin My Bin

Petit plugin pour Jeedom facilitant la gestion des poubelles domestiques.

# Configuration

## Configuration du plugin

Sur la page de configuration du plugin, vous pouvez choisir d'utiliser un widget global qui affichera un calendrier de ramassage de vos différentes poubelles ainsi qu'une icône pour chaque poubelle devant être sortie.

Vous pouvez également spécifier l'objet parent pour ce widget, les éléments à afficher, et les dates à utiliser pour le calendrier (ramassage ou notification)

Les données sont vérifiées toutes les 5 minutes.

## Configuration des équipements

Pour accéder aux différents équipements **My Bin**, dirigez-vous vers le menu **Plugins → Organisation → My Bin**.

### Ramassage

Définissez les jours et heures de ramassage de votre poubelle. Plusieurs options qui peuvent se cumuler :

- En cochant les mois/semains/jours
- En spécifiant des dates précises (format Y-m-d)
- En utilisant une ou plusieurs expressions cron

### Notification

Définisez combien de jours avant chaque ramassage et à quelle heure l'etat de la commande "Poubeele à sortir" doit passer à 1.
Vous pouvez également définir une expression binaire qui sera évaluée au moment de la notification. Si vérifiée, la commande passera à 1.

### Compteur

Un compteur est également disponible (manuel ou automatique) ainsi qu'un seuil : une fois que le compteur atteint la valeur spécifiée dans ce paramètre, les notifications seront désactivées.

### Actions

Vous pouvez définir une ou plusieurs actions à exécuter après ramassage et/ou notification.

### Informations

Vous pouvez visualiser, en fonction de votre configuration, les 10 prochains dates de ramassage et de notification.
Si il y a une erreur dans votre configration, le problème sera spécifier en orange avec une information vous expliquant le problème.

## Personnalisation

En cliquant sur l'icône **Personnalisation** sur la page des équipements, vous pouvez :

- changer les icônes des types de poubelle existants
- revenir à l'icône par défaut
- créer vos propres types de poubelle

# Utilisation

Chaque équipement crée 5 commandes :

- une indiquant si il faut sortir la poubelle (à 1 dans ce cas)
- une commande 'ack' remettant le statut à 0. Cette commande est automatiquement appelée à l'heure de ramassage
- une commande 'compteur' qui s'incrémente à chaque ack (en fonction de la configuration du compteur)
- une commande 'reset' pour réinitialiser le compteur
- Une commande 'Prochain ramassage' vous indiquant la date et l'heure du prochain ramassage

# Contributions

Ce plugin gratuit est ouvert à contributions (améliorations et/ou corrections). N'hésitez pas à soumettre vos pull-requests sur <a href="https://github.com/tomitomas/plugin-mybin" target="_blank">Github</a>

# Disclaimer

- Ce plugin ne prétend pas être exempt de bugs.
- Ce plugin vous est fourni sans aucune garantie. Bien que peu probable, si il venait à corrompre votre installation Jeedom, l'auteur ne pourrait en être tenu pour responsable.

# ChangeLog

Disponible [ici](./changelog.html).

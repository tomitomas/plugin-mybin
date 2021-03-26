# Plugin My Bin

Very simple plugin for Jeedom easing the management of your home bins.

You like this plugin? You can, if you wish, encourage its developer:

[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/paypalme/hugoKs3)

# Configuration

## Plugin configuration

On the plugin's configuration page, you can choose to use a global widget whch will display a collect calendar for your different bins as well as an icon for each bin that needs to be put out.

You can also choose the parent object for this widget, the elements to display, et the dates to use for the calendar (collect or notification)

The data is checked every 5 minutes.

## Equipment configuration

To access the different equipment **My Bin**, go to the menu **Plugins → Organization → My Bin**.

### Ramassage

Deine days and time of collect for your bin. Several options that are cumulative:
- By checking months/weeks/days
- By specifying precise dates
- By using one or several cron expressions

### Notification

Define how many days before collect and at which time, the status of the "bin" command must be set to 1.
You can also define a binary expression that will be evaluated at notification time. If true, the bin command will be set to 1.

### Counter

A counter is also available (manual or automatic) with a threshold: when the counter reaches the threshold value, notifications will be suspended.

### Actions

You can define one of several actions that will be executed after notification and/or collect.

### Information

You can visualize, according to your configuration, the 10 next collect and notification dates.
If there is an error in your configuration, the proble, will be highlighted in orange with an information explaining the issue.

# Utilisation

Each equipment creates 5 comamnds:
- one indicating if the bin needs to be put out
- one to acknowledge it. This command will be automatically called at collect time
- one counter which increments itself at each ack (depending on the counter configuration)
- one action to reset counter
- one action informaing when the next collect will happen

# Contributions

This plugin is opened for contributions and even encouraged! Please submit your pull requests for improvements/fixes on <a href="https://github.com/hugoKs3/plugin-mybin" target="_blank">Github</a>

# Disclaimer

-   This code does not pretend to be bug-free
-   Although it should not harm your Jeedom system, it is provided without any warranty or liability

# ChangeLog
Available [here](./changelog.html).

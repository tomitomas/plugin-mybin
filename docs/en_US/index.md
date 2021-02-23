# Plugin My Bin

Very simple plugin for Jeedom easing the management of your home bins.

# Configuration

## Plugin configuration

The plugin **My Bin** does not require any specific configuration and should only be activated after installation.

The data is checked every 5 minutes.

## Equipment configuration

To access the different equipment **My Bin**, go to the menu **Plugins → Organization → My Bin**.

On the equipment page, fill in the collect days and times for your bins as well as how to be notified.

# Utilisation

Eqch equipment creates 9 comamnds:
- 1 for each bin indicating the ones to put out
- 1 for each bin to acknowledge it. These commands will be automatically called at collect time of concernd bin
- Refresh: well... refresh ;)

A widget's template is available that offers a visual indication (colored bin), and a calendar. A click on the icon will 'ack' the bin.

# Contributions

This plugin is opened for contributions and even encouraged! Please submit your pull requests for improvements/fixes on <a href="https://github.com/hugoKs3/plugin-mybin" target="_blank">Github</a>

# Disclaimer

-   This code does not pretend to be bug-free
-   Although it should not harm your Jeedom system, it is provided without any warranty or liability

# ChangeLog
Available [here](./changelog.html).

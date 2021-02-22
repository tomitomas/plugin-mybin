# Plugin My Bin

Very silpe plugin for Jeedom easing the management of your home bins.

# Configuration

## Plugin configuration

The plugin **My Bin** does not require any specific configuration and should only be activated after installation.

The data is checked every 5 minutes.

## Equipment configuration

To access the different equipment **My Bin**, go to the menu **Plugins → Organization → My Bin**.

On the equipment page, fill in the collect days and times for your bins as well as how to be notified.

# Utilisation

Eqch equipment creates 5 comamnds:
- Yellow bin & Green bin : 1 if one of the bin needs to be put out
- Ack: Puts both commands above to 0. This command is automatically called at collect time.
- Refresh: well... refresh ;)
- Global status : 'N' if no bin to put out, 'Y' if yellow bin to put out, 'G' if green bin to put out, 'B' if both bins to put out.

A widget's template is available that offers a visual indication (colored bin).

# Contributions

This plugin is opened for contributions and even encouraged! Please submit your pull requests for improvements/fixes on <a href="https://github.com/hugoKs3/plugin-mybin" target="_blank">Github</a>

# Disclaimer

-   This code does not pretend to be bug-free
-   Although it should not harm your Jeedom system, it is provided without any warranty or liability

# ChangeLog
Available [here](./changelog.html).

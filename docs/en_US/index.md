# Plugin My Bin

Very simple plugin for Jeedom easing the management of your home bins.

# Configuration

## Plugin configuration

On the plugin's configuration page, you can choose to use a global widget whch will display a collect calendar for your different bins as well as an icon for each bin that needs to be put out.

You cqn also choose the parent object for this widget.

The data is checked every 5 minutes.

## Equipment configuration

To access the different equipment **My Bin**, go to the menu **Plugins → Organization → My Bin**.

On the equipment page, fill in the collect days and times for your bin, its color, as well as when to be notified.

You can also specify commands that will be executed at collect and notification times.

# Utilisation

Each equipment creates 3 comamnds:
- one indicating if the bin needs to be put out
- one to acknowledge it. This command will be automatically called at collect time
- one counter which increments itself at each ack (manual or automatic)

# Contributions

This plugin is opened for contributions and even encouraged! Please submit your pull requests for improvements/fixes on <a href="https://github.com/hugoKs3/plugin-mybin" target="_blank">Github</a>

# Disclaimer

-   This code does not pretend to be bug-free
-   Although it should not harm your Jeedom system, it is provided without any warranty or liability

# ChangeLog
Available [here](./changelog.html).

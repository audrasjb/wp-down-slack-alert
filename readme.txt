=== WP Down Slack Alert ===
Contributors: whodunitagency, audrasjb, leprincenoir
Tags: Slack, alert, notification, recovery, recovery mode, downtime, crash, break
Requires at least: 5.2
Tested up to: 5.3
Stable tag: 0.2
Requires PHP: 5.6
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Connect WordPress Recovery Mode to a your Slack team to receive alerts when websites are down.

== Description ==

**This plugin is meant to send automatic notifications on the channel of your choice in your Slack Team.**

Wether you manage hundred of websites or only a single one, it’s always good to know when they are down, so you can step in as quick as possible.

**WP Down Slack Alert** provides a dedicated settings screen where you are able to set up your Slack channel configuration and to create a customized bot (name, avatar…) for your Slack notifications.

This plugin is based on WordPress Core Recovery Mode. The Slack alert is triggered when your websites goes into Recovery Mode and send you a Slack Notification with details about the issue.

== Screenshots ==
1. Settings screen.
2. Slack notification example.

== Installation ==

1. Install the plugin and activate.
2. Go to Tools > WP Down Slack Alert settings.
3. See our FAQ below or follow the instruction in the settings page to configure your Slack token.

== Frequently Asked Questions ==

= How to set up the connexion to my Slack Team? =

Go to Tools > WP Down Slack Alert and follow the tutorial to get your Slack API token and customize your Slack notification bot:

To set up your Slack app, you'll need to get a Slack Bot token:

1. Go to this page: https://api.slack.com/apps?new_app=1 and provide a name for your App, choose a Slack workspace and click on "Create App" button.
2. In the "Features and functionality" section, click on the "Bots" panel.
3. That will lead you to the "Bot user" screen. Click on "Add a Bot User" button.
4. Leave the default names (you will be able to override that in the plugin’s settings), and click "Add bot user" button.
5. Click on the "Install App" menu item in the navigation sidebar, then click on the "Install App to Workspace" button.
6. Allow this Slack App to access your Slack team: click on the "Allow" button.
7. Copy/paste the **Bot User OAuth Access Token** in the plugin’s settings field.

== Changelog ==

= 0.2 =
* Better internationalization and tutorial integration.

= 0.1 =
* Plugin initial commit. Works fine :)
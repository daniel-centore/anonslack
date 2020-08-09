# Anonymous Slack Bot with Admin Logging
## Intro
This is a Slack bot which allows users to send messages anonymously while also logging those messages to a private admin-only group for moderation purposes

This is what it looks like in the chat:

[![image.png](https://i.postimg.cc/MG9D9dTJ/image.png)](https://postimg.cc/3yDmRjmt)

This is what the message log channel looks like:

[![image.png](https://i.postimg.cc/C1d4XkBB/image.png)](https://postimg.cc/LYKfZqV2)


## Slack App Configuration
* Create a new Slack app at https://api.slack.com/apps in your workspace
* Fill out the display information you would like in the "Basic Information" tab
  * [![image.png](https://i.postimg.cc/qR92FYHS/image.png)](https://postimg.cc/LY3qYxX3)
* In the "Slash Commands" tab
  * "Create New Command"
  * Fill it out with a command of your choice (we personally make two - `/a` and `/anon` - but you can use anything you like), your webserver domain + `/index.php`, and a description
  * Make sure to tick "Escape channels, users, and links sent to your app"! This will make it correctly linkify channels, usernames, and links in the echoed message instead of them being plaintext.
  * [![image.png](https://i.postimg.cc/3w81jP3Z/image.png)](https://postimg.cc/tscFpvZ1)
* Add the required permissions in the "OAuth & Permissions" tab
  * channels:read
  * chat:write
  * chat:write:public
  * commands
  * groups:read
  * groups:write
  * users:read
* Click "Install App" or "Reinstall App" at the top of the "OAuth & Permissions" tab
  * Save the "Bot User OAuth Access Token" - you will need this in the next section


## Server Configuration
* Copy contents of `src` directory to the root of your PHP webserver (tested with PHP 7.3)
* Copy `settings.php.example` and rename the copy to `settings.php`
  * In `settings.php`, fill in the "Bot User OAuth Access Token" from the previous section
* Run `composer update`
* The Slack slash command(s) should now be working

## User Guide
* To send an anonymous message, just use the slash command you specified earlier along with the message, e.g. `/a This is a message!`
* To send in a private channel, the bot needs to be added to the private channel first. Do this by @ mentioning the bot by whatever name you chose for it (e.g. `@Anonymous`) and hitting enter. You will then get an invite dialog.
  * [![image.png](https://i.postimg.cc/Tw8wvXhV/image.png)](https://postimg.cc/zLj5T6Cv)
  * [![image.png](https://i.postimg.cc/fbdzKg5J/image.png)](https://postimg.cc/qtkHBwPT)
* Every time a message is sent with the bot, it will show up in a private group chat called "anon-message-log".
  * All Slack admins are added to this chat every time someone sends a message
  * You can also add additional people to this log manually by simply inviting them

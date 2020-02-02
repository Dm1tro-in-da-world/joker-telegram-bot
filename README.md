Joker Telegram Bot 
=================

Born in 2001'th this bot was entertaiment chatbot written in miRCscript, joking on #blackcrystal Quakenet channel. 

Since that time many things has been changed. Here is third rewrite of Joker, made for Telegram to be modern and simple, with PHP to be fun.

Currently this bot is used in telegram channel @bc_dev

Installation and start
-------------------------------------------------

Install required software packages

```
# (Ubuntu, Debian)
sudo apt-get install php-cli php-curl php-json git composer
```


Clone repository and install dependencies.

```
git clone https://github.com/miamibc/joker-telegram-bot.git
cd joker-telegram-bot
composer install
cp .env.sample .env
```

To run Telegram bot, you must be [registered in BotFather](https://core.telegram.org/bots#6-botfather) 
and have working API token. Insert this token into **joker.php**
or **.env** file before start. Now all is ready to launch.

```
php joker.php
```

Find your bot in Telegram by name and say him **Hi**. 

Plugins
-------

By default, bot does nothing, plugins are used to extend it's functionality and interact with users.  We made few classic plugins for Joker, like [HelloPlugin](https://github.com/miamibc/joker-telegram-bot/blob/master/src/HelloPlugin.php) and more complex one [TempPlugin](https://github.com/miamibc/joker-telegram-bot/blob/master/src/TempPlugin.php), feel free to use them and add new. 

To add plugin, create new class extending `Joker\Plugin` and connect it with `$joker->plug( array )` command before main loop. Add methods like `on[Action][Action]( Joker\Event $event )`. These methods will be called when all actions is found in request. Actions can be:

- `message` - requests containing message section
- `sticker` - stickers or replies with sticker
- `text` - contains text
- `public` - public requests
- `private` - non-public requests
- `group` - group, supergroup and channel requests
- `empty` - empty requests

For example, `onPrivateSticker` or `onStickerPrivate` will be called when both `sticker` and `private` is found in request.

Parameter of this method is used get message details and react. For example `$event->answerMessage()` or `$event->answerSticker()` is a shortcut to answer same channel or private user, by message or sticker. 

Return value of plugin method can be:

- `Joker\Bot::PLUGIN_NEXT` or `true` or `null`- (default) process next plugin in chain
- `Joker\Bot::PLUGIN_BREAK` or `false` - do not process plugin chain anymore.

I'll be glad to see your plugins and help with implementations.

Project pages
-------------

* https://github.com/miamibc/joker-telegram-bot
* https://blackcrystal.net/project/joker/

Contact
-------

* miami at blackcrystal dot net
* https://blackcrystal.net
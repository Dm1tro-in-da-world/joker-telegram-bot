<?php
/**
 * @package joker-telegram-bot
 * @author Sergei Miami <miami@blackcrystal.net>
 */

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(dirname(__FILE__));
$dotenv->load();

$token    = getenv('TELEGRAM_TOKEN');

$bot = new Joker\Bot( $token );
$bot->plug([
  new Joker\LogPlugin(['file'=>'log/log.json']),
  new Joker\QuotePlugin(['dir'=>'jokes']),
  new Joker\ModeratePlugin(),
  new Joker\HelloPlugin(),
  new Joker\StickerPlugin(),
]);

do { $bot->loop(); } while(true);
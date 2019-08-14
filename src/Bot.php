<?php

/**
 * Joker the Telegram bot
 *
 * Born in 2001'th this bot was entertaiment chatbot made in miRCscript,
 * joking on channel #blackcrystal in Quakenet. Since that year many things
 * has been changed. Here's third rewrite of Joker on PHP and Telegram API.
 *
 * @package joker-telegram-bot
 * @author Sergei Miami <miami@blackcrystal.net>
 *
 * @property  Plugin $plugin
 */

namespace Joker;

class Bot
{

  const PLUGIN_NEXT   = 100500;
  const PLUGIN_BREAK  = 100501;

  private
    $debug = false,
    $ch = null,
    $client  = null,
    $token = null,
    $buffer = [],
    $last_update_id = 0,
    $plugins = [];

  public function __construct( $token, $debug = false )
  {
    $this->token = $token;
    $this->debug = $debug;
    $this->ch = curl_init();
  }

  /**
   * @param $method
   * @param $data
   *
   * @return array|bool
   * @throws Exception
   */
  private function _request($method,$data = [])
  {
    curl_setopt_array($this->ch, [
      CURLOPT_URL => $url = "https://api.telegram.org/bot{$this->token}/{$method}",
      CURLOPT_RETURNTRANSFER => true,         // return web page
      CURLOPT_HEADER         => false,        // don't return headers
      CURLOPT_FOLLOWLOCATION => true,         // follow redirects
      CURLOPT_USERAGENT      => "joker_the_bot",     // who am i
      CURLOPT_AUTOREFERER    => true,         // set referer on redirect
      CURLOPT_CONNECTTIMEOUT => 120,          // timeout on connect
      CURLOPT_TIMEOUT        => 120,          // timeout on response
      CURLOPT_MAXREDIRS      => 10,           // stop after 10 redirects
      CURLOPT_POST           => true,         // i am sending post data
      CURLOPT_POSTFIELDS     => $plain_request = json_encode($data),    // this are my post vars
      CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Connection: Keep-Alive',
      ],
      // CURLOPT_SSL_VERIFYHOST => false,      // don't verify ssl
      // CURLOPT_SSL_VERIFYPEER => false,      //
      // CURLOPT_VERBOSE        => 1           //
    ]);

    $plain_response = curl_exec($this->ch);
    $result = json_decode( $plain_response, true);
    $this->log( $method . ' '. $plain_request . ' => ' . $plain_response );

    if (!isset($result['ok']) || !$result['ok'])
      throw new Exception("Something went wrong");

    return isset($result['result']) ? $result['result'] : false;
  }

  public function loop()
  {

    // request new updates
    try { $this->requestUpdates(); }
    catch ( Exception $exception){ $this->log($exception); }

    $event = new Event( $this, array_shift($this->buffer) );
    try { $this->processEvent( $event ); }
    catch ( Exception $exception)   { $this->log($exception); }

    $event = null;
    unset($event);

    // sleep a bit
    $time = count($this->buffer) ? 2 : 4;
    sleep($time);
  }

  /**
   * @throws Exception
   */
  private function requestUpdates()
  {
    foreach ($this->_request("getUpdates", ['offset' =>$this->last_update_id]) as $item)
    {
      $this->buffer[] = $item;
      $this->last_update_id = $item['update_id']+1;
    }
  }

  public function sendMessage( $chat_id, $text)
  {
    $result = $this->_request("sendMessage", ["chat_id" =>$chat_id,"text" =>$text] );
    return $result;
  }

  public function sendSticker( $chat_id, $file_id)
  {
    $result = $this->_request("sendSticker", ["chat_id" =>$chat_id,"sticker" =>$file_id] );
    return $result;
  }

  public function deleteMessage( $chat_id, $message_id)
  {
    $result = $this->_request("deleteMessage", ["chat_id" =>$chat_id,"message_id" =>$message_id] );
    return $result;
  }

  public function customRequest( $method, $data )
  {
    return $this->_request( $method, $data );
  }

  private function processEvent(Event $event )
  {
    // get message parameters
    $tags = $event->getTags();

    $this->log(['tags'=>$tags]);

    // checks to perform for each plugin
    $checks = [
      'onPublicSticker'  => $tags['public']  && $tags['sticker'],
      'onPrivateSticker' => $tags['private'] && $tags['sticker'],
      'onPublicText'     => $tags['public']  && $tags['text'],
      'onPrivateText'    => $tags['private'] && $tags['text'],
      'onSticker'        => $tags['sticker'],
      'onText'           => $tags['text'],
      'onMessage'        => $tags['message'],
      'onEmpty'          => $tags['empty'],
      'onTimer'          => true,
      'onAnything'       => true,
    ];
    $result = null;
    foreach ( $this->plugins as $plugin )
    {
      foreach ( $checks as $method => $check )
      {
        if ($check && method_exists($plugin,$method))
          $result = call_user_func( [$plugin,$method], $event );

        if     ($result === Bot::PLUGIN_NEXT)  { break 1; }
        elseif ($result === Bot::PLUGIN_BREAK) { break 2; }
        elseif ($result === false) { break 2; }
      }
    }
  }

  public function log( $message )
  {
    if ($this->debug)
    {
      $timestamp = date("Y-m-d H:i:s");
      $json = is_string($message) ? $message : json_encode($message);
      echo "\n[$timestamp] $json";
    }
    return $message;
  }

  /**
   * @param Plugin[] $plugins
   * @return $this
   */
  public function plug( array $plugins )
  {
    $this->plugins = $plugins;
    return $this;
  }

}
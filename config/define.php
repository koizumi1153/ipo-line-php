<?php
return [
  //////////
  /// LINE BOT用共通定数
  //////////

  // api url
  define('LINE_API_URL', 'https://api.line.me/v2/bot/message/reply'),
  define('LINE_API_PUSH_URL', 'https://api.line.me/v2/bot/message/push'), // 単一ユーザーPUSH
  define('LINE_API_MULTI_URL','https://api.line.me/v2/bot/message/multicast'), // 複数ユーザーPUSH
  define('LINE_MULTI_USER', '150'), //複数ユーザーID数
  define('LINE_MESSAGE_COUNT', '5'), //メッセージ送信可能数
  define('LINE_MESSAGE_LENGTH', 998), //メッセージ長さ

  // action
  define('ACTION_POST_BACK',  'postback'),        // ポストバックアクション
  define('ACTION_MESSAGE',    'message'),         // メッセージアクション
  define('ACTION_URI',        'uri'),             // URIアクション
  define('ACTION_DATE_TIME',  'datetimepicker'),  // 日時選択アクション

  //postback time
  define('SELECT_DATE','date'),         // 例：2017-06-18
  define('SELECT_TIME','time'),         // 例：00:00
  define('SELECT_DATETIME','datetime'), // 例：2017-06-18T06:15

  //postback.data
  define('POSTBACK_SELECT_PUSH_TIME','select_time'),  //push時間

  define('WORDS',      999), //その他
  define('PUSH',      1000), //push通知可否変更
  define('PUSHON',    1001), //push通知ON
  define('PUSHOFF',   1002), //push通知OFF
  define('PUSHTIME',  1003), //push通知時間変更

  //priority
  define('PRIORITY_DEFAULT', 0), // 全て
  define('PRIORITY_BEFORE',  1), // 前
  define('PRIORITY_AFTER',   2), // 後ろ

  // 共通フラグ 0 or 1
  define('OFF_FLG', 0),
  define('ON_FLG',  1),


  //////////
  /// IPO用定数
  //////////
  // アクセストークン
  define('IPO_ACCESS_TOKEN', 'L/TXWHveBPBMa1/KasKUE8eAvLptEO14MRJyH5fMMqH7tq1fe18NQXOkXwTJHZTBkhJ6JbOeUAiG3DNMk5SzUYsYR5P/PUVxJ1li/lt1eRtrJR4MrIEaOb1Jm/E1aybsN7g8wOqfXWnjgVzrFGrRRAdB04t89/1O/w1cDnyilFU='),
  // ipo api url
  define('IPO_API_URL', "http://ipo-cal.appspot.com/api/ipo?page=0"),
  // スケジュール取得用url
  define('IPO_SCHUDULE_BASE_URL', "https://kabusyo.com/ipo/"),

  //twitter access
  define('IPO_TWITTER_CONSUMER_KEY','nbAerZBGS9CNy354bw9aEnnJ6'),
  define('IPO_TWITTER_CONSUMER_SECRET','oq3o7iVHO6NaA5BXbYnOSZ9WSRlGBCalYhrZPS0tuDXjZNnFUD'),
  define('IPO_TWITTER_ACCESS_TOKEN','981465922341822465-11y6a219JIfiyPqXChN9Zfa5Ao92ik8'),
  define('IPO_TWITTER_ACCESS_TOKEN_SECRET', 'MvGS3iYvfHiZzTiBNJKgwm1eUu3g7cDK7YAb4SiS2ImFI'),

];//defien

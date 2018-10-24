# yii2-stream-log


Yii2 module, provide a cli to send log to elasticsearch asynchronously use redis as a local buffer

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```sh
php composer.phar require --prefer-dist "macfly/yii2-stream-log" "*"
```

or add

```json
"macfly/yii2-stream-log": "*"
```

to the require section of your `composer.json` file.

Configure
------------

Configure **config/console.php** and **config/web.php** as follows

```php
  'bootstrap' => [
      'log',
      'streamlog',
  ],
  'modules' => [
     ................
     'streamlog' => [
         'class' => 'macfly\streamlog\Module',
         'redisTarget' => [
             'exportInterval' => 1,
             'logVars'        => [],
             'logUser'        => true,
             'logApp'         => true,
             'logTracker'     => true,
             'logUserIp'      => true,
             'logSession'     => true,
             'userNameAt'     => 'username',
         ],
         'elasticsearchTarget' => [
            'indexDateFormat' => 'y-MM-dd', // Set date to append to index, will be yii-2018-10-24
        ],
    ],
    ................
  ],
```

Usage
------------

Run the following to enable log streaming from redis to elasticsearch

```sh
php yii streamlog/sender/start
```

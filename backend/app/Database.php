<?php

namespace App;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
  public static function connect()
  {
    $capsule = new Capsule;
    $capsule->addConnection([
      'driver'    => 'mysql',
      'host'      => $_ENV['DB_HOST'],
      'database'  => $_ENV['DB_DATABASE'],
      'username'  => $_ENV['DB_USERNAME'],
      'password'  => $_ENV['DB_PASSWORD'],
      'charset'   => $_ENV['DB_CHARSET'],
      'collation' => $_ENV['DB_COLLATION'],
    ]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
  }
}

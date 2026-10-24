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
      'host'      => '127.0.0.1',
      'database'  => 'modulo_licitaciones',
      'username'  => 'user_licitaciones',
      'password'  => 'modulo123',
      'charset'   => 'utf8mb4',
      'collation' => 'utf8mb4_unicode_ci',
    ]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
  }
}

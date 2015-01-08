<?php

return array(

  /*
  |--------------------------------------------------------------------------
  | Database Connections
  |--------------------------------------------------------------------------
  |
  | Here are each of the database connections setup for your application.
  | Of course, examples of configuring each database platform that is
  | supported by Laravel is shown below to make development simple.
  |
  |
  | All database work in Laravel is done through the PHP PDO facilities
  | so make sure you have the driver for your particular database of
  | choice installed on your machine before you begin development.
  |
  */

  'connections' => array(

    'mysql' => array(
      'driver'    => 'mysql',
      'host'      => 'localhost',
      'database'  => 'homestead',
      'username'  => 'homestead',
      'password'  => 'secret',
      'charset'   => 'utf8',
      'collation' => 'utf8_unicode_ci',
      'prefix'    => '',
    ),

    'pgsql' => array(
      'driver'   => 'pgsql',
      'host'     => 'localhost',
      'database' => 'homestead',
      'username' => 'homestead',
      'password' => 'secret',
      'charset'  => 'utf8',
      'prefix'   => '',
      'schema'   => 'public',
    ),

    'mongodb' => array(
      'driver'   => 'mongodb',
      'host'     => getenv('DB_HOST') ? getenv('DB_HOST') : 'localhost',
      'port'     => getenv('DB_PORT') ? getenv('DB_PORT') : 27017,
      'username' => getenv('DB_USERNAME') ? getenv('DB_USERNAME') : '',
      'password' => getenv('DB_PASSWORD') ? getenv('DB_PASSWORD') : '',
      'database' => getenv('DB_NAME') ? getenv('DB_NAME') : 'userapi',
    ),

  ),

);

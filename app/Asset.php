<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 18/08/2021
 * Time: 00:24
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'asset';
    // 90147414
}

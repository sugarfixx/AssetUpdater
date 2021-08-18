<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 18/08/2021
 * Time: 07:13
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $connection = 'mysql';

    protected $casts = [
        'item' => 'array'
    ];

}

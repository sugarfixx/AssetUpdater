<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 27/09/2021
 * Time: 08:56
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class ReindexFixxer extends Model
{
    protected $table = 'reindex_fixer';

    protected $connection = 'mysql';

    protected $casts = [
        'metadata' => 'array',
        'item' => 'array'
    ];
}

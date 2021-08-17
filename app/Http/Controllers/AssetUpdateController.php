<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 18/08/2021
 * Time: 00:27
 */

namespace App\Http\Controllers;


use App\Asset;

class AssetUpdateController extends Controller
{
    protected $companyId =1324004;

    // select count(assetid) from asset where assetcompany_id = 1324004;
    public function getCount()
    {
        $assets = Asset::where('assetcompany_id', $this->companyId)->get();
        var_dump(count($assets));
    }
    public function update()
    {

    }
}

<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 18/08/2021
 * Time: 00:27
 */

namespace App\Http\Controllers;


use App\Asset;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class AssetUpdateController extends Controller
{
    protected $companyId =1324004;
    protected $jwtToken = 'Bearer';
    protected $mapUrl = 'https://map-api-eu1.mediabank.me/asset/';

    // select count(assetid) from asset where assetcompany_id = 1324004;
    public function getCount()
    {
        try {
            DB::connection('pgsql')->getPdo();
        } catch (\Exception $e) {
            die("Could not connect to the database.  Please check your configuration. error:" . $e );
        }
        // $assets = DB::table('asset')->where('assetcompany_id', $this->companyId)->count();
        $assets = Asset::on('pgsql')->where('assetcompany_id', $this->companyId)->get();
        return count($assets);
    }




    public function update()
    {
        $assets = Asset::where('assetcompany_id', $this->companyId)->get();

    }

    public function callMapApi($assetId)
    {
        $client = new Client();
        $client->put($this->mapUrl, [
            'headers' => [
                'Authorization' => $this->jwtToken,
                'MAP-Application'  => 'library'
            ],
            'body' => [
                'assetid' => $assetId,
            ],
            'allow_redirects' => false,
            'timeout'         => 5
        ]);
    }
}

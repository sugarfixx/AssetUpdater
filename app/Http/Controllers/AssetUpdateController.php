<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 18/08/2021
 * Time: 00:27
 */

namespace App\Http\Controllers;


use App\Asset;
use App\Queue;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class AssetUpdateController extends Controller
{
    protected $companyId = 1324004;
    protected $jwtToken = 'Bearer';
    protected $mapUrl = 'https://map-api-eu1.mediabank.me/asset/';

    // select count(assetid) from asset where assetcompany_id = 1324004;
    public function getCount()
    {
        try {
            DB::connection('pgsql')->getPdo();
        } catch (\Exception $e) {
            die("Could not connect to the database.  Please check your configuration. error:" . $e);
        }
        // $assets = DB::table('asset')->where('assetcompany_id', $this->companyId)->count();
        $assets = Asset::on('pgsql')->where('assetcompany_id', $this->companyId)->get();
        return count($assets);
    }


    public function buildQueue()
    {
        $assets = Asset::on('pgsql')->where('assetcompany_id', $this->companyId)->limit(2)->pluck('assetid');
        $processed = 0;
        foreach ($assets as $asset) {
            $message = json_encode(['assetId' => $asset]);
            $queue = new Queue();
            $queue->item = $message;
            $queue->done = false;
            if ($queue->save()) {
                $processed++;
            }
        }

        return response()->json(['message' => $processed . ' items added to queue']);
    }

    public function viewQueue()
    {
        return response()->json(Queue::where('done', false)->get());
    }


    public function runQueue()
    {
        $queue = Queue::where('done', 'false')->limit(500);


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

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
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetUpdateController extends Controller
{
    protected $companyId = 1324004;
    protected $jwtToken = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtZWRpYWJhbmsubWUiLCJhdWQiOiJtZWRpYWJhbmsubWUiLCJpYXQiOjE2MjkzMjAzMTgsIm5iZiI6MTYyOTMyMDMxOCwiZXhwIjoxNjI5MzIzOTE4LCJ1c2VySWQiOjcxNDEwMDEsInRhZ3NBcGlUb2tlbiI6bnVsbCwiYXBpVG9rZW4iOiJabVUxWmpabE16aGtaREE1TkRnek5HWXhZalJoWTJRME4yVTNZakkxT0RjME5qSXgiLCJ1c2VyIjp7InVzZXJpZCI6NzE0MTAwMSwidXNlcm5hbWUiOiJpdG9yc3J1ZEBuZXBncm91cC5jb20iLCJmdWxsbmFtZSI6IkluZ2FyIFRvcnNydWQgKE1lZGlhYmFuaykiLCJwaG9uZSI6bnVsbCwiZW1haWwiOiJpdG9yc3J1ZEBuZXBncm91cC5jb20iLCJjb21wYW55X2lkIjoiODY0MDA0IiwiY29tcGFueV9uYW1lIjoiREFaTiIsInRhZ3NfYXBpX3Rva2VuIjpudWxsLCJyb2xlcyI6WzEsMTAwMCwxMDA0LDEwMDYsMzUsMzYsMzNdLCJyZWdpb24iOiJldS1oaWx2ZXJzdW0tMSJ9fQ.UeXQWYjS7oD-e02pK-Vaft3c7Qa2QlCRLZQJRNrtf7A';
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
        $assets = Asset::on('pgsql')->where('assetcompany_id', $this->companyId)->get()->pluck('assetid');
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
        $queue = Queue::where('done', 'false')->take(500)->get();
        $i = 0;
        foreach ($queue as $entry)  {
            $i++;
            $item = json_decode($entry->item);
            $assetId = $item->assetId;
            if ($this->callMapApi($assetId) !== false) {
                $message = 'Success: Asset ' . $assetId .' updated through map api';
            } else {
                $message = 'Error: Asset ' . $assetId .' failed to be updated through map api';
            }
            Log::info($message);
        }
        return response()->json(['message' => $i . ' items processed']);
    }

    public function callMapApi($assetId)
    {
        $client = new Client();
        $request = [
            'headers' => [
                'Authorization' => $this->jwtToken,
                'MAP-Application'  => 'library'
            ],
            'form_params' => [
                'id' => $assetId,
                'assetmeta' => json_encode(['updatedBy' => 'AssetUpdater']),
                'notify' => 0
            ]
        ];

        try {
            $response = $client->request('PUT', $this->mapUrl, $request);
            if ($response->getStatusCode()== 200 ) {
                return true;
            } else {
                return false;
            }
        } catch (ClientException $e) {
            Log::info($e->getMessage());
        } catch (ServerException $e ) {
            Log::info($e->getMessage());
        } catch (\Exception $e) {
            Log::info($e->getMessage());
        }
    }
}

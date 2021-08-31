<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 18/08/2021
 * Time: 00:27
 */

namespace App\Http\Controllers;


use App\Asset;
use App\Library\SportRadarIntegrationClient;
use App\Queue;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use http\Env\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetUpdateController extends Controller
{
    protected $companyId = 1324004;
    protected $jwtToken = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtZWRpYWJhbmsubWUiLCJhdWQiOiJtZWRpYWJhbmsubWUiLCJpYXQiOjE2MjkzMjAzMTgsIm5iZiI6MTYyOTMyMDMxOCwiZXhwIjoxNjI5MzIzOTE4LCJ1c2VySWQiOjcxNDEwMDEsInRhZ3NBcGlUb2tlbiI6bnVsbCwiYXBpVG9rZW4iOiJabVUxWmpabE16aGtaREE1TkRnek5HWXhZalJoWTJRME4yVTNZakkxT0RjME5qSXgiLCJ1c2VyIjp7InVzZXJpZCI6NzE0MTAwMSwidXNlcm5hbWUiOiJpdG9yc3J1ZEBuZXBncm91cC5jb20iLCJmdWxsbmFtZSI6IkluZ2FyIFRvcnNydWQgKE1lZGlhYmFuaykiLCJwaG9uZSI6bnVsbCwiZW1haWwiOiJpdG9yc3J1ZEBuZXBncm91cC5jb20iLCJjb21wYW55X2lkIjoiODY0MDA0IiwiY29tcGFueV9uYW1lIjoiREFaTiIsInRhZ3NfYXBpX3Rva2VuIjpudWxsLCJyb2xlcyI6WzEsMTAwMCwxMDA0LDEwMDYsMzUsMzYsMzNdLCJyZWdpb24iOiJldS1oaWx2ZXJzdW0tMSJ9fQ.UeXQWYjS7oD-e02pK-Vaft3c7Qa2QlCRLZQJRNrtf7A';
    protected $baseUri = 'https://map-api-eu1.mediabank.me/';
    protected $resourceUrl = 'asset/';

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
        $assets = Asset::on('pgsql')
            ->select('assetid', 'assetmeta')
            ->where('assetcompany_id', $this->companyId)
            ->get();


        $processed = 0;
        foreach ($assets as $asset) {
            $metadata = $this->jsonFromHstore($asset->assetmeta);
            $item = json_encode([
                'assetId' => $asset->assetid,
                'metadata' => $metadata
            ]);
            $queue = new Queue();
            $queue->item = $item;
            $queue->done = false;
            if ($queue->save()) {
                $processed++;
            }
        }

        return response()->json(['message' => $processed . ' items added to queue']);
    }

    public function viewQueue()
    {
        return response()->json(Queue::where('done', false)->first());
    }

    public function deleteQueue()
    {
        $deleted = 0;
        $queue = Queue::where('done', false)->all();
        foreach ($queue as $q) {
            $q->delete();
            $deleted++;
        }
        return \response()->json(['message' => $deleted . ' items was deleted from queue']);

    }


    public function runQueue()
    {
        $queue = Queue::where('done', 'false')->take(500)->get();
        $i = 0;
        foreach ($queue as $entry)  {
            $i++;
            $time_start = microtime(true);
            $item = json_decode($entry->item);
            $assetId = $item->assetId;
            $assetMeta = $this->jsonFromHstore($item->metadata);
            $integrationService = new SportRadarIntegrationClient();
            $metadata = $integrationService->getMetadata($assetMeta);
            if ($this->callMapApi($assetId, $metadata) !== false) {
                $time_end = microtime(true);
                $entry->done = true;
                if ($entry->save()) {
                    $message = 'Success: Asset ' . $assetId .' updated through map api. TIME: ' . ($time_end - $time_start) . ' sec';
                } else {
                    $message = 'Success with incident: Asset ' . $assetId .' updated through map api but Queue done field update failed. TIME: ' . ($time_end - $time_start) . ' sec';
                }

            } else {
                $time_end = microtime(true);
                $message = 'Error: Asset ' . $assetId .' failed to be updated through map api. TIME: ' . ($time_end - $time_start) . ' sec';
            }
            Log::info($message);
        }
        return response()->json(['message' => $i . ' items processed']);
    }

    public function callMapApi($assetId, $metadata)
    {
        $client = new Client(['base_uri' => $this->baseUri]);
        $request = [
            'headers' => [
                'Authorization' => $this->jwtToken,
                'MAP-Application'  => 'library'
            ],
            'form_params' => [
                'id' => $assetId,
                'metadata' => json_encode($metadata),
                'notify' => 0
            ]
        ];

        try {
            $response = $client->request('PUT', $this->resourceUrl, $request);
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

    public function jsonFromHstore($data)
    {
        preg_match_all('/(?:"((?:\\\\"|[^"])+)"|(\w+))\s*=>\s*(?:"((?:\\\\"|[^"])*)"|(NULL))/ms',
            $data, $matches, PREG_SET_ORDER);
        $hstore = array();

        foreach ($matches as $set) {

            $key = $set[1] ? $set[1] : $set[2];
            $val = (array_key_exists(4, $set) &&  $set[4]=='NULL') ? null : $set[3];
            $hstore[$key] = $val;
        }
        return json_encode($hstore);
         //return json_decode('{' . str_replace('"=>"', '":"', $hstore) . '}');
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 31/08/2021
 * Time: 21:24
 */

namespace App\Library;

use Illuminate\Support\Facades\Log;
use App\Queue;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class AssetUpdater
{
    protected $companyId = 1324004;
    protected $jwtToken = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtZWRpYWJhbmsubWUiLCJhdWQiOiJtZWRpYWJhbmsubWUiLCJpYXQiOjE2MzA0MzAwODAsIm5iZiI6MTYzMDQzMDA4MCwiZXhwIjoxNjMwNDMzNjgyLCJ1c2VySWQiOjcxNDEwMDEsInRhZ3NBcGlUb2tlbiI6bnVsbCwiYXBpVG9rZW4iOiJNVEUxWXpCaU9XVmpOamMwT0dJNFptTTFOV1JsTkdGbE9EVXlOelJsT0RRelpESXciLCJ1c2VyIjp7InVzZXJpZCI6NzE0MTAwMSwidXNlcm5hbWUiOiJpdG9yc3J1ZEBuZXBncm91cC5jb20iLCJmdWxsbmFtZSI6IkluZ2FyIFRvcnNydWQgKE1lZGlhYmFuaykiLCJwaG9uZSI6bnVsbCwiZW1haWwiOiJpdG9yc3J1ZEBuZXBncm91cC5jb20iLCJjb21wYW55X2lkIjoiODY0MDA0IiwiY29tcGFueV9uYW1lIjoiREFaTiIsInRhZ3NfYXBpX3Rva2VuIjpudWxsLCJyb2xlcyI6WzEsMTAwMCwxMDA0LDEwMDYsMzUsMzYsMzNdLCJyZWdpb24iOiJldS1oaWx2ZXJzdW0tMSJ9fQ.x116qmr2IQP4FmegGHyylh-bZ6ZKjjGffKKvxypKz1s';
    protected $baseUri = 'https://map-api-eu1.mediabank.me/';
    protected $resourceUrl = 'asset/';

    public function runQueue()
    {
        $queue = Queue::where('done', 'false')->take(20)->get();
        $i = 0;

        foreach ($queue as $entry)  {
            $i++;
            $time_start = microtime(true);
            $item = json_decode($entry->item);
            $assetId = $item->assetId;

            $integrationService = new SportRadarIntegrationClient();
            $metadata = $integrationService->getMetadata($item->metadata);

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
        return response()->json([
            'number' => $i,
            'message' => $i . ' items processed']);
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
    }

}

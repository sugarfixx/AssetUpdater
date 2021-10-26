<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 24/09/2021
 * Time: 07:50
 */

namespace App\Http\Controllers;



use App\Queue;
use App\ReindexFixxer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;

class QueueController extends Controller
{
    protected $companyId = 1324004;
    protected $jwtToken = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtZWRpYWJhbmsubWUiLCJhdWQiOiJtZWRpYWJhbmsubWUiLCJpYXQiOjE2MzUyMjY0OTYsIm5iZiI6MTYzNTIyNjQ5NiwiZXhwIjoxNjM1MjMyMTY1LCJ1c2VySWQiOiI3MTQxMDAxIiwidGFnc0FwaVRva2VuIjoieWYzQWswN21UR3A3YktcL25hNmt2I1JRNUFcL0w5NFBQRVhkTWFSbGNLOCIsImFwaVRva2VuIjoiT0RJd1pXTTJNekU0TVRFNE56UTFaakkwWlRVd05qVTBNRGs0WW1WaE56TmlOVFEzIiwidXNlciI6eyJ1c2VyaWQiOiI3MTQxMDAxIiwidXNlcm5hbWUiOiJpbmdhciIsImZ1bGxuYW1lIjoiSW5nYXIgVG9yc3J1ZCAoTWVkaWFiYW5rKSIsInBob25lIjpudWxsLCJlbWFpbCI6Iml0b3JzcnVkQG5lcGdyb3VwLmNvbSIsImNvbXBhbnlfaWQiOiIzMDA2IiwiY29tcGFueV9uYW1lIjoiTkVQIE1lZGlhIFNlcnZpY2VzIiwidGFnc19hcGlfdG9rZW4iOiJ5ZjNBazA3bVRHcDdiS1wvbmE2a3YjUlE1QVwvTDk0UFBFWGRNYVJsY0s4Iiwicm9sZXMiOlsxLDEwMDAsMTAwNCwxMDA2LDc5LDIsMyw0LDUsNiw3LDI5LDMwLDMyLDMzLDM0LDM1LDM2LDM4LDM5LDQwLDQxLDQ4LDQ5LDUwLDUxLDUyLDUzLDU0LDU1XSwicmVnaW9uIjoiZXUtbm9yd2F5LTEifX0.m6aP5eftKI7ltuJBRIJ_OxsiW_OQqGQ1PjtEUAkxVzk';
    protected $baseUri = 'https://map-api-eu1.mediabank.me/';
    protected $resourceUrl = 'asset/';

    protected $assetIDS = [
        10102499004,
        10102452004,
        10102358004,
        10102356004,
        10102309004,
        10102306004,
        10097843004,
        10097786004,
        10096905004,
        10096874004
    ];

    protected $noExtId = [
        10102452004,
        10102358004,
        10102358004,
        10102306004,
        10097843004,
        10097786004,
        10096905004,
        10096874004
    ];

    public function reindexWithModifiedMetadata()
    {
        // these are untouched
        // $matches = ReindexFixxer::whereIn('asset_id', $this->noExtId)->get();
        // return response()->json($matches);

        // test one asset id 10102499004
        // $call = $this->callMapApi(10102499004, ['ExternalMatchId' => '']);
        // var_dump($call);
        // exit;

        // Loop through and set correct value
        // foreach ($this->noExtId as $assetId) {
        //     $this->callMapApi($assetId, ['ExternalMatchId' => '']);
        // }
        // echo "Done";

        // final one 10102356004
        $final = ReindexFixxer::where('asset_id',10102356004)->first();
        $metadata = $final->metadata;
        $metadata['ExternalMatchId'] = '';
        $this->callMapApi($final->asset_id, $metadata);
        // var_dump($metadata);

        return response()->json($metadata);

    }

    public function findInItem()
    {

        // $searchPhrase = "27110156";
        // $searchPhrase = 'BG PATHUM UNITED,VIETTEL FC';
        $searchPhrase = (string) $this->assetIDS[0];
        $result = [];
        //$matches = Queue::where('item', 'LIKE', "%{$searchPhrase}%")->offset(100)->limit(10)->get();
        // $matches = Queue::where('item', 'LIKE', "%metadata%")->where('item', 'LIKE', "%{$searchPhrase}%")->get();
        $matches = Queue::where('item', 'LIKE', "%{$searchPhrase}%")->get();
        // var_dump($matches); exit;



        foreach ($matches as $m) {
            $item = json_decode($m->item);
            if (isset($item->metadata)) {
                $result[] = json_decode($item->metadata);
            }

        }
        return response()->json($result);
        $filtered =[];
        foreach ($result as $assetId) {
            $entries = Queue::where('item', 'LIKE', "%{$assetId}%")->get();
            $items =[];
            foreach ($entries as $e) {
                $item = json_decode($e->item);

                if (property_exists($item, 'metadata') &&
                    $this->hasSearchPhrase($item, $searchPhrase) !== true)
                {
                    //var_dump($item); exit;
                    // var_dump($this->hasSearchPhrase($item, $searchPhrase)); exit;
                    $items = $e->id;
                    $title = json_decode($item->metadata);
                }
                /*
                if (property_exists($item, 'metadata'))
                {
                    if (strpos($item->metadata, $searchPhrase) !== false) {
                        $isFaulty = true;
                    } else {
                        // $items[] = ['queueId' => $e->id];
                        $items = $e->id;
                        $title = json_decode($item->metadata);

                    }
                }
                else {
                    $isFaulty = 'no-metadata';
                }
                */


            }
            $filtered[] = [
                'assetID' => $assetId,
                'queue_id' => $items,
                'metadata' => $title,
            ];
            /*
            $fix = new ReindexFixxer();
            $fix->queue_id = $items;
            $fix->asset_id = $assetId;
            $fix->item = $item;
            $fix->metadata = $title;
            $fix->done = false;
            if ($fix->save()) {
                $message ='queueId' . $items . 'added';
            } else {
                $message ='queueId' . $items . 'failed to be added';
            }
            Log::info($message);
            */
        }

        //var_dump(count($matches)); exit;
        return response()->json($filtered);
        // $queueEntry = Queue::find(20149);
        // $queueEntry = Queue::find(35470);
        // var_dump($queueEntry->item); exit;
        // $item = json_decode($queueEntry->item);
        // return response()->json(json_decode($item->metadata));

    }

    public function findInItemNew()
    {
        $searchPhrase = "27110156";
        $result = [];
        // first we found all that was indexed with wrong data
        // $matches = Queue::where('done', false)->where('item', 'LIKE', "%{$searchPhrase}%")->offset(0)->limit(100)->get();

        // then we need to find the 1235 that was not re-indexed that does NOT include the wrong data
        $matches = Queue::where('done', false)->where('item', 'NOT LIKE', "%{$searchPhrase}%")->offset(1200)->limit(100)->get();

        foreach ($matches as $m) {
            $item = json_decode($m->item);
            $result[] = $item->assetId;
        }
        $filtered =[];
        foreach ($result as $assetId) {
            $entries = Queue::where('item', 'LIKE', "%{$assetId}%")->get();
            foreach ($entries as $e) {
                $item = json_decode($e->item);

                if (property_exists($item, 'metadata') &&
                    $this->hasSearchPhrase($item, $searchPhrase) !== true)
                {
                    $array = [
                        'assetID' => $assetId,
                        'queueID' => $e->id,
                        'metadata' =>json_decode($item->metadata),
                        'item' => $item
                    ];
                    $this->addToReindex($array);
                    $filtered[] = $array;
                }
            }
        }


        return response()->json($filtered);


    }

    public function addToReindex($array)
    {

        $fix = new ReindexFixxer();
        $fix->queue_id = $array['queueID'];
        $fix->asset_id = $array['assetID'];
        $fix->item = $array['item'];
        $fix->metadata = $array['metadata'];
        $fix->done = false;
        if ($fix->save()) {
            $message ='queueId' . $array['queueID'] . 'added';
        } else {
            $message ='queueId' . $array['queueID'] . 'failed to be added';
        }
        Log::info($message);
    }

    public function runReIndexer()
    {
        $queue = ReindexFixxer::where('done', false)->take(100)->get();

        $i = 0;
        $success = 0;
        foreach ($queue as $q) {
            $i++;
            Log::info('Updating asset with id: ' . $q->asset_id. ' using MAP API');
            $apiCall = $this->callMapApi($q->asset_id, $q->metadata);
            if ($apiCall) {
                $q->done = true;
                if ($q->save()) {
                    $success++;
                    Log::info('Successfully Updated asset with id: ' . $q->asset_id. ' in MAP');
                }
            }
        }

        return response()->json([
            'number' => $i,
            'message' => $success . ' items processed successfully']);

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
    public function hasSearchPhrase($item, $searchPhrase)
    {
        return strpos($item->metadata, $searchPhrase) !== false;
    }
}



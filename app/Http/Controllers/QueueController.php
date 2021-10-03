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
    protected $jwtToken = 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJtZWRpYWJhbmsubWUiLCJhdWQiOiJtZWRpYWJhbmsubWUiLCJpYXQiOjE2MzMyNjc0OTEsIm5iZiI6MTYzMzI2NzQ5MSwiZXhwIjoxNjMzMjcxMDkzLCJ1c2VySWQiOjcxNDEwMDEsInRhZ3NBcGlUb2tlbiI6bnVsbCwiYXBpVG9rZW4iOiJOR1F5WlRGalkyRTJPVEkwTXpSbE1ERmpZbVkzTXpKa01XUmxNemszTkdVd1pqTTEiLCJ1c2VyIjp7InVzZXJpZCI6NzE0MTAwMSwidXNlcm5hbWUiOiJpdG9yc3J1ZEBuZXBncm91cC5jb20iLCJmdWxsbmFtZSI6IkluZ2FyIFRvcnNydWQgKE1lZGlhYmFuaykiLCJwaG9uZSI6bnVsbCwiZW1haWwiOiJpdG9yc3J1ZEBuZXBncm91cC5jb20iLCJjb21wYW55X2lkIjoiMTMyNDAwNCIsImNvbXBhbnlfbmFtZSI6IlNwb3J0cmFkYXIiLCJ0YWdzX2FwaV90b2tlbiI6bnVsbCwicm9sZXMiOlsxLDEwMDAsMTAwNCwxMDA2LDIsNjksNzYsOTBdLCJyZWdpb24iOiJldS1ub3J3YXktMSJ9fQ.VYqo3QXxkYKVKBu5mOcWBqoHYR4lFdnVBXVlDjg8ET4';
    protected $baseUri = 'https://map-api-eu1.mediabank.me/';
    protected $resourceUrl = 'asset/';

    public function findInItem()
    {

        $searchPhrase = "27110156";
        // $searchPhrase = 'BG PATHUM UNITED,VIETTEL FC';
        $result = [];
        $matches = Queue::where('item', 'LIKE', "%{$searchPhrase}%")->offset(100)->limit(10)->get();
        // $matches = Queue::where('item', 'LIKE', "%metadata%")->where('item', 'LIKE', "%{$searchPhrase}%")->get();
        // var_dump(count($matches)); exit;
        foreach ($matches as $m) {
            $item = json_decode($m->item);
            $result[] = $item->assetId;
        }
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
        $matches = Queue::where('item', 'LIKE', "%{$searchPhrase}%")->offset(1400)->limit(100)->get();
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
        $queue = ReindexFixxer::where('done', false)->take(2)->get();

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



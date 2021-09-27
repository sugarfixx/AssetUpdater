<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 24/09/2021
 * Time: 07:50
 */

namespace App\Http\Controllers;


use App\Queue;

class QueueController extends Controller
{
    public function findInItem()
    {

        $searchPhrase = "27110156";
        // $searchPhrase = 'BG PATHUM UNITED,VIETTEL FC';
        $result = [];
        // $matches = Queue::where('item', 'LIKE', "%{$searchPhrase}%")->offset(100)->limit(10)->get();
        $matches = Queue::where('item', 'LIKE', "%metadata%")->where('item', 'LIKE', "%{$searchPhrase}%")->get();
        var_dump(count($matches)); exit;
        foreach ($matches as $m) {
            $item = json_decode($m->item);
            /*
            $result [$item->assetId][] = [
                'id' => $m->id,
                'asset_id' => $item->assetId,
                'created_at' => $m->created_at
            ]; */
            $result[] = $item->assetId;
        }
        $filtered =[];
        foreach ($result as $assetId) {
            $entries = Queue::where('item', 'LIKE', "%{$assetId}%")->get();
            $items =[];
            foreach ($entries as $e) {
                $item = json_decode($e->item);

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



            }
            $filtered[] = [
                'assetID' => $assetId,
                'queue_id' => $items,
                'metadata' => $title
            ];
        }

        //var_dump(count($matches)); exit;
        return response()->json($filtered);
        // $queueEntry = Queue::find(20149);
        // $queueEntry = Queue::find(35470);
        // var_dump($queueEntry->item); exit;
        // $item = json_decode($queueEntry->item);
        // return response()->json(json_decode($item->metadata));

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



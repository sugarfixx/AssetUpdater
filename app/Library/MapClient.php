<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 20/08/2021
 * Time: 14:08
 */

namespace App\Library;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
class MapClient
{
    protected $baseUri = 'https://map-api-eu1.mediabank.me/';
    protected $resourceUrl = 'asset/';

    public function assetUpdate($assetId, $metadata,$bearerToken)
    {

        $client = new Client(['base_uri' => $this->baseUri]);
        $request = [
            'headers' => [
                'Authorization' => $bearerToken,
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
}

<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 20/08/2021
 * Time: 14:09
 */

namespace App\Library;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;

class SportRadarIntegrationClient
{
    public function getMetadata($assetMeta)
    {
        $client = new Client(['base_uri' => $this->baseUrl]);
        $request = [
            'headers' => [
                'accept' => 'accept: application/json',
                'Content-Type'=> 'application/json'
            ],
            'json' => [
                json_encode($assetMeta)
            ]
        ];
        try {
            $response = $client->request('POST', 'login', $request);
            if ($response->getStatusCode()== 200 ) {
                $body = $response->getBody();
                if ($body)  {
                    return $body;
                }

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

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

use GuzzleHttp\Psr7\Utils;
use Illuminate\Support\Facades\Log;

class SportRadarIntegrationClient
{
    private $baseUrl = 'http://mb-sportradar-integration.vpc2.mnw.no/';

    public function getMetadata($assetMeta)
    {
        $client = new Client();
        $request = ['body' => $assetMeta];
        try {
            $response = $client->request('POST',$this->baseUrl . 'metadata', $request);
            if ($response->getStatusCode()== 200 ) {
                return json_decode((string) $response->getBody());
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

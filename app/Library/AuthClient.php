<?php
/**
 * Created by PhpStorm.
 * User: sugarfixx
 * Date: 20/08/2021
 * Time: 14:10
 */

namespace App\Library;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;

class AuthClient
{
    private $username = null;
    private $password = null;

    protected $baseUrl = null;

    public function __construct()
    {
        $this->baseUrl = env('JWT_ENDPOINT');
        $this->username = env('JWT_USERNAME');
        $this->password = env('JWT_PASSWORD');
    }

    public function getJwtToken()
    {
        $client = new Client(['base_uri' => $this->baseUrl]);
        $request = [
            'headers' => [

            ],
            'form_params' => [
                'email' => $this->username,
                'password' => $this->password
            ]
        ];
        try {
            $response = $client->request('POST', 'login', $request);
            if ($response->getStatusCode()== 200 ) {
                $body = $response->getBody();
                if ($body && isset($body->token))  {
                    return 'Bearer ' .  $body->token;
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

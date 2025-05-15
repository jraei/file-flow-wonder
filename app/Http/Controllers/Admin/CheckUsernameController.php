<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use GuzzleHttp\Client;
use Illuminate\Http\Response;

class CheckUsernameController extends Controller
{
    /**
     * Get the account username from the validation provider
     * 
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    public function getAccountUsername(array $data)
    {
        $provider = Provider::where('provider_name', 'checkUsername')->first();

        if (!$provider) {
            return response()->json([
                'status' => 'error',
                'message' => 'Username checking provider not configured'
            ], 404);
        }

        $api_key = $provider->api_key;
        $base_url = $provider->base_url;

        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $base_url,
        ]);

        // Try catch
        try {
            $params = [
                'api_key' => $api_key,
                'game' => $data['game'],
                'user_id' => $data['user_id'],
            ];
            if (isset($data['zone_id'])) {
                $params['zone_id'] = $data['zone_id'];
            }

            $response = $client->request('POST', '', [
                'form_params' => $params,

            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            // check if status success
            if ($responseData['status'] != 'success') {
                return response()->json([
                    'status' => 'error',
                    'message' => $responseData['message'] ?? 'Account validation failed'
                ], Response::HTTP_BAD_REQUEST);
            }

            $username = $responseData['result']['username'] ?? null;

            return response()->json(['status' => 'success', 'username' => $username]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Network or connection errors
            $errorMessage = 'Connection error while validating account. Please try again.';
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $errorData = json_decode($response->getBody()->getContents(), true);
                $errorMessage = $errorData['message'] ?? $errorMessage;
            }

            return response()->json([
                'status' => 'error',
                'message' => $errorMessage
            ], Response::HTTP_BAD_GATEWAY);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

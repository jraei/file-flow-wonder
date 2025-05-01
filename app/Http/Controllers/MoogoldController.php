<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use GuzzleHttp\Client;
use App\Models\Layanan;
use App\Models\Kategori;
use App\Models\Provider;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Medboubazine\Moogold\Moogold;
use Medboubazine\Moogold\Auth\Credentials;

class MoogoldController extends Controller
{
    protected $credentials;
    public function __construct()
    {
        $provider = Provider::where('provider_name', 'moogold')->first();
        $user_id = $provider->api_username;
        $partner_id = $provider->api_key;
        $secret_key = $provider->api_private_key;

        $this->credentials = new Credentials($user_id, $partner_id, $secret_key);
    }

    public function getMoogoldBalance()
    {
        try {
            $moogold =  new Moogold($this->credentials);
            $user = $moogold->user();
            $balance = $user->balance();

            return collect([
                'status' => true,
                'data' => $balance->getAmount()
            ]);
            // return $balance->getAmount();
        } catch (\Exception $e) {
            logger()->error("Failed to get balance: " . $e->getMessage());
            return  collect([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getMoogoldProducts()
    {
        $provider = Provider::where('provider_name', 'moogold')->first();

        if (!$provider || $provider->status != 'active') {
            return [
                'status' => false,
                'message' => 'Provider not found or inactive!'
            ];
        }
        // List kategori dan ID nya (statis)
        $categories = Kategori::where('provider_id', $provider->id)->where('status', 'active')->get();

        try {
            $moogold = new Moogold($this->credentials);
            $products = $moogold->products();
        } catch (\Exception $e) {
            logger()->error("Failed to connect in getMoogoldProducts functions at MoogoldController: " . $e->getMessage());
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }


        $affectedRows = [
            'success' => 0,
            'failed' => 0
        ];
        // Loop semua kategori
        foreach ($categories as $category) {
            $categoryCode = $category->kode_kategori;
            $categoryName = $category->kategori_name;
            $categoryId = $category->id;
            try {
                // Call API Moogold per kategori
                $response = $products->list($categoryCode);
                // // Tambahkan hasil ke array utama
                if (!empty($response)) {
                    foreach ($response as $product) {
                        // dd($product->getName());
                        $productName = $product->getName();

                        // Skip produk yang mengandung karakter Chinese/non-Latin
                        if (
                            preg_match('/[\p{Han}]/u', $productName) || // Deteksi karakter Chinese
                            preg_match('/[^\x00-\x7F]/', $productName)
                        ) { // Deteksi karakter non-ASCII
                            continue; // Lewati produk ini
                        }

                        Produk::updateOrCreate(
                            ['provider_id' => $provider->id, 'brand' => $product->getId()],
                            [
                                "nama" => $product->getName(),
                                "developer" => 'Unknown Developer',
                                "kategori_id" => $categoryId,
                                "slug" => Str::slug($product->getName(), '-') ?? null,
                                "status" => 'active',
                            ]
                        );
                        $affectedRows['success']++;
                    }
                }
            } catch (\Exception $e) {
                // Kalau ada error ambil 1 kategori, log errornya tapi lanjut loop
                logger()->error("Failed to fetch products for category {$categoryName}: " . $e->getMessage());
                $affectedRows['failed']++;
            }
        }

        return $affectedRows;
    }

    public function getMoogoldServices()
    {
        $provider = Provider::where('provider_name', 'moogold')->first();
        if (!$provider || $provider->status != 'active') {
            return [
                'status' => false,
                'message' => 'Provider not found or inactive!'
            ];
        }
        $moogold = new Moogold($this->credentials);
        $moogoldProducts = $moogold->products();
        $activeProducts = Produk::where('provider_id', $provider->id)->where('status', 'active')->get();
        $affectedRows = [
            'success' => 0,
            'failed' => 0
        ];

        foreach ($activeProducts as $produk) {
            try {
                $response = $moogoldProducts->details($produk['brand']);

                $productName = $response->getName();

                if (!empty($response)) {
                    $services = $response->getOffers();

                    foreach ($services as $data) {

                        $dataId = $data->getId();
                        $price = $data->getPrice();

                        if (empty($dataId) || !is_scalar($dataId)) {
                            logger()->error("Invalid ID: " . json_encode($dataId));
                            continue; // Lewati layanan ini
                        }

                        if (!is_numeric($price)) {
                            logger()->error("Invalid Price for ID $dataId: " . json_encode($price));
                            continue;
                        }
                        $serviceName = $data->getName();

                        $idTag = "(#{$dataId})";
                        if (strpos($serviceName, $idTag) !== false) {
                            $serviceName = str_replace($idTag, '', $serviceName);
                            $serviceName = trim($serviceName);
                        }


                        // Remove product name from service name
                        $productNameClean = trim($productName);
                        if (stripos($serviceName, $productNameClean) === 0) {
                            $serviceName = trim(str_replace($productNameClean, '', $serviceName));
                            $serviceName = preg_replace('/^\s*-\s*/', '', $serviceName); // lebih kuat buat bersihin dash + spasi
                        }

                        // Safety check biar gak kosong
                        if (empty($serviceName)) {
                            $serviceName = $data->getName(); // fallback ke nama original aja kalau kosong
                        }


                        // =====================================

                        Layanan::updateOrCreate(
                            ['kode_layanan' => $dataId],
                            [
                                "produk_id" => $produk->id,
                                "provider_id" =>  $provider->id,
                                "nama_layanan" => $serviceName, // Pakai nama yang sudah difilter
                                "harga_beli" => $price,
                                "status" => 'active'
                            ]
                        );
                        $affectedRows['success']++;
                    }
                }
            } catch (\Exception $e) {
                logger()->error("Failed to fetch layanan for products {$produk->nama}: " . $e->getMessage());
                $affectedRows['failed']++;
            }
        }
        return $affectedRows;
    }

    public function createTransaction(array $data)
    {
        try {
            $result = $this->createTransactionRequest(
                $data['category_id'],
                $data['order_id'],
                $data['service_id'],
                $data['quantity'],
                $data['user_id'] ?? null,
                $data['server'] ?? null
            );

            return collect([
                'status' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            logger()->error("Failed to create transaction: " . $e->getMessage());
            return collect([
                'status' => false,
                'data' => $e->getMessage()
            ]);
        }
    }



    public function createTransactionRequest(int $categoryType, string $externalId, int $offerId, int $quantity, ?string $user_id = null, ?string $server = null)
    {
        // Setup info dasar
        $timestamp = time(); // Unix timestamp
        $path = "order/create_order"; // Endpoint path dari Moogold
        $baseUri = "https://moogold.com/wp-json/v1/api/";

        // Credentials (replace ini sesuai object atau .env kamu)
        $partnerId = $this->credentials->partner_id;
        $secretKey = $this->credentials->secret_key;

        // Bangun body untuk API
        $data = [
            "category" => $categoryType,
            "product-id" => $offerId,
            "quantity" => $quantity,
        ];
        // Tambahkan jika ada
        if ($user_id !== null) {
            $data["User ID"] = $user_id;
        }
        if ($server !== null) {
            $data["Server ID"] = $server;
        }

        $bodyArray = [
            "path" => $path,
            "data" => $data,
            "partnerOrderId" => $externalId,
        ];

        $body = json_encode($bodyArray);
        // Signature
        $signature = hash_hmac('SHA256', "{$body}{$timestamp}{$path}", $secretKey);

        // Headers
        $headers = [
            "Accept" => "application/json",
            "timestamp" => $timestamp,
            "auth" => $signature,
            "Authorization" => "Basic " . base64_encode("{$partnerId}:{$secretKey}"),
        ];

        // Guzzle request
        $client = new Client([
            "base_uri" => $baseUri,
            "timeout"  => 10,
            "allow_redirects" => false,
            "http_errors" => false,
            "verify" => true,
            "headers" => $headers,
        ]);

        try {
            $response = $client->request("POST", $path, [
                "body" => $body
            ]);

            if ($response->getStatusCode() === 200) {
                $contents = $response->getBody();
                $data = json_decode($contents, true);

                if (isset($data['status']) && isset($data['order_id'])) {
                    return [
                        'status' => true,
                        'order_id' => $data['order_id'],
                        'response' => $data,
                    ];
                } else {
                    return [
                        'status' => false,
                        'message' => 'Invalid response structure',
                        'raw' => $data,
                    ];
                }
            } else {
                return [
                    'status' => false,
                    'message' => "HTTP Error: " . $response->getStatusCode(),
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => "Exception: " . $e->getMessage(),
            ];
        }
    }
}
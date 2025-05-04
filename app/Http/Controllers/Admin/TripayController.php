<?php

namespace App\Http\Controllers\Admin;

use App\Models\PayMethod;
use ZerosDev\TriPay\Client;
use Illuminate\Http\Request;
use ZerosDev\TriPay\Merchant;
use App\Models\PaymentProvider;
use ZerosDev\TriPay\Transaction;
use App\Http\Controllers\Controller;
use ZerosDev\TriPay\Support\Helper;

class TripayController extends Controller
{
    protected $config;
    public function __construct()
    {
        $provider = PaymentProvider::where('provider_name', 'Tripay')->first();

        $this->config =  [
            "mode" => 'development',
            "merchant_code" => $provider['kode_merchant'],
            "api_key" => $provider['api_key'],
            "private_key" => $provider['private_key'],
            "guzzle_options" => []
        ];
    }
    public function getTripayMethod()
    {
        $provider = PaymentProvider::where('provider_name', 'Tripay')->first();
        $client = new Client($this->config);
        $merchant = new Merchant($client);
        $result = $merchant->paymentChannels();

        $data = json_decode($result->getBody()->getContents(), true)['data'];

        // affected
        $affectedRow = 0;

        foreach ($data as $method) {
            $method = collect($method);

            // Ambil nilai fee flat dan percent dari API
            $fixed = $method['total_fee']['flat'];
            $percent = (float) $method['total_fee']['percent'];

            if ($fixed > 0 && $percent == 0) {
                $feeType = 'fixed';
            } else if ($fixed == 0 && $percent > 0) {
                $feeType = 'percent';
            } else if ($fixed > 0 && $percent > 0) {
                $feeType = 'mixed';
            } else {
                $feeType = 'none';
            }

            // update or Create
            PayMethod::updateOrCreate(
                ['kode' => $method['code'], 'payment_provider' => 'Tripay'],
                [
                    "nama" => $method['name'],
                    "kode" => $method['code'],
                    "tipe" => $method['group'],
                    "payment_provider" => $provider['provider_name'],
                    "gambar" => $method['icon_url'],
                    "keterangan" => $method['name'],
                    "fee_fixed" => $fixed,
                    "fee_percent" => $percent,
                    "fee_type" => $feeType,
                    "status" => $method['active'] ? 'active' : 'inactive'
                ]
            );
            $affectedRow++;
        }
        return $affectedRow;
    }

    public function createTransaction(array $data)
    {
        $client = new Client($this->config);
        $transaction = new Transaction($client);

        $result = $transaction->addOrderItem(
            $data['item'],
            $data['price'],
            $data['quantity']
        )
            ->create([
                'method' => $data['method'],
                'merchant_ref' => $data['merchant_ref'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'],
                'expired_time' => Helper::makeTimestamp('1 DAY')
            ]);

        $response = json_decode($result->getBody()->getContents(), true);
        return $response;
    }


    public function getTransactionDetail($reference)
    {
        $client = new Client($this->config);
        $transaction = new Transaction($client);
        $result = $transaction->detail($reference);
        $data = json_decode($result->getBody()->getContents(), true);
        return $data;
    }
}

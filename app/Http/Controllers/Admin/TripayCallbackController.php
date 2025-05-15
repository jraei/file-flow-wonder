<?php

namespace App\Http\Controllers\Admin;


use Exception;
use App\Models\Voucher;
use App\Models\Pembelian;
use App\Models\Pembayaran;
use ZerosDev\TriPay\Client;
use Illuminate\Http\Request;
use App\Models\FlashsaleItem;
use ZerosDev\TriPay\Callback;
use App\Models\PaymentProvider;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MoogoldController;

class TripayCallbackController extends Controller
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

    public function handle()
    {
        $client = new Client($this->config);
        $callback = new Callback($client);

        $callback->enableDebug();
        try {
            $callback->validate();
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }

        $data = $callback->data();

        $tripayReference = $data->reference;
        $invoice = Pembayaran::where('payment_reference', $tripayReference)
            ->first();

        $order_id = $invoice->order_id;
        $dataPembelian = Pembelian::where('order_id', $order_id)->first();
        $dataLayanan = $dataPembelian->layanan;
        $dataProduk = $dataLayanan->produk;

        $uid = $dataPembelian->input_id ?? null;
        $zone = $dataPembelian->input_zone ?? null;
        $kode_layanan = $dataLayanan->kode_layanan;
        $target = $uid . $zone;


        if (!$invoice || $invoice->status != 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Invoice not found or status not pending',
            ]);
        }

        if (!$dataPembelian || $dataPembelian->status != 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found or status not pending',
            ]);
        }

        if (intval($data->total_amount) !== (int) $invoice->total_price) {
            return response()->json([
                'status' => 'error',
                'message' => 'Total amount not match',
            ]);
        }

        $status = null;
        $reference = null;


        if ($data->status == "PAID") {

            $invoice->update([
                'status' => 'paid'
            ]);


            if ($dataLayanan->provider->provider_name == "digiflazz") {
                $digiflazz = new DigiflazzController();
                $order = $digiflazz->createTransaction([
                    'kode_layanan' => $kode_layanan,
                    'target' => $target,
                    'ref_id' => $order_id
                ]);

                if ($order['data']['status'] == "Pending" || $order['data']['status'] == "Sukses") {
                    $status = "completed";
                    $reference = $order_id;
                } else {
                    $status = "failed";
                }
            } else if ($dataLayanan->provider->provider_name == "moogold") {
                $moogoldOrderCategory = $dataProduk->moogold_order_category;

                $moogold = new MoogoldController;
                $order = $moogold->createTransaction([
                    'category_id' => $moogoldOrderCategory,
                    'order_id' => $order_id,
                    'service_id' => $kode_layanan,
                    'quantity' => $dataPembelian->quantity,
                    'user_id' => $uid,
                    'server' => $zone
                ]);



                if (!$order['data']['status']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $order['data']['raw']['err_message'] ?? $order['data']['message']
                    ]);
                }

                $data = $order['data'];
                if ($data['response']['status'] == "pending" || $data['response']['status'] == "processing") {
                    $status = "completed";
                    $reference = $data['order_id'];
                } else {
                    $status = "failed";
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Provider not found',
                ]);
            }

            if ($status) {

                $dataPembelian->update([
                    'status' => $status,
                    'reference_id' => $reference ?? null
                ]);

                return response()->json([
                    'status' => 'success',
                    'data' => 'Sukses mengubah status pembelian & pembayaran',
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Status tidak valid',
                ]);
            }
        } else if ($data->status == "EXPIRED" || $data->status == "FAILED") {

            // restore voucher stock
            if ($dataPembelian->voucher_id) {
                $voucher = Voucher::find($dataPembelian->voucher_id);
                if ($voucher) {
                    $voucher->update([
                        'usage_count' => $voucher->usage_count - 1
                    ]);
                }
            }

            // restore flashsale stock
            if ($dataPembelian->flashsale_item_id) {
                $flashsaleItem = FlashsaleItem::find($dataPembelian->flashsale_item_id);
                if ($flashsaleItem) {
                    if ($flashsaleItem->stok_tersedia != null) {
                        $flashsaleItem->update([
                            'stok_terjual' => $flashsaleItem->stok_terjual - $dataPembelian->quantity
                        ]);
                    }

                    if ($flashsaleItem->stok_tersedia != null) {
                        $flashsaleItem->update([
                            'stok_tersedia' => $flashsaleItem->stok_tersedia + $dataPembelian->quantity
                        ]);
                    }
                }
            }



            $invoice->update(['status' => 'failed']);
            return response()->json([
                'status' => 'error',
                'message' => 'Payment failed',
            ]);
        } else {

            return response()->json([
                'status' => 'error',
                'message' => 'Status tidak valid',
            ]);
        }



        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Produk;
use App\Models\Layanan;
use App\Models\Kategori;
use App\Models\Provider;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Gonon\Digiflazz\Digiflazz;
use Gonon\Digiflazz\PriceList;
use App\Http\Controllers\Controller;
use Gonon\Digiflazz\Balance;
use Gonon\Digiflazz\Exceptions\ApiException;
use Gonon\Digiflazz\Transaction;

class DigiflazzController extends Controller
{
    public function __construct()
    {
        $digiflazz = Provider::where('provider_name', 'digiflazz')->first();
        Digiflazz::initDigiflazz($digiflazz->api_username, $digiflazz->api_key);
    }

    public function getDigiflazzBalance()
    {
        try {
            $balance = Balance::getBalance();

            return collect([
                'status' => true,
                'data' => $balance->deposit
            ]);
        } catch (\Exception $e) {
            logger()->error("Failed to get balance: " . $e->getMessage());
            return  collect([
                'status' => false,
                'message' => $e->getMessage()
            ]);
            // return $balance->deposit;
        }
    }

    public function createTransaction(array $data)
    {
        // try catch
        try {
            $params = [
                'buyer_sku_code' => $data['kode_layanan'],
                'customer_no' => $data['target'],
                'ref_id' => $data['ref_id'],
                'testing' => $data['testing'] ?? false
            ];
            $result = Transaction::createTransaction($params);

            return $result;
        } catch (ApiException $e) {
            logger()->error("Failed to create transaction: " . $e->getMessage());
            return collect([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getDigiflazzService()
    {
        $res = PriceList::getPrePaid();

        $digiflazz = Provider::where('provider_name', 'digiflazz')->first();
        $affectedRows = 0;
        $activeProducts = Produk::where('provider_id', $digiflazz->id)->where('status', 'active')->get();

        foreach ($activeProducts as $produk) {
            foreach ($res as $data) {
                $data = collect($data);

                $produk = collect($produk);
                if (Str::upper($data['brand']) == Str::upper($produk['reference'])) {

                    $layananExist = Layanan::where('kode_layanan', $data['buyer_sku_code'])->first();
                    $params = [
                        "produk_id" => $produk['id'],
                        "provider_id" =>  $digiflazz->id,
                        "kode_layanan" => $data['buyer_sku_code'],
                        "nama_layanan" => $data['product_name'],
                        "harga_beli" => $data['price'],
                        "status" => ($data['seller_product_status'] === true ? "active" : "inactive")
                    ];

                    // cek layanan sudah ada apa belum
                    if (!$layananExist) {
                        // masukkan data ke dalam db
                        Layanan::create($params);
                        $affectedRows++; // Increment saat insert

                    } else {
                        // jika ada layanan sebelumnya, update harganya
                        $layananExist->update([
                            'harga_beli' => $data['price'],
                            'status' => ($data['seller_product_status'] === true ? "active" : "inactive")
                        ]);
                        $affectedRows++; // Increment saat update

                    }
                }
            }
        }
        return $affectedRows; // Kembalikan jumlah row yang terpengaruh
    }

    public function getDigiflazzProduk()
    {
        $provider = Provider::where('provider_name', 'digiflazz')->first();

        if (!$provider) {
            return back()->with('status', ['type' => 'error', 'action' => 'Request Error', 'text' => 'Provider not found!']);
        }

        $res = \Gonon\Digiflazz\PriceList::getPrePaid(); // Produk prabayar dari API

        $arrGame = [];
        $arrPulsa = [];

        foreach ($res as $item) {
            if ($item->category === 'Games') {
                $arrGame[] = $item->brand;
            } elseif ($item->category === 'Pulsa') {
                $arrPulsa[] = $item->brand;
            }
        }

        // Developer mapping
        $developerList = [
            "Mobile Legends" => "Moonton",
            "Mobile Legends: Bang Bang" => "Moonton",
            "Free Fire" => "Garena",
            "PUBG Mobile" => "Tencent",
            "PUBG" => "Tencent",
            "Call of Duty Mobile" => "Activision",
            "Call of Duty" => "Activision",
            "Fortnite" => "Epic Games",
            "Valorant" => "Riot Games",
            "Clash of Clans" => "Supercell",
            "Clash Royale" => "Supercell",
            // Tambahkan sesuai kebutuhan
        ];

        // Ambil kategori id dari tabel kategoris
        $gameKategori = Kategori::firstOrCreate(['kategori_name' => 'Top Up']);
        $pulsaKategori = Kategori::firstOrCreate(['kategori_name' => 'Pulsa']);

        // Counter
        $affected = [
            'game' => 0,
            'pulsa' => 0,
        ];

        // Tambahkan produk game
        foreach (array_unique($arrGame) as $gameBrand) {
            // $game = Produk::where('brand', $gameBrand)->first();
            $developer = $developerList[$gameBrand] ?? 'Unknown';

            Produk::updateOrCreate(
                ['reference' => $gameBrand],
                [
                    "nama" => $gameBrand,
                    "developer" => $developer,
                    "kategori_id" => $gameKategori->id,
                    "reference" => $gameBrand,
                    "provider_id" => $provider->id,
                    "slug" => Str::slug($gameBrand, '-'),
                    "status" => 'active',
                ]
            );
            $affected['game']++;
        }

        // Tambahkan produk pulsa
        foreach (array_unique($arrPulsa) as $pulsaBrand) {
            Produk::updateOrCreate(
                ['reference' => $pulsaBrand],
                [
                    "nama" => $pulsaBrand,
                    "developer" => $pulsaBrand,
                    "kategori_id" => $pulsaKategori->id,
                    "reference" => $pulsaBrand,
                    "provider_id" => $provider->id,
                    "slug" => Str::slug($pulsaBrand, '-'),
                    "status" => 'active',
                ]
            );
            $affected['pulsa']++;
        }

        return $affected;
    }
}
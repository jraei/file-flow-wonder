<?php

namespace App\Http\Controllers\Admin;

use App\Models\Provider;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\MoogoldController;
use App\Http\Controllers\Admin\ProviderController;

class CronjobController extends Controller
{
    public function getMoogold()
    {
        try {

            $provider = Provider::where('provider_name', 'moogold')->first();
            $moogold = new MoogoldController();

            $affectedProduk = $moogold->getMoogoldProducts();
            $affectedLayanan = $moogold->getMoogoldServices();
            $providerController = new ProviderController;
            $providerController->updateServicesRateKurs($provider);


            if (!$affectedProduk['status']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed get products from Moogold: ' . $affectedProduk['message'],
                ]);
            }

            if (!$affectedLayanan['status']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed get services from Moogold' . $affectedLayanan['message'],
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Success get services & products from Moogold',
                'success' => $affectedProduk['success'] + $affectedLayanan['success'],
                'failed' => $affectedProduk['failed'] + $affectedLayanan['failed']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getDigiflazz()
    {
        try {
            $provider = Provider::where('provider_name', 'digiflazz')->first();
            $digiflazz = new DigiflazzController();

            $affectedProduk = $digiflazz->getDigiflazzProduk();
            $affectedLayanan = $digiflazz->getDigiflazzService();
            $providerController = new ProviderController;
            $providerController->updateServicesRateKurs($provider);

            if (!$affectedProduk['game'] || !$affectedProduk['pulsa']) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed get products from Digiflazz: ' . $affectedProduk['message'],
                ]);
            }

            if (!$affectedLayanan) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed get services from Digiflazz' . $affectedLayanan['message'],
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Success get services & products from Digiflazz',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
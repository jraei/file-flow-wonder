<?php

namespace App\Http\Controllers;

use Xendit\Configuration;
use Illuminate\Http\Request;
use Xendit\Invoice\InvoiceApi;
use Xendit\XenditSdkException;
use App\Models\PaymentProvider;
use Xendit\PaymentMethod\PaymentMethodApi;

class XenditController extends Controller
{

    public function __construct()
    {
        $xendit_key = PaymentProvider::where('provider_name', 'Xendit')->first();
        Configuration::setXenditKey($xendit_key->private_key);
    }

    public function createInvoice()
    {
        $apiInstance = new InvoiceApi();
        $create_invoice_request = new Xendit\Invoice\CreateInvoiceRequest([
            'external_id' => 'test1234',
            'description' => 'Test Invoice',
            'amount' => 10000,
            'invoice_duration' => 172800,
            'currency' => 'IDR',
            'reminder_time' => 1
        ]); // \Xendit\Invoice\CreateInvoiceRequest
        $for_user_id = "62efe4c33e45694d63f585f0"; // string | Business ID of the sub-account merchant (XP feature)

        try {
            $result = $apiInstance->createInvoice($create_invoice_request, $for_user_id);
            print_r($result);
        } catch (XenditSdkException $e) {
            dd($e->getMessage());
        }
    }

    public function getPaymentMethods()
    {
        $apiInstance = new PaymentMethodApi();

        try {
            $result = $apiInstance->getAllPaymentMethods();
            dd($result);
        } catch (\Xendit\XenditSdkException $e) {
            dd($e->getMessage());
        }
    }
}

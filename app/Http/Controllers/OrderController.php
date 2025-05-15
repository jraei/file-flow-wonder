<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Produk;
use App\Models\Layanan;
use App\Models\Voucher;
use App\Models\PayMethod;
use App\Models\Pembelian;
use App\Models\WebConfig;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use App\Models\FlashsaleItem;
use App\Models\ItemThumbnail;
use App\Models\FlashsaleEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MoogoldController;
use App\Http\Controllers\Admin\TripayController;
use App\Http\Controllers\Admin\DigiflazzController;
use App\Http\Controllers\Admin\CheckUsernameController;

class OrderController extends Controller
{
    public function index(Produk $produk)
    {
        $inputFields = $produk->inputFields()->with('options')->orderBy('order')->get();
        $waNumber = WebConfig::get('support_whatsapp', '');

        $excludedLayananIds = FlashsaleItem::whereHas('flashsaleEvent', function ($q) {
            $q->where('status', 'active')
                ->where('event_start_date', '<=', now())
                ->where('event_end_date', '>=', now());
        })
            ->where('status', 'active')
            ->pluck('layanan_id');

        // Normal services (excluding flashsale items)
        $services = $produk->layanan()
            ->whereNotIn('id', $excludedLayananIds)
            ->orderBy('harga_beli_idr', 'asc')
            ->get()
            ->map(function ($service) {
                // Cari angka dari nama_layanan
                preg_match('/(\d+)/', $service->nama_layanan, $matches);
                $quantity = isset($matches[1]) ? (int)$matches[1] : null;

                $thumbnail = null;
                if ($quantity !== null) {
                    $thumbnail = ItemThumbnail::findThumbnailForQuantity($service->produk_id, $quantity);
                }

                // Kalau gagal dapet thumbnail dari quantity, fallback ke default
                if (!$thumbnail) {
                    $thumbnail = ItemThumbnail::where('produk_id', $service->produk_id)
                        ->default()
                        ->first();
                }

                return array_merge($service->toArray(), [
                    'thumbnail' => $thumbnail?->image_url
                ]);
            });

        // Get active flashsale events related to this product
        $flashsaleEvents = FlashsaleEvent::whereHas('layanan', function ($q) use ($produk) {
            $q->where('produk_id', $produk->id);
        })
            ->where('status', 'active')
            ->where('event_start_date', '<=', now())
            ->where('event_end_date', '>=', now())
            ->get();

        // Active flashsale items with stock/price validation
        $flashsaleItems = FlashsaleItem::with('flashsaleEvent', 'layanan')
            ->whereHas('layanan', function ($q) use ($produk) {
                $q->where('produk_id', $produk->id);
            })
            ->whereHas('flashsaleEvent', function ($q) {
                $q->where('status', 'active')
                    ->where('event_start_date', '<=', now())
                    ->where('event_end_date', '>=', now());
            })
            ->where('status', 'active')
            ->where(function ($q) {
                $q->where('stok_tersedia', '>', 0)
                    ->orWhereNull('stok_tersedia');
            })
            ->orderBy('harga_flashsale', 'asc')
            ->get()
            ->filter(function ($item) {
                $layanan = $item->layanan;
                return $layanan->harga_jual > $item->harga_flashsale;
            })
            ->map(function ($item) {
                $service = $item->layanan;
                // Similar thumbnail logic for flashsale items
                preg_match('/(\d+)/', $service->nama_layanan, $matches);
                $quantity = $matches[1] ?? null;

                $serviceWithThumbnail = array_merge($service->toArray(), [
                    'thumbnail' => $service->gambar
                        ?: ItemThumbnail::findThumbnailForQuantity(
                            $service->produk_id,
                            $quantity
                        )?->image_url,
                    'flashSaleItem' => array_merge($item->toArray(), [
                        'is_active' => true, // Add boolean flag for frontend use
                    ])
                ]);

                return $serviceWithThumbnail;
            });


        // Dynamic methods (grouped by tipe)
        $methods = PayMethod::where('status', 'active')
            ->with('paymentProvider')
            ->get();

        // Ambil satu metode saldo
        $saldoMethod = $methods->firstWhere('tipe', 'Saldo Akun');

        $dynamicMethods = $methods
            ->where('tipe', '!=', 'Saldo Akun')
            ->groupBy('tipe')
            ->map(function ($group) {
                return $group->map(function ($method) {
                    return [
                        'id' => $method->id,
                        'nama' => $method->nama,
                        'tipe' => $method->tipe,
                        'fee_fixed' => $method->fee_fixed,
                        'fee_percent' => $method->fee_percent,
                        'fee_type' => $method->fee_type,
                        'min_amount' => $method->min_amount,
                        'max_amount' => $method->max_amount,
                        'gambar' => $method->gambar,
                        'is_recommended' => $method->keterangan && str_contains(strtolower($method->keterangan), 'recommended'),
                        'payment_provider' => $method->paymentProvider?->toArray(),
                    ];
                })->values();
            });

        // Get active vouchers for public display
        $activeVouchers = Voucher::where('is_public', true)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            })
            ->get()
            ->map(function ($voucher) {
                return [
                    'code' => $voucher->code,
                    'description' => $voucher->description,
                    'discount_value' => $voucher->discount_value,
                    'discount_type' => $voucher->discount_type,
                    'end_date' => $voucher->end_date?->format('d M Y'),
                    'max_discount' => $voucher->max_discount,
                    'min_purchase' => $voucher->min_purchase,
                    'usage_limit' => $voucher->usage_limit,
                    'usage_count' => $voucher->usage_count,
                    'is_public' => $voucher->is_public
                ];
            });

        // FAQs for product
        $faqs = [
            [
                'question' => 'How long does top-up take?',
                'answer' => 'Instant delivery for 90% of orders. Most top-ups are processed automatically and delivered within seconds. For manual processing, it may take up to 5 minutes during peak hours.',
                'category' => 'delivery'
            ],
            [
                'question' => 'Is it safe to purchase here?',
                'answer' => 'Yes, all transactions are secured with industry-standard encryption. We never store your complete payment details and have been serving customers since 2020 with a 99.7% satisfaction rate.',
                'category' => 'security'
            ],
            [
                'question' => 'What payment methods are accepted?',
                'answer' => 'We accept a wide range of payment methods including credit/debit cards, e-wallets, bank transfers, and convenience store payments. You can view all available options during checkout.',
                'category' => 'payment'
            ],
            [
                'question' => 'Can I get a refund if there\'s a problem?',
                'answer' => 'We offer a full refund if the top-up fails to deliver. Please contact our support team within 24 hours of purchase with your order number for assistance.',
                'category' => 'refunds'
            ]
        ];

        return Inertia::render('Order/Index', [
            'user' => auth()->user(),
            'produk' => $produk,
            'layanans' => $services,
            'flashsaleItems' => $flashsaleItems,
            'inputFields' => $inputFields,
            'waNumber' => $waNumber,
            'flashsaleEvents' => $flashsaleEvents,
            'dynamicMethods' => $dynamicMethods,
            'saldoMethod' => $saldoMethod,
            'activeVouchers' => $activeVouchers,
            'faqs' => $faqs,
        ]);
    }

    public function invoice($order_id)
    {
        // Find order by order_id
        $pembelian = Pembelian::with(['layanan.produk', 'pembayaran', 'user'])
            ->where('order_id', $order_id)
            ->first();

        if (!$pembelian) {
            // kirim error ke view
            return back()->with('status', [
                'type' => 'error',
                'action' => 'Not Found',
                'text' => 'Transaction not found. Please check your invoice ID.'
            ]);
        }

        // Get product information
        $produk = $pembelian->layanan->produk;

        return Inertia::render('Order/Invoice', [
            'order' => $pembelian,
            'payment' => $pembelian->pembayaran,
            'product' => $produk,
            'user' => auth()->user(),
        ]);
    }

    /**
     * Process the order confirmation (Phase 1)
     */
    public function confirmOrder(Request $request)
    {
        // Validate the core fields
        $validator = Validator::make($request->all(), [
            'layanan_id' => 'required|exists:layanans,id',
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'required',
            'email' => 'nullable|email',
            'phone' => 'required|string|min:7',
            'voucher_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $layanan = \App\Models\Layanan::with('produk')->findOrFail($request->layanan_id);
        $produk = $layanan->produk;

        // Extract input ID and zone from dynamically named fields
        $inputId = null;
        $inputZone = null;

        // Get the input fields configuration for this product
        $inputFields = $produk->inputFields()->with('options')->get();

        // Dynamic fields container for all product-specific inputs
        $dynamicFields = [];

        // Find the user ID and zone/server fields from inputs
        foreach ($inputFields as $field) {
            $fieldName = $field->name;

            // Check if the field exists in the request
            if ($request->has($fieldName)) {
                // Add to dynamic fields collection
                $dynamicFields[$fieldName] = $request->input($fieldName);

                // Set special fields for validation
                if ($field->isUserIdField()) {
                    $inputId = $request->input($fieldName);
                } elseif ($field->isServerField()) {
                    $inputZone = $request->input($fieldName);
                }
            }
        }

        // Check if it's a flashsale item
        $flashsaleItem = null;

        if ($request->flashsale_item_id && $request->layanan_id) {
            $flashsaleData = $this->validateFlashsale($request->flashsale_item_id, $request->layanan_id, $request->quantity);

            if ($flashsaleData['status'] !== 'success') {
                return response()->json([
                    'status' => 'error',
                    'message' => $flashsaleData['message'],
                ], 422);
            }

            $flashsaleItem = $flashsaleData['data'];
        }

        // calculate base price
        $basePrice = $flashsaleItem ? $flashsaleItem->harga_flashsale : $layanan->harga_jual;
        $price = $basePrice * $request->quantity;


        // Process voucher if provided
        $voucherDiscount = 0;
        if ($request->voucher_code) {

            $voucher = $this->validateVoucher($request->voucher_code, $price);
            $voucherDiscount = $voucher['calculated_discount'];
        }


        // Get username if validation is required
        $username = null;
        $validationError = null;
        if ($produk->validasi_id !== 'tidak' && $produk->validasi_id !== null) {
            try {
                $usernameController = new CheckUsernameController();
                $data = [
                    'game' => $produk->validasi_id,
                    'user_id' => $inputId
                ];

                if ($inputZone) {
                    $data['zone_id'] = $inputZone;
                }

                $response = $usernameController->getAccountUsername($data);

                if ($response->getStatusCode() === 200) {
                    $responseData = json_decode($response->getContent(), true);
                    $username = $responseData['username'];
                } else {
                    $responseData = json_decode($response->getContent(), true);
                    $validationError = $responseData['message'] ?? 'Failed to validate account';
                }
            } catch (\Exception $e) {
                $validationError = $e->getMessage();
                // $validationError = $data;
            }
        }

        // Calculate total price
        $totalPrice = $price - $voucherDiscount;
        $totalPrice = ceil($totalPrice);

        // Calculate fees based on payment method
        $paymentInfo = $this->calculatePaymentFees($request->payment_method, $totalPrice);
        $finalPrice = $paymentInfo['finalPrice'];

        // Check profit safeguard
        $hargaBeli = $layanan->harga_beli_idr * $request->quantity;

        if ($finalPrice <= $hargaBeli) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid price calculation',
            ], 400);
        }

        // Return response with updated structure to include dynamic fields
        return response()->json([
            'status' => 'success',
            'orderSummary' => [
                'nickname' => $username,
                'validasi_id' => $produk->validasi_id,
                'validation_error' => $validationError,
                'account_id' => $inputId,
                'server_id' => $inputZone,
                'dynamic_fields' => $dynamicFields,
                'layanan' => $layanan->nama_layanan,
                'quantity' => $request->quantity,
                'basePrice' => $price,
                'discount' => $voucherDiscount,
                'payment_method' => $paymentInfo['methodName'],
                'payment_fee' => $paymentInfo['fee'],
                'final_price' => $finalPrice,
                'contact' => [
                    'email' => $request->email,
                    'phone' => $request->phone
                ]
            ]
        ]);
    }

    /**
     * Process the final order (Phase 2)
     */
    public function processOrder(Request $request)
    {
        // Validate user is authenticated if using balance
        if ($request->payment_method['type'] === 'Saldo Akun' && !Auth::check()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication required for balance payment',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'layanan_id' => 'required|exists:layanans,id',
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'required',
            'email' => 'nullable|email',
            'phone' => 'required|string|min:7',
            'voucher_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();

        $layanan = Layanan::with('produk')->findOrFail($request->layanan_id);
        $produk = $layanan->produk;
        $kategori = $produk->Kategori;


        $paymentMethodDynamic = PayMethod::where('id', $request->payment_method['channel'])->first();

        // Extract input ID and zone from dynamically named fields
        $inputId = null;
        $inputZone = null;

        // Get the input fields configuration for this product
        $inputFields = $produk->inputFields()->with('options')->get();

        // Dynamic fields container for all product-specific inputs
        $dynamicFields = [];

        // Find the user ID and zone/server fields from inputs
        foreach ($inputFields as $field) {
            $fieldName = $field->name;

            // Check if the field exists in the request
            if ($request->has($fieldName)) {
                // Add to dynamic fields collection
                $dynamicFields[$fieldName] = $request->input($fieldName);

                // Set special fields for validation
                if ($field->isUserIdField()) {
                    $inputId = $request->input($fieldName);
                } elseif ($field->isServerField()) {
                    $inputZone = $request->input($fieldName);
                }
            }
        }


        // Check if it's a flashsale item
        $flashsaleItem = null;
        if ($request->flashsale_item_id && $request->layanan_id) {
            $flashsaleData = $this->validateFlashsale($request->flashsale_item_id, $request->layanan_id, $request->quantity);

            if ($flashsaleData['status'] !== 'success') {
                return response()->json([
                    'status' => 'error',
                    'message' => $flashsaleData['message'],
                ], 422);
            }

            $flashsaleItem = $flashsaleData['data'];
        }
        // calculate basePrice

        $basePrice = $flashsaleItem ? $flashsaleItem->harga_flashsale : $layanan->harga_jual;
        $basePrice = $basePrice;
        $quantity = $request->quantity;
        $price = $basePrice * $quantity;

        // Process voucher if provided
        $voucher = null;
        $voucherDiscount = 0;
        if ($request->voucher_code) {
            $voucher = $this->validateVoucher($request->voucher_code, $price);
            $voucherDiscount = $voucher['calculated_discount'];
        }

        // Calculate total price
        $totalPrice = $price - $voucherDiscount;
        $totalPrice = ceil($totalPrice);

        // Calculate fees based on payment method
        $paymentInfo = $this->calculatePaymentFees($request->payment_method, $totalPrice);
        $finalPrice = $paymentInfo['finalPrice'];

        // Check profit safeguard
        $hargaBeli = $layanan->harga_beli_idr * $request->quantity;
        if ($totalPrice <= $hargaBeli) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid price calculation',
            ], 400);
        }


        // Generate unique order ID
        $orderId = $this->generateUniqueOrderId();

        // Prepare additional data for dynamic fields
        $additionalData = [];
        foreach ($request->except(['layanan_id', 'quantity', 'payment_method', 'email', 'phone', 'voucher_code', 'flashsale_item_id']) as $key => $value) {
            $additionalData[$key] = $value;
        }



        $status = null;
        $reference = null;

        // Process payment based on method
        if ($request->payment_method['type'] === 'Saldo Akun') {
            // Check if user has enough balance
            if ($user->saldo < $finalPrice) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Saldo tidak mencukupi',
                ], 400);
            }

            // Deduct balance
            $user->saldo -= $finalPrice;
            $user->save();


            Pembayaran::create([
                'order_id' => $orderId,
                'price' => $finalPrice,
                'fee' => $paymentInfo['fee'],
                'total_price' => $finalPrice,
                'payment_method' => 'Saldo Akun',
                'payment_provider' => 'Saldo Akun',
                'status' => 'paid',
            ]);

            if ($produk->provider->provider_name == 'moogold') {

                $moogoldOrderCategory = $produk->moogold_order_category;
                // Process order through API
                $moogold = new MoogoldController();


                $apiResult = $moogold->createTransaction([
                    'category_id' => $moogoldOrderCategory,
                    'order_id' => $orderId,
                    'service_id' => $layanan->kode_layanan,
                    'quantity' => $request->quantity,
                    'user_id' => $inputId,
                    'server' => $inputZone
                ]);


                if (!$apiResult['data']['status']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $apiResult['data']['raw']['err_message'] ?? $apiResult['data']['message']
                    ]);
                }

                $data = $apiResult['data'];

                // Update order status
                if ($data['response']['status'] == "pending" || $data['response']['status'] == "processing") {
                    $status = "completed";
                    $reference = $data['order_id'];
                } else {
                    $status = "failed";
                }
            } else if ($produk->provider->provider_name == 'digiflazz') {
                $digiflazz = new DigiflazzController();

                $target = $inputId . $inputZone;

                $apiResult = $digiflazz->createTransaction([
                    'kode_layanan' => $layanan->kode_layanan,
                    'target' => $target,
                    'ref_id' => $orderId,
                    'testing' => true
                ]);

                if (!$apiResult->status) {
                    return response()->json([
                        'status' => 'error',
                        'message' =>  $apiResult->message ?? "DF | Create transaction error"
                    ]);
                }


                // Update order status
                if ($apiResult->status == "Pending" || $apiResult->status == "Sukses") {
                    $status = "completed";
                    $reference = $apiResult->ref_id;
                } else {
                    $status = "failed";
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Provider name issue, please contact admin',
                ], 500);
            }
        } else {


            // Payment gateway integration
            $item = $produk->nama . ' - ' . $layanan->nama_layanan;

            $paymentData = [];

            // process tripay payment gateway if payment method provider is tripay

            if ($paymentMethodDynamic->payment_provider === 'Tripay') {

                $tripay = new TripayController();
                $response = $tripay->createTransaction([
                    'item' => $item,
                    'price' => $totalPrice,
                    'quantity' => 1,
                    'method' => $paymentInfo['methodCode'],
                    'merchant_ref' => $orderId,
                    'customer_name' => $user->username ?? 'Guest',
                    'customer_email' => $request->email ?? 'guest@gmail.com',
                    'customer_phone' => $request->phone
                ]);

                if (!$response || $response['data']['success'] !== true) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $response['data']['message'],
                    ]);
                }

                // Create invoice for payment
                $paymentData = $response['data']['data'];
            }

            Pembayaran::create([
                'order_id' => $orderId,
                'price' => $paymentData['amount_received'],
                'fee' => $paymentData['total_fee'],
                'total_price' => $paymentData['amount'],
                'payment_provider' => 'Tripay',
                'payment_method' => $paymentInfo['methodCode'],
                'payment_reference' => $paymentData['reference'],
                'status' => 'pending',
                'instruksi' => $paymentData['instructions'] ?? null,
                'qr_url' => $paymentData['qr_url'] ?? null,
                'payment_link' => $paymentData['checkout_url'] ?? null,
                'expired_time' => $paymentData['expired_time'] ?? null,
            ]);

            $status = 'pending';
        }

        if ($status) {

            // reduce voucher & flashsales
            if ($flashsaleItem) {
                $this->reduceFlashsaleStock($request->flashsale_item_id, $quantity);
            }
            if ($voucher) {
                $this->reduceVoucherStock($request->voucher_code);
            }

            // Create the pembelian record
            $pembelian = new Pembelian();
            $pembelian->order_id = $orderId;
            $pembelian->order_type = 'game';
            $pembelian->user_id = Auth::id();
            $pembelian->layanan_id = $layanan->id;
            $pembelian->nickname = $request->nickname;
            $pembelian->input_id = $inputId;
            $pembelian->input_zone = $inputZone;
            $pembelian->price = $basePrice;
            $pembelian->quantity = $quantity;
            $pembelian->discount = $voucherDiscount;
            $pembelian->total_price = $totalPrice;
            $pembelian->profit = $totalPrice - $hargaBeli;
            $pembelian->status = $status;
            $pembelian->reference_id = $reference;
            $pembelian->phone = $request->phone;
            $pembelian->email = $request->email;
            $pembelian->voucher_id = $voucher ? $voucher['id'] : null;
            $pembelian->flashsale_item_id = $flashsaleItem ? $flashsaleItem->id : null;

            // Store additional fields as JSON
            if (!empty($additionalData)) {
                $pembelian->callback_data = $additionalData;
            }

            $pembelian->save();


            return response()->json([
                'status' => 'success',
                'message' => 'Order processed successfully',
                'order_id' => $orderId,
                'redirect' => true,
            ]);
        }
    }

    /**
     * Helper method to calculate payment fees
     */
    private function calculatePaymentFees($paymentMethod, $amount)
    {
        $finalPrice = $amount;
        $fee = 0;
        $methodName = '';
        $methodType = '';

        if ($paymentMethod['type'] === 'Saldo Akun') {
            $saldo = PayMethod::where('tipe', 'Saldo Akun')->first();
            $methodName = $saldo->nama;
            $methodType = $saldo->tipe;
        } elseif ($paymentMethod['type'] === 'qris') {
            $qris = PayMethod::where('tipe', 'QRIS')->first();
            $methodName = $qris->nama;
            $methodType = $qris->tipe;
            $methodCode = $qris->kode;

            if ($qris) {
                if ($qris->fee_type === 'fixed') {
                    $fee = $qris->fee_fixed;
                    $finalPrice += $fee;
                } elseif ($qris->fee_type === 'percent') {
                    $fee = $amount * ($qris->fee_percent / 100);
                    $finalPrice += $fee;
                } elseif ($qris->fee_type === 'mixed') {
                    $fee = $qris->fee_fixed + ($amount * ($qris->fee_percent / 100));
                    $finalPrice += $fee;
                }
            }
        } else {
            // Dynamic payment method
            $payMethod = PayMethod::find($paymentMethod['channel']);
            if ($payMethod) {
                $methodName = $payMethod->nama;
                $methodType = $payMethod->tipe;
                $methodCode = $payMethod->kode;

                if ($payMethod->fee_type === 'fixed') {
                    $fee = $payMethod->fee_fixed;
                    $finalPrice += $fee;
                } elseif ($payMethod->fee_type === 'percent') {
                    $fee = $amount * ($payMethod->fee_percent / 100);
                    $finalPrice += $fee;
                } elseif ($payMethod->fee_type === 'mixed') {
                    $fee = $payMethod->fee_fixed + ($amount * ($payMethod->fee_percent / 100));
                    $finalPrice += $fee;
                }
            }
        }

        return [
            'finalPrice' => ceil($finalPrice), // Round up to nearest integer
            'fee' => ceil($fee),
            'methodName' => $methodName,
            'methodType' => $methodType,
            'methodCode' => $methodCode ?? '',
        ];
    }

    /**
     * Validate voucher code
     */
    public function validateVoucher($code, $amount)
    {
        $validator = Validator::make([
            'code' => $code,
            'amount' => $amount,
        ], [
            'code' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $voucher = Voucher::where('code', $code)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            })
            ->first();

        if (!$voucher) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher not found or expired',
            ], 404);
        }

        // Check usage limit
        if ($voucher->usage_limit && $voucher->usage_count >= $voucher->usage_limit) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher has reached usage limit',
            ], 400);
        }

        // Check minimum purchase
        if ($voucher->min_purchase && $amount < $voucher->min_purchase) {
            return response()->json([
                'status' => 'error',
                'message' => "Minimum purchase amount is " . number_format($voucher->min_purchase),
            ], 400);
        }

        // Calculate discount
        $discount = 0;
        if ($voucher->discount_type === 'fixed') {
            $discount = $voucher->discount_value;
        } else {
            $discount = ($amount * $voucher->discount_value) / 100;

            // Apply max discount cap if exists
            if ($voucher->max_discount && $discount > $voucher->max_discount) {
                $discount = $voucher->max_discount;
            }
        }

        // Ensure discount doesn't exceed the purchase amount
        $discount = min($discount, $amount);

        return  [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'discount_type' => $voucher->discount_type,
            'discount_value' => $voucher->discount_value,
            'calculated_discount' => $discount,
            'min_purchase' => $voucher->min_purchase,
            'max_discount' => $voucher->max_discount,
        ];
    }

    public function reduceVoucherStock($code)
    {
        $voucher = Voucher::where('code', $code)->first();
        $voucher->usage_count += 1;
        $voucher->save();
    }

    // validate flashsale
    public function validateFlashsale($flashsale_item_id, $layanan_id, $quantity)
    {
        $flashsaleItem = FlashsaleItem::where('id', $flashsale_item_id)
            ->whereHas('flashsaleEvent', function ($q) {
                $q->where('status', 'active')
                    ->where('event_start_date', '<=', now())
                    ->where('event_end_date', '>=', now());
            })
            ->where('status', 'active')
            ->where('layanan_id', $layanan_id)
            ->first();

        if (!$flashsaleItem) {
            return [
                'status' => 'error',
                'message' => 'Flash sale item tidak ditemukan atau tidak aktif',
            ];
        }

        // Cek stok tersedia
        if ($flashsaleItem->stok_tersedia !== null) {
            if ($flashsaleItem->stok_tersedia <= 0) {
                return [
                    'status' => 'error',
                    'message' => 'Stok flash sale habis',
                ];
            }

            if ($flashsaleItem->stok_tersedia < $quantity) {
                return [
                    'status' => 'error',
                    'message' => 'Jumlah melebihi stok tersedia',
                ];
            }
        }

        // Cek batas pembelian user
        if ($flashsaleItem->batas_user !== null) {
            if (auth()->check()) {

                $userTotalBought = Pembelian::where('user_id', auth()->id())
                    ->where('flashsale_item_id', $flashsaleItem->id)
                    ->whereIn('status', ['processing', 'completed'])
                    ->count();

                if (($userTotalBought + 1) > $flashsaleItem->batas_user) {
                    return [
                        'status' => 'error',
                        'message' => 'Kamu telah mencapai batas pembelian flash sale untuk layanan ini',
                    ];
                }
            } else {
                $sessionKey = 'flashsale_limit_' . $flashsale_item_id;
                $guestPurchaseCount = session()->get($sessionKey, 0);

                if (($guestPurchaseCount + 1) > $flashsaleItem->batas_user) {
                    return [
                        'status' => 'error',
                        'message' => 'Batas pembelian flash sale telah tercapai untuk pengguna ini.',
                    ];
                }

                // Simpan kembali ke session
                session()->put($sessionKey, $guestPurchaseCount + 1);
            }
        }

        return [
            'status' => 'success',
            'data' => $flashsaleItem,
        ];
    }



    public function reduceFlashsaleStock($flashsale_item_id, $quantity)
    {
        $flashsaleItem = FlashsaleItem::find($flashsale_item_id);

        if (!$flashsaleItem) {
            return response()->json([
                'status' => 'error',
                'message' => 'Flash sale item not found',
            ], 404);
        }

        if ($flashsaleItem->stok_tersedia != null && $flashsaleItem->stok_tersedia > 0) {
            $flashsaleItem->stok_tersedia -= $quantity;
        }
        $flashsaleItem->stok_terjual += $quantity;
        $flashsaleItem->save();
    }


    /**
     * Validate account before order processing
     */
    public function validateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'game' => 'required|string',
            'user_id' => 'required|string',
            'zone_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $usernameController = new CheckUsernameController();
        $data = [
            'game' => $request->game,
            'user_id' => $request->user_id
        ];

        if ($request->zone_id) {
            $data['zone_id'] = $request->zone_id;
        }

        try {
            $response = $usernameController->getAccountUsername($data);

            return $response;
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    protected function generateUniqueOrderId()
    {
        do {
            $prefix = 'N'; // bisa diganti "M", "X", dll
            $timestamp = now()->format('dmHis'); // d=day, m=month, H=hour, i=minute, s=second
            $random = mt_rand(100, 999); // random 3 digit
            $orderId = $prefix . $timestamp . $random;
        } while ($this->orderIdExists($orderId));

        return $orderId;
    }

    protected function orderIdExists($orderId)
    {
        return Pembelian::where('order_id', $orderId)->exists();
    }
}

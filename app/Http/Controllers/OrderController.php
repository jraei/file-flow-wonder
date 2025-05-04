<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Produk;
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

        // Payment Method Data Assembly ---
        // Static methods (saldo, qris)
        $staticMethods = [
            'saldo' => PayMethod::where('tipe', 'Saldo Akun')->first(),
            'qris' => [
                'nama' => 'QRIS (Semua Pembayaran)',
                'gambar' => PayMethod::where('tipe', 'QRIS')->first()?->gambar,
                'fee_fixed' => PayMethod::where('tipe', 'QRIS')->first()?->fee_fixed,
                'fee_percent' => PayMethod::where('tipe', 'QRIS')->first()?->fee_percent,
                'fee_type' => PayMethod::where('tipe', 'QRIS')->first()?->fee_type,
                'min_amount' => PayMethod::where('tipe', 'QRIS')->first()?->min_amount,
                'max_amount' => PayMethod::where('tipe', 'QRIS')->first()?->max_amount
            ]
        ];
        // Dynamic methods (grouped by tipe)
        $dynamicMethods = PayMethod::whereNotIn('tipe', ['saldo', 'QRIS'])
            ->where('status', 'active')
            ->with('paymentProvider')
            ->get()
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
            'staticMethods' => $staticMethods,
            'dynamicMethods' => $dynamicMethods,
            'activeVouchers' => $activeVouchers,
            'faqs' => $faqs,
        ]);
    }

    public function invoice($orderId)
    {
        $order = Pembelian::with(['layanan.produk', 'pembayaran', 'user'])
            ->where('order_id', $orderId)
            ->firstOrFail();

        return Inertia::render('Order/Invoice', [
            'order' => $order
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
        if ($request->has('flashsale_item_id')) {
            $flashsaleItem = FlashsaleItem::where('id', $request->flashsale_item_id)
                ->whereHas('flashsaleEvent', function ($q) {
                    $q->where('status', 'active')
                        ->where('event_start_date', '<=', now())
                        ->where('event_end_date', '>=', now());
                })
                ->where('status', 'active')
                ->where('layanan_id', $request->layanan_id)
                ->first();
        }

        // calculate base price
        $basePrice = $flashsaleItem ? $flashsaleItem->harga_flashsale : $layanan->harga_jual;
        $basePrice = ceil($basePrice * $request->quantity);

        // Process voucher if provided
        $voucherDiscount = 0;
        if ($request->voucher_code) {
            $voucher = $this->validateVoucher($request->voucher_code, $basePrice);
            $voucherDiscount = $voucher['discount_value'];
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
                    $username = $responseData['username'] ?? null;
                } else {
                    $responseData = json_decode($response->getContent(), true);
                    $validationError = $responseData['message'] ?? 'Failed to validate account';
                }
            } catch (\Exception $e) {
                $validationError = $e->getMessage();
            }
        }

        // Calculate total price
        $totalPrice = $basePrice - $voucherDiscount;

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
                'basePrice' => $basePrice,
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
        if ($request->payment_method['type'] === 'saldo' && !Auth::check()) {
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
        $flashsaleDiscount = 0;
        if ($request->has('flashsale_item_id')) {
            $flashsaleItem = FlashsaleItem::where('id', $request->flashsale_item_id)
                ->whereHas('flashsaleEvent', function ($q) {
                    $q->where('status', 'active')
                        ->where('event_start_date', '<=', now())
                        ->where('event_end_date', '>=', now());
                })
                ->where('status', 'active')
                ->where('layanan_id', $request->layanan_id)
                ->first();

            if ($flashsaleItem) {
                if ($flashsaleItem->stok_tersedia !== null && $flashsaleItem->stok_tersedia <= 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Flash sale item out of stock',
                    ], 400);
                }

                $flashsaleDiscount = $layanan->harga_jual - $flashsaleItem->harga_flashsale;
            }
        }

        // calculate basePrice
        $basePrice = $flashsaleItem ? $flashsaleItem->harga_flashsale : $layanan->harga_jual;
        $basePrice = ceil($basePrice * $request->quantity);

        // Process voucher if provided
        $voucherDiscount = 0;
        if ($request->voucher_code) {
            $voucher = $this->validateVoucher($request->voucher_code, $basePrice);
            $voucherDiscount = $voucher['discount_value'];
        }

        // Calculate total price
        $totalPrice = $basePrice - $voucherDiscount;

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

        // Create the pembelian record
        $pembelian = new Pembelian();
        $pembelian->order_id = $orderId;
        $pembelian->order_type = 'game';
        $pembelian->user_id = Auth::id();
        $pembelian->layanan_id = $layanan->id;
        $pembelian->nickname = $request->nickname;
        $pembelian->input_id = $inputId;
        $pembelian->input_zone = $inputZone;
        $pembelian->price = $totalPrice;
        $pembelian->profit = $totalPrice - $hargaBeli;
        $pembelian->status = 'pending';
        $pembelian->phone = $request->phone;
        $pembelian->email = $request->email;

        // Store additional fields as JSON
        if (!empty($additionalData)) {
            $pembelian->callback_data = $additionalData;
        }

        $pembelian->save();

        // Process payment based on method
        if ($request->payment_method['type'] === 'saldo') {
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
                'total_price' => $finalPrice,
                'payment_method' => 'Saldo Akun',
                'status' => 'success',
            ]);

            // Process order through API
            $moogold = new MoogoldController();

            try {
                $apiResult = $moogold->createTransaction([
                    'category_id' => $produk->kategori_id,
                    'order_id' => $orderId,
                    'service_id' => $layanan->api_service_id,
                    'quantity' => $request->quantity,
                    'user_id' => $request->input_id,
                    'server' => $request->input_zone
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to process order',
                ], 500);
            }


            // Update order status
            $pembelian->reference_id = $apiResult['data']['order_id'];
            $pembelian->status = 'processing';
            $pembelian->save();


            return response()->json([
                'status' => 'success',
                'message' => 'Order processed successfully',
                'order_id' => $orderId,
            ]);
        } else {

            // Payment gateway integration
            $item = $produk->nama . ' - ' . $layanan->nama_layanan;
            $tripay = new TripayController();
            $response = $tripay->createTransaction([
                'item' => $item,
                'price' => $totalPrice,
                'quantity' => $request->quantity,
                'method' => $paymentInfo['methodCode'],
                'merchant_ref' => $orderId,
                'customer_name' => $user->username ?? 'Guest',
                'customer_email' => $request->email ?? 'guest@gmail.com',
                'customer_phone' => $request->phone
            ]);

            if (!$response) {
                return response()->json([
                    'status' => 'error',
                    'message' => $response['message'],
                ]);
            }

            // Create invoice for payment
            $tripayData = $response['data'];
            Pembayaran::create([
                'order_id' => $orderId,
                'payment_provider' => 'Tripay',
                'price' => $tripayData['amount_received'],
                'fee' => $tripayData['total_fee'],
                'total_price' => $tripayData['amount'],
                'payment_link' => $tripayData['checkout_url'],
                'payment_method' => $paymentInfo['methodCode'],
                'payment_reference' => $tripayData['reference'],
                'expired_time' => $tripayData['expired_time'],
                'status' => 'pending',
            ]);

            // Redirect to payment gateway or return payment link
            return response()->json([
                'status' => 'success',
                'message' => $tripayData,
                'order_id' => $orderId,
                'payment_url' => route('payment.show', ['order_id' => $orderId]),
                'redirect' => true
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

        if ($paymentMethod['type'] === 'saldo') {
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
    public function validateVoucher(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $voucher = Voucher::where('code', $request->code)
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
        if ($voucher->min_purchase && $request->amount < $voucher->min_purchase) {
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
            $discount = ($request->amount * $voucher->discount_value) / 100;

            // Apply max discount cap if exists
            if ($voucher->max_discount && $discount > $voucher->max_discount) {
                $discount = $voucher->max_discount;
            }
        }

        // Ensure discount doesn't exceed the purchase amount
        $discount = min($discount, $request->amount);

        return  [
            'code' => $voucher->code,
            'discount_type' => $voucher->discount_type,
            'discount_value' => $voucher->discount_value,
            'calculated_discount' => $discount,
            'min_purchase' => $voucher->min_purchase,
            'max_discount' => $voucher->max_discount,
        ];
    }

    // validate flashsale
    public function validateFlashsale(Request $request) {}


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

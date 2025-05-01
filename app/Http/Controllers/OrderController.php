<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Produk;
use App\Models\Voucher;
use App\Models\PayMethod;
use App\Models\Pembelian;
use App\Models\WebConfig;
use Illuminate\Http\Request;
use App\Models\FlashsaleItem;
use App\Models\ItemThumbnail;
use App\Models\FlashsaleEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MoogoldController;
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

    /**
     * Process the order confirmation (Phase 1)
     */
    public function confirmOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'layanan_id' => 'required|exists:layanans,id',
            // 'input_id' => 'required|string',
            'input_zone' => 'nullable|string',
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

        // Process voucher if provided
        $voucher = null;
        $voucherDiscount = 0;
        if ($request->voucher_code) {
            $voucher = Voucher::where('code', $request->voucher_code)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>', now());
                })
                ->first();

            if ($voucher) {
                // Calculate base price
                $basePrice = $flashsaleItem ? $flashsaleItem->harga_flashsale : $layanan->harga_jual;
                $basePrice = $basePrice * $request->quantity;

                // Check minimum purchase requirement
                if ($voucher->min_purchase && $basePrice < $voucher->min_purchase) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Minimum purchase for voucher not met',
                    ], 422);
                }

                // Calculate discount
                if ($voucher->discount_type === 'fixed') {
                    $voucherDiscount = $voucher->discount_value;
                } else {
                    $voucherDiscount = ($basePrice * $voucher->discount_value) / 100;

                    // Apply max discount cap if exists
                    if ($voucher->max_discount && $voucherDiscount > $voucher->max_discount) {
                        $voucherDiscount = $voucher->max_discount;
                    }
                }

                // Ensure discount doesn't exceed the base price
                $voucherDiscount = min($voucherDiscount, $basePrice);
                // Math ceil to avoid rounding errors
                $voucherDiscount = ceil($voucherDiscount);
            }
        }

        // Get username if validation is required
        $username = null;
        $validationError = null;
        if ($produk->validasi_id !== 'tidak') {
            try {
                $usernameController = new CheckUsernameController();
                $data = [
                    'game' => $produk->validasi_id,
                    'user_id' => $request->input_id
                ];

                if ($request->input_zone) {
                    $data['zone_id'] = $request->input_zone;
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
        $basePrice = $flashsaleItem ? $flashsaleItem->harga_flashsale : $layanan->harga_jual;
        $basePrice = ceil($basePrice);
        $totalPrice = $basePrice * $request->quantity - $voucherDiscount;

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

        return response()->json([
            'status' => 'success',
            'orderSummary' => [
                'nickname' => $username,
                'validation_error' => $validationError,
                'account_id' => $request->input_id,
                'server_id' => $request->input_zone,
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
            'input_id' => 'required|string',
            'input_zone' => 'nullable|string',
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

        // Process voucher if provided
        $voucher = null;
        $voucherDiscount = 0;
        if ($request->voucher_code) {
            $voucher = Voucher::where('code', $request->voucher_code)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('end_date')
                        ->orWhere('end_date', '>', now());
                })
                ->first();

            if ($voucher) {
                // Calculate base price (with flashsale discount already applied if applicable)
                $basePrice = $flashsaleItem ? $flashsaleItem->harga_flashsale : $layanan->harga_jual;
                $basePrice = $basePrice * $request->quantity;

                // Check minimum purchase requirement
                if ($voucher->min_purchase && $basePrice < $voucher->min_purchase) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Minimum purchase for voucher not met',
                    ], 422);
                }

                // Calculate discount
                if ($voucher->discount_type === 'fixed') {
                    $voucherDiscount = $voucher->discount_value;
                } else {
                    $voucherDiscount = ($basePrice * $voucher->discount_value) / 100;

                    // Apply max discount cap if exists
                    if ($voucher->max_discount && $voucherDiscount > $voucher->max_discount) {
                        $voucherDiscount = $voucher->max_discount;
                    }
                }

                // Ensure discount doesn't exceed the base price
                $voucherDiscount = min($voucherDiscount, $basePrice);
            }
        }

        // Calculate total price
        $basePrice = $flashsaleItem ? $flashsaleItem->harga_flashsale : $layanan->harga_jual;
        $totalPrice = $basePrice * $request->quantity - $voucherDiscount;

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

        // Create the pembelian record
        $pembelian = new Pembelian();
        $pembelian->order_id = $orderId;
        $pembelian->order_type = 'game';
        $pembelian->user_id = Auth::id();
        $pembelian->layanan_id = $layanan->id;
        $pembelian->nickname = $request->nickname;
        $pembelian->input_id = $request->input_id;
        $pembelian->input_zone = $request->input_zone;
        $pembelian->price = $finalPrice;
        $pembelian->profit = $totalPrice - $hargaBeli;
        $pembelian->status = 'pending';
        $pembelian->save();

        // Process payment based on method
        if ($request->payment_method['type'] === 'saldo') {
            // Check if user has enough balance
            $user = Auth::user();
            if ($user->saldo < $finalPrice) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Saldo tidak mencukupi',
                ], 400);
            }

            // Deduct balance
            $user->saldo -= $finalPrice;
            $user->save();

            // Process order through API
            $moogold = new MoogoldController();
            $retryCount = 0;
            $apiResult = null;

            do {
                try {
                    $apiResult = $moogold->createTransaction([
                        'category_id' => $produk->kategori_id,
                        'order_id' => $orderId,
                        'service_id' => $layanan->api_service_id,
                        'quantity' => $request->quantity,
                        'user_id' => $request->input_id,
                        'server' => $request->input_zone
                    ]);

                    if ($apiResult && isset($apiResult['status']) && $apiResult['status'] === 'success') {
                        break;
                    }
                } catch (\Exception $e) {
                    if ($retryCount >= 2) {
                        // Refund user's balance on final failure
                        $user->saldo += $finalPrice;
                        $user->save();

                        // Update order status
                        $pembelian->status = 'failed';
                        $pembelian->save();

                        return response()->json([
                            'status' => 'error',
                            'message' => 'Failed to process order after multiple attempts',
                        ], 500);
                    }
                }

                $retryCount++;
            } while ($retryCount < 3);

            // Update order status
            $pembelian->status = 'processing';
            $pembelian->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Order processed successfully',
                'order_id' => $orderId,
            ]);
        } else {
            // Payment gateway integration
            // Create invoice for payment
            $pembayaran = new \App\Models\Pembayaran();
            $pembayaran->order_id = $orderId;
            $pembayaran->price = $finalPrice;
            $pembayaran->payment_method = $paymentInfo['methodType'];
            $pembayaran->status = 'pending';
            $pembayaran->save();

            // Redirect to payment gateway or return payment link
            return response()->json([
                'status' => 'success',
                'message' => 'Payment invoice created',
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
            $methodName = 'NaelCoin';
            $methodType = 'saldo';
        } elseif ($paymentMethod['type'] === 'qris') {
            $methodName = 'QRIS';
            $methodType = 'qris';

            $qris = PayMethod::where('tipe', 'QRIS')->first();
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
            'methodType' => $methodType
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

        return response()->json([
            'status' => 'success',
            'voucher' => [
                'code' => $voucher->code,
                'discount_type' => $voucher->discount_type,
                'discount_value' => $voucher->discount_value,
                'calculated_discount' => $discount,
                'min_purchase' => $voucher->min_purchase,
                'max_discount' => $voucher->max_discount,
            ],
        ]);
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
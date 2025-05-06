<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\Banner;
use App\Models\Produk;
use App\Models\Voucher;
use App\Models\Kategori;
use App\Models\Provider;
use App\Models\PayMethod;
use App\Models\PaymentProvider;
use Illuminate\Database\Seeder;
use App\Models\ProdukInputField;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {


        User::factory()->create([
            'username' => 'justin',
            'email' => 'justin@gmail.com',
            'password' => bcrypt('123justin'),
            'phone' => '081932888380',
            'user_role_id' => 1,
            'status' => 'active'
        ]);

        Banner::create([
            "image_path" => "banner/SaZNWDudT1tOIYvuyFHCITKwnCYi7My55qPqVgjF.webp",
            "order" => 1,
            "status" => "active"
        ]);

        User::factory()->count(50)->create();

        PaymentProvider::create([
            'provider_name' => 'Tripay',
            'kode_merchant' => env('TRIPAY_MERCHANT_CODE'),
            'api_key' => env('TRIPAY_APIKEY'),
            'private_key' => env('TRIPAY_PRIVATEKEY')
        ]);

        PaymentProvider::create([
            'provider_name' => 'Manual'
        ]);



        Provider::create([
            "provider_name" => "digiflazz",
            "api_key" => "eeeufytr2c-jassstinnnn-justin7765eerf",
            "api_username" => "figojugqYaVg"
        ]);
        Provider::create([
            "provider_name" => "moogold",
            "api_username" => "rubennatanael36@gmail.com",
            "api_key" => "e5c552e32e2b0df880d3f2e60d91001c",
            "api_private_key" => '3bfDmtIhPp'
        ]);
        Provider::create([
            "provider_name" => "checkUsername",
            "api_username" => "rubennatanael36@gmail.com",
            "api_key" => "e5c552e32e2b0df880d3f2e60d91001c",
            "api_private_key" => '3bfDmtIhPp',
            "base_url" => "https://apinickname.tinped.com/api/v1/check-game"
        ]);

        Kategori::create([
            "kategori_name" => "Top Up",
            "kode_kategori" => 50,
            "provider_id" => 2,
        ]);

        Produk::create([
            "nama" => "Mobile Legends (Indonesia)",
            "developer" => "Moonton",
            "reference" => 2362359,
            "kategori_id" => 1,
            "slug" => "mobile-legends-indonesia",
            "provider_id" => 2,
            "status" => "active",
        ]);
        PayMethod::create([
            "nama" => "Saldo Akun",
            "tipe" => "Saldo Akun",
            "kode" => "saldo",
            "payment_provider" => "Manual",
        ]);


        Voucher::create([
            "code" => "123456",
            "description" => "test",
            "discount_type" => "percentage",
            "discount_value" => 10,
            "start_date" => now(),
            "end_date" => now()->addDays(7),
            "status" => "active",
            "is_public" => true,
            "min_purchase" => 100,
            "max_discount" => 100,
            "usage_limit" => 3
        ]);

        ProdukInputField::create([
            "produk_id" => 1,
            "name" => "user_id",
            "label" => "User ID",
            "type" => "number",
            "required" => true,
            "order" => 1
        ]);
        ProdukInputField::create([
            "produk_id" => 1,
            "name" => "server_id",
            "label" => "Zone ID",
            "type" => "number",
            "required" => true,
            "order" => 2
        ]);
    }
}

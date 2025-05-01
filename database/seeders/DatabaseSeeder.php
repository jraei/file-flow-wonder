<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Provider;
use App\Models\SubKategori;
use Illuminate\Support\Str;
use App\Models\PaymentProvider;
use App\Models\WebConfig;
use Illuminate\Database\Seeder;

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

        Kategori::create([
            "kategori_name" => "Top Up"
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
            "api_private_key" => '3bfDmtIhPp'
        ]);

        Produk::create([
            "nama" => 'FREE FIRE',
            "developer" => 'Garena',
            "brand" => "FREE FIRE",
            "kategori_id" => 1,
            "slug" => Str::slug('FREE FIRE', '-'),
            "provider_id" => 1,
            "validasi_id" => "tidak",
            "deskripsi_game" => "s",
            "petunjuk_field" => "s",
            "status" => "active",
            "thumbnail" => "DtGDJVLmxITN2qlYkN6RbqiuxMqRjDZ0PjLqDVld.png"

        ]);

        // WebConfig::create([
        //     "judul_web" => "Game Store",
        //     "meta_judul" => "Game Store",
        //     "meta_deskripsi" => "Game Store",
        //     "meta_keywords" => "Game Store",
        //     "support_email" => "lH5wT@example.com",
        //     "support_whatsapp" => "081932888380",
        //     "support_instagram" => "https://www.instagram.com/",
        //     "support_youtube" => "https://www.youtube.com/",
        //     "support_facebook" => "https://www.facebook.com/",
        //     "primary_color" => "#6366F1",
        //     "secondary_color" => "#6366F1",
        //     "logo_header" => "DtGDJVLmxITN2qlYkN6RbqiuxMqRjDZ0PjLqDVld.png",
        //     "logo_footer" => "DtGDJVLmxITN2qlYkN6RbqiuxMqRjDZ0PjLqDVld.png",
        //     "logo_favicon" => "DtGDJVLmxITN2qlYkN6RbqiuxMqRjDZ0PjLqDVld.png"
        // ]);


        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
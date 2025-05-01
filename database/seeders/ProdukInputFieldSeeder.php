<?php

namespace Database\Seeders;

use App\Models\Produk;
use Illuminate\Database\Seeder;
use App\Models\ProdukInputField;
use App\Models\ProdukInputOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProdukInputFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Ambil produk berdasarkan nama
        $games = [
            'Mobile Legends' => [
                [
                    'name' => 'user_id',
                    'label' => 'User ID',
                    'type' => 'number',
                ],
                [
                    'name' => 'zone_id',
                    'label' => 'Zone ID',
                    'type' => 'number',
                ],
            ],
            'Free Fire' => [
                [
                    'name' => 'user_id',
                    'label' => 'User ID',
                    'type' => 'number',
                ],
            ],
            'PUBG Mobile' => [
                [
                    'name' => 'user_id',
                    'label' => 'User ID',
                    'type' => 'number',
                ],
                [
                    'name' => 'server',
                    'label' => 'Server',
                    'type' => 'select',
                    'options' => [
                        ['label' => 'Asia', 'value' => 'asia'],
                        ['label' => 'Europe', 'value' => 'europe'],
                        ['label' => 'KRJP', 'value' => 'krjp'],
                    ]
                ],
            ],
            'Genshin Impact' => [
                [
                    'name' => 'user_id',
                    'label' => 'UID',
                    'type' => 'number',
                ],
                [
                    'name' => 'server',
                    'label' => 'Server',
                    'type' => 'select',
                    'options' => [
                        ['label' => 'Asia', 'value' => 'asia'],
                        ['label' => 'America', 'value' => 'america'],
                        ['label' => 'Europe', 'value' => 'europe'],
                        ['label' => 'TW, HK, MO', 'value' => 'tw'],
                    ]
                ],
            ]
        ];

        foreach ($games as $gameName => $fields) {
            $produk = Produk::where('nama', $gameName)->first();
            if (!$produk) {
                // Skip kalau produk belum ada
                continue;
            }

            foreach ($fields as $index => $field) {
                $inputField = ProdukInputField::create([
                    'produk_id' => $produk->id,
                    'name' => $field['name'],
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'required' => true,
                    'order' => $index,
                ]);

                // Insert options jika field type select
                if ($field['type'] === 'select' && isset($field['options'])) {
                    foreach ($field['options'] as $option) {
                        ProdukInputOption::create([
                            'produk_input_field_id' => $inputField->id,
                            'label' => $option['label'],
                            'value' => $option['value'],
                        ]);
                    }
                }
            }
        }
    }
}

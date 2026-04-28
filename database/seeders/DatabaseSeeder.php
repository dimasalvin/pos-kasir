<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Supplier;
use App\Models\Barang;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ──
        User::create([
            'name'     => 'Administrator',
            'email'    => 'admin@kasirpos.com',
            'password' => Hash::make('admin123'),
            'role'     => 'admin',
        ]);

        User::create([
            'name'     => 'Kasir 1',
            'email'    => 'kasir@kasirpos.com',
            'password' => Hash::make('kasir123'),
            'role'     => 'kasir',
        ]);

        // ── Kategori ──
        $hijau = Kategori::create(['nama' => 'Hijau', 'warna' => '#22C55E']);
        $merah = Kategori::create(['nama' => 'Merah', 'warna' => '#EF4444']);
        $biru  = Kategori::create(['nama' => 'Biru',  'warna' => '#3B82F6']);

        // ── Supplier ──
        $supplier1 = Supplier::create([
            'nama'        => 'PT. Kimia Farma',
            'alamat'      => 'Jl. Veteran No. 9, Jakarta',
            'no_telp'     => '021-4208311',
            'jatuh_tempo' => 30,
        ]);

        $supplier2 = Supplier::create([
            'nama'        => 'PT. Kalbe Farma',
            'alamat'      => 'Jl. Let. Jend. Suprapto Kav. 4, Jakarta',
            'no_telp'     => '021-4287 3888',
            'jatuh_tempo' => 45,
        ]);

        $supplier3 = Supplier::create([
            'nama'        => 'PT. Sanbe Farma',
            'alamat'      => 'Jl. Tamansari No. 10, Bandung',
            'no_telp'     => '022-2034567',
            'jatuh_tempo' => 30,
        ]);

        // ── Barang ──
        $barangs = [
            ['kode_barang' => 'BRG001', 'barcode' => '8991234500001', 'nama_barang' => 'Paracetamol 500mg', 'satuan' => 'strip', 'kategori_id' => $hijau->id, 'supplier_id' => $supplier1->id, 'harga_beli' => 3000, 'harga_jual_umum' => 5000, 'harga_jual_resep' => 4500, 'stok' => 100, 'stok_minimum' => 20],
            ['kode_barang' => 'BRG002', 'barcode' => '8991234500002', 'nama_barang' => 'Amoxicillin 500mg', 'satuan' => 'strip', 'kategori_id' => $merah->id, 'supplier_id' => $supplier1->id, 'harga_beli' => 8000, 'harga_jual_umum' => 12000, 'harga_jual_resep' => 10000, 'stok' => 50, 'stok_minimum' => 10],
            ['kode_barang' => 'BRG003', 'barcode' => '8991234500003', 'nama_barang' => 'Vitamin C 1000mg', 'satuan' => 'tablet', 'kategori_id' => $hijau->id, 'supplier_id' => $supplier2->id, 'harga_beli' => 1500, 'harga_jual_umum' => 3000, 'harga_jual_resep' => 2500, 'stok' => 200, 'stok_minimum' => 30],
            ['kode_barang' => 'BRG004', 'barcode' => '8991234500004', 'nama_barang' => 'Omeprazole 20mg', 'satuan' => 'kapsul', 'kategori_id' => $biru->id, 'supplier_id' => $supplier2->id, 'harga_beli' => 5000, 'harga_jual_umum' => 8000, 'harga_jual_resep' => 7000, 'stok' => 80, 'stok_minimum' => 15],
            ['kode_barang' => 'BRG005', 'barcode' => '8991234500005', 'nama_barang' => 'Cetirizine 10mg', 'satuan' => 'strip', 'kategori_id' => $hijau->id, 'supplier_id' => $supplier3->id, 'harga_beli' => 2000, 'harga_jual_umum' => 4000, 'harga_jual_resep' => 3500, 'stok' => 120, 'stok_minimum' => 20],
            ['kode_barang' => 'BRG006', 'barcode' => '8991234500006', 'nama_barang' => 'Metformin 500mg', 'satuan' => 'tablet', 'kategori_id' => $merah->id, 'supplier_id' => $supplier1->id, 'harga_beli' => 4000, 'harga_jual_umum' => 7000, 'harga_jual_resep' => 6000, 'stok' => 60, 'stok_minimum' => 10],
            ['kode_barang' => 'BRG007', 'barcode' => '8991234500007', 'nama_barang' => 'Ibuprofen 400mg', 'satuan' => 'strip', 'kategori_id' => $hijau->id, 'supplier_id' => $supplier3->id, 'harga_beli' => 3500, 'harga_jual_umum' => 6000, 'harga_jual_resep' => 5000, 'stok' => 90, 'stok_minimum' => 15],
            ['kode_barang' => 'BRG008', 'barcode' => '8991234500008', 'nama_barang' => 'Betadine 60ml', 'satuan' => 'botol', 'kategori_id' => $biru->id, 'supplier_id' => $supplier2->id, 'harga_beli' => 15000, 'harga_jual_umum' => 22000, 'harga_jual_resep' => 20000, 'stok' => 40, 'stok_minimum' => 10],
            ['kode_barang' => 'BRG009', 'barcode' => '8991234500009', 'nama_barang' => 'Salbutamol Inhaler', 'satuan' => 'pcs', 'kategori_id' => $merah->id, 'supplier_id' => $supplier1->id, 'harga_beli' => 25000, 'harga_jual_umum' => 40000, 'harga_jual_resep' => 35000, 'stok' => 3, 'stok_minimum' => 5],
            ['kode_barang' => 'BRG010', 'barcode' => '8991234500010', 'nama_barang' => 'Antasida Doen', 'satuan' => 'botol', 'kategori_id' => $hijau->id, 'supplier_id' => $supplier3->id, 'harga_beli' => 8000, 'harga_jual_umum' => 12000, 'harga_jual_resep' => 11000, 'stok' => 2, 'stok_minimum' => 8],
            ['kode_barang' => 'BRG011', 'barcode' => '8991234500011', 'nama_barang' => 'Dexamethasone 0.5mg', 'satuan' => 'strip', 'kategori_id' => $merah->id, 'supplier_id' => $supplier1->id, 'harga_beli' => 2500, 'harga_jual_umum' => 5000, 'harga_jual_resep' => 4000, 'stok' => 70, 'stok_minimum' => 10],
            ['kode_barang' => 'BRG012', 'barcode' => '8991234500012', 'nama_barang' => 'Kasa Steril 16x16', 'satuan' => 'pcs', 'kategori_id' => $biru->id, 'supplier_id' => $supplier2->id, 'harga_beli' => 5000, 'harga_jual_umum' => 8000, 'harga_jual_resep' => 7500, 'stok' => 150, 'stok_minimum' => 20],
        ];

        foreach ($barangs as $b) {
            Barang::create($b);
        }
    }
}

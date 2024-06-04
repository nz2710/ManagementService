<?php

namespace Database\Seeders;

use App\Models\Depot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class DepotsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filePath = 'test1.csv'; // Đường dẫn tới file CSV
        $csv = Reader::createFromPath($filePath, 'r'); // Tạo đối tượng CSV reader
        $csv->setHeaderOffset(0); // Đặt hàng đầu tiên của CSV là header

        $records = $csv->getRecords(); // Lấy danh sách các bản ghi trong CSV
        foreach ($records as $record) {
            Depot::create([
                'name' => $record['title'],
                'address' => $record['address'],
                'longitude' => $record['longitude'], // Check for the typo in your CSV or correct it here
                'latitude' => $record['latitude'],
                'status' => 'Active' // Set status as Active for all entries
            ]);
        }
    }
}

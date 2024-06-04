<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

class OrderAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Path to your CSV file
        $csvPath = 'output3.csv';

        // Load the CSV file
        $csv = Reader::createFromPath($csvPath, 'r');

        // Get all the records from the CSV
        $records = $csv->getRecords();

        // Fetch all order IDs from the database
        $orderIds = DB::table('orders')->pluck('id')->toArray();

        foreach ($records as $index => $record) {
            // Assuming the address is in the first column of the CSV file
            $address = $record[4];

            // Update the orders table sequentially
            if (isset($orderIds[$index])) {
                DB::table('orders')->where('id', $orderIds[$index])->update(['address' => $address]);
            }
        }
    }
}

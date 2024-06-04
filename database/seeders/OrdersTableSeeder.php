<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Partner;
use League\Csv\Reader;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrdersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filePath = 'pr30.txt'; // Đường dẫn tới file trên máy tính của bạn
        $fileContent = file_get_contents($filePath); // Đọc nội dung file
        $lines = explode("\n", $fileContent); // Tách các dòng trong file

        // Đọc dữ liệu từ file output.csv
        $csvPath = 'output3.csv';
        $csv = Reader::createFromPath($csvPath, 'r');
        $csv->setHeaderOffset(0); // Bỏ qua dòng header

        $records = $csv->getRecords(['address']);
        $data = iterator_to_array($records, false);


        // Khởi tạo Faker để tạo dữ liệu giả
        $faker = Faker::create('vi_VN');

        // Lấy tất cả các partner_id từ bảng partners
        $partnerIds = Partner::pluck('id')->all();

        // Lặp qua từng dòng trong file pr30.txt
        foreach ($lines as $index => $line) {
            if (!empty(trim($line))) { // Kiểm tra dòng không trống
                $data = explode(" ", trim($line)); // Sử dụng tab làm dấu phân cách để tách dữ liệu

                // Lấy địa chỉ từ file output.csv
                $address = $data[$index]['address'] ?? '';

                // Tạo số điện thoại và tên khách hàng giả
                $phone = $faker->phoneNumber;
                $customerName = $faker->name;

                // Lấy ngẫu nhiên partner_id từ danh sách partner_id
                $partnerId = $faker->randomElement($partnerIds);

                // Tạo mã đơn hàng bằng phương thức generateCodeOrder
                $order = new Order();
                $codeOrder = $order->generateCodeOrder($partnerId);

                // Tạo thời gian giả từ tháng 1/2024 đến tháng 5/2024
                $createdAt = $faker->dateTimeBetween('2024-01-01', '2024-05-31');

                // Tạo đối tượng Order mới
                Order::create([
                    'longitude' => $data[2],
                    'latitude' => $data[1],
                    'time_service' => $data[3],
                    'mass_of_order' => $data[4],
                    'address' => $address,
                    'phone' => $phone,
                    'customer_name' => $customerName,
                    'partner_id' => $partnerId,
                    'code_order' => $codeOrder,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }
}

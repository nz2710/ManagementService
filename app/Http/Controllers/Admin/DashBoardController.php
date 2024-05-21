<?php

namespace App\Http\Controllers\Admin;

use App\Models\Depot;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class DashBoardController extends Controller
{

    public function getTotalAll()
    {
        $totalProduct = Product::count();
        $vehicle = Vehicle::first(); // Lấy bản ghi đầu tiên
        $totalVehicles = $vehicle ? $vehicle->total_vehicles : 0; // Kiểm tra nếu bản ghi tồn tại
        $totalDepots = Depot::count();
        $totalPartners = Partner::count();
        $totalOrders = Order::count();

        return response()->json([
            'success' => true,
            'message' => 'Total of all',
            'totalProduct' => $totalProduct,
            'totalVehicles' => $totalVehicles,
            'totalDepots' => $totalDepots,
            'totalPartners' => $totalPartners,
            'totalOrders' => $totalOrders
        ]);
    }
    public function getTotalProduct()
    {
        $totalProduct = Product::count();


        return response()->json([
            'success' => true,
            'message' => 'Total of Product',
            'totalProduct' => $totalProduct
        ]);
    }

    public function getTotalVehicles()
    {
        $vehicle = Vehicle::first(); // Lấy bản ghi đầu tiên
        $totalVehicles = $vehicle ? $vehicle->total_vehicles : 0; // Kiểm tra nếu bản ghi tồn tại

        return response()->json([
            'success' => true,
            'message' => 'Total of vehicles',
            'totalVehicles' => $totalVehicles
        ]);
    }


    public function getTotalDepots()
    {
        $totalDepots = Depot::count();

        return response()->json([
            'success' => true,
            'message' => 'Total of depots',
            'totalDepots' => $totalDepots
        ]);
    }
    public function getTotalOrders(Request $request)
    {
        // Lấy tham số 'start_date', 'end_date' và 'status' từ request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');

        // Tạo query cơ bản
        $query = Order::query();

        // Áp dụng bộ lọc theo khoảng thời gian nếu có
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
        }

        // Áp dụng bộ lọc theo trạng thái nếu có
        if ($status) {
            $query->where('status', $status);
        }

        // Đếm tổng số đơn hàng theo các bộ lọc đã áp dụng
        $totalOrders = $query->count();

        // Trả về dữ liệu dưới dạng JSON
        return response()->json([
            'success' => true,
            'message' => 'Total of orders',
            'totalOrders' => $totalOrders
        ]);
    }


    public function getTotalRevenue(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        if ($startDate && $endDate) {
            $totalRevenue = Order::where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate . ' 23:59:59')
                ->sum('price');
        } else {
            $totalRevenue = Order::sum('price');
        }

        return response()->json([
            'success' => true,
            'message' => 'Total of revenue',
            'totalRevenue' => $totalRevenue
        ]);
    }

    public function getTotalPartners(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            $totalPartners = Partner::where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate . ' 23:59:59')
                ->count();
        } else {
            $totalPartners = Partner::count();
        }

        return response()->json([
            'success' => true,
            'message' => 'Total of partners',
            'totalPartners' => $totalPartners
        ]);
    }


    public function getTopPartnersByRevenue(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Tạo query cơ bản lấy đối tác có doanh thu > 0
        $query = Partner::where('revenue', '>', 0);

        // Áp dụng bộ lọc theo khoảng thời gian nếu có
        if ($startDate && $endDate) {
            $query = $query->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
        }

        // Lấy top 5 đối tác có doanh thu cao nhất
        $topPartners = $query->orderByDesc('revenue')
            ->take(5)
            ->get();

        // Trả về kết quả dưới dạng JSON
        return response()->json([
            'success' => true,
            'message' => 'Top 5 partners by revenue',
            'topPartners' => $topPartners
        ]);
    }

    public function getRevenueSummary(Request $request)
    {
        $defaultStartDate = Order::orderBy('created_at', 'asc')->first()->created_at->toDateString();
        $startDate = $request->input('start_date', $defaultStartDate); // Mặc định từ đầu năm nếu không có tham số
        $endDate = $request->input('end_date', Carbon::now()->toDateString()); // Mặc định đến ngày hiện tại nếu không có tham số

        $data = Order::selectRaw('DATE(created_at) as date, SUM(price) as total_revenue')
                     ->whereBetween('created_at', [$startDate . " 00:00:00", $endDate . " 23:59:59"])
                     ->groupBy('date')
                     ->orderBy('date', 'asc')
                     ->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getMonthlyRevenue()
    {
        $startYear = Carbon::parse(Order::min('created_at'))->year;
        $endYear = Carbon::parse(Order::max('created_at'))->year;

        $monthlyRevenue = Order::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(price) as revenue')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($row) {
                // Transform the data to be more frontend friendly
                $date = str_pad($row->month, 2, '0', STR_PAD_LEFT) . '-' . $row->year;
                return ['date' => $date, 'revenue' => $row->revenue];
            });

        return response()->json([
            'success' => true,
            'message' => 'Monthly revenue',
            'monthlyRevenue' => $monthlyRevenue
        ]);
    }
}

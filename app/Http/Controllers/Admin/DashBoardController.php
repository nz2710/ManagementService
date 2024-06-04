<?php

namespace App\Http\Controllers\Admin;

use App\Models\Depot;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Vehicle;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DashBoardController extends Controller
{

    public function getTotalAll()
    {
        $totalProduct = Product::count();
        $totalVehicles = Vehicle::sum('total_vehicles');
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

    // public function getSummaryData(Request $request)
    // {
    //     $year = $request->input('year');
    //     $month = $request->input('month');
    //     $filterType = $request->input('filter_type', 'month');

    //     $query = Order::join('order_product', 'orders.id', '=', 'order_product.order_id')
    //         ->join('products', 'order_product.product_id', '=', 'products.id')
    //         ->selectRaw('SUM(orders.price) as revenues')
    //         ->selectRaw('SUM(order_product.quantity) as items_sold');

    //     if ($filterType === 'year') {
    //         $validator = Validator::make($request->all(), [
    //             'year' => 'required|integer|min:2000|max:' . date('Y'),
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json(['success' => false, 'message' => $validator->errors()], 400);
    //         }
    //         $query->selectRaw('MONTH(orders.created_at) as month')
    //             ->whereYear('orders.created_at', $year)
    //             ->groupBy('month')
    //             ->orderBy('month', 'asc');
    //     } elseif ($filterType === 'month') {
    //         $validator = Validator::make($request->all(), [
    //             'year' => 'required|integer|min:2000|max:' . date('Y'),
    //             'month' => 'required|integer|min:1|max:12',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json(['success' => false, 'message' => $validator->errors()], 400);
    //         }
    //         $query->selectRaw('DATE_FORMAT(orders.created_at, "%d-%m-%Y") as date')
    //             ->whereYear('orders.created_at', $year)
    //             ->whereMonth('orders.created_at', $month)
    //             ->groupBy('date')
    //             ->orderBy('date', 'asc');
    //     }

    //     $data = $query->get();

    //     // $data = $query->orderBy('date', 'asc')->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $data
    //     ]);
    // }


    public function getTopProducts(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');
        $filterType = $request->input('filter_type', 'month');
        $metricType = $request->input('metric_type', 'sale'); // Mặc định là 'revenue'

        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2000|max:' . date('Y'),
            'month' => 'integer|min:1|max:12',
            'metric_type' => 'in:sale,quantity' // Đảm bảo chỉ có 'revenue' hoặc 'amount'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()], 400);
        }

        if ($filterType === 'year') {
            $query = OrderProduct::with('product')
            ->whereYear('created_at', $year)
            ->groupBy('product_id');

            if ($metricType === 'sale') {
                $query->selectRaw('product_id, SUM(price*quantity) as total_metric')
                    ->orderByDesc('total_metric');
            } else {
                $query->selectRaw('partner_id, SUM(quantity) as total_metric')
                    ->orderByDesc('total_metric');
            }

            $data = $query->take(10)->get()->map(function ($item) use ($year) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'total' => $item->total_metric,
                    'year' => $year,
                ];
            });
        } elseif ($filterType === 'month') {
            $startDate = Carbon::create($year, $month)->startOfMonth();
            $endDate = Carbon::create($year, $month)->endOfMonth();

            $query = OrderProduct::with('product')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('product_id');

            if ($metricType === 'sale') {
                $query->selectRaw('product_id, SUM(price*quantity) as total_metric')
                    ->orderByDesc('total_metric');
            } else {
                $query->selectRaw('product_id, SUM(quantity) as total_metric')
                    ->orderByDesc('total_metric');
            }

            $data = $query->take(10)->get()->map(function ($item) use ($year, $month) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'total' => $item->total_metric,
                    'month' => $month,
                    'year' => $year,
                ];
            });
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid filter type'], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    // {
    //     $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
    //     $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

    //     if ($startDate && $endDate) {
    //         $totalRevenue = Order::where('created_at', '>=', $startDate)
    //             ->where('created_at', '<=', $endDate . ' 23:59:59')
    //             ->sum('price');
    //     } else {
    //         $totalRevenue = Order::sum('price');
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Total of revenue',
    //         'totalRevenue' => $totalRevenue
    //     ]);
    // }

    // public function getTotalPartners(Request $request)
    // {
    //     $startDate = $request->input('start_date');
    //     $endDate = $request->input('end_date');

    //     if ($startDate && $endDate) {
    //         $totalPartners = Partner::where('created_at', '>=', $startDate)
    //             ->where('created_at', '<=', $endDate . ' 23:59:59')
    //             ->count();
    //     } else {
    //         $totalPartners = Partner::count();
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Total of partners',
    //         'totalPartners' => $totalPartners
    //     ]);
    // }
    public function getTopPartners(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');
        $filterType = $request->input('filter_type', 'month');
        $metricType = $request->input('metric_type', 'revenue'); // Mặc định là 'revenue'

        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2000|max:' . date('Y'),
            'month' => 'integer|min:1|max:12',
            'metric_type' => 'in:revenue,amount' // Đảm bảo chỉ có 'revenue' hoặc 'amount'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()], 400);
        }

        if ($filterType === 'year') {
            $query = Order::with('partner')
                ->whereYear('created_at', $year)
                ->groupBy('partner_id');

            if ($metricType === 'revenue') {
                $query->selectRaw('partner_id, SUM(price) as total_metric')
                    ->orderByDesc('total_metric');
            } else {
                $query->selectRaw('partner_id, COUNT(*) as total_metric')
                    ->orderByDesc('total_metric');
            }

            $data = $query->take(10)->get()->map(function ($item) use ($year) {
                return [
                    'partner_id' => $item->partner_id,
                    'partner_name' => $item->partner->name,
                    'total' => $item->total_metric,
                    'year' => $year,
                ];
            });
        } elseif ($filterType === 'month') {
            $startDate = Carbon::create($year, $month)->startOfMonth();
            $endDate = Carbon::create($year, $month)->endOfMonth();

            $query = Order::with('partner')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('partner_id');

            if ($metricType === 'revenue') {
                $query->selectRaw('partner_id, SUM(price) as total_metric')
                    ->orderByDesc('total_metric');
            } else {
                $query->selectRaw('partner_id, COUNT(*) as total_metric')
                    ->orderByDesc('total_metric');
            }

            $data = $query->take(10)->get()->map(function ($item) use ($year, $month) {
                return [
                    'partner_id' => $item->partner_id,
                    'partner_name' => $item->partner->name,
                    'total' => $item->total_metric,
                    'month' => $month,
                    'year' => $year,
                ];
            });
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid filter type'], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }


    public function getRevenueSummary(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');
        $filterType = $request->input('filter_type', 'month');

        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2000|max:' . date('Y'),
            'month' => 'integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()], 400);
        }

        if ($filterType === 'year') {
            $data = collect(range(1, 12))->map(function ($month) use ($year) {
                $revenue = Order::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->sum('price');

                return [
                    'month' => $month,
                    'revenue' => $revenue
                ];
            });
        } elseif ($filterType === 'month') {
            $startDate = Carbon::create($year, $month)->startOfMonth();
            $endDate = Carbon::create($year, $month)->endOfMonth();
            $daysInMonth = $endDate->diffInDays($startDate) + 1;

            $data = collect(range(1, $daysInMonth))->map(function ($day) use ($year, $month) {
                $date = Carbon::create($year, $month, $day)->format('d-m-Y');
                $revenue = Order::whereDate('created_at', Carbon::create($year, $month, $day))
                    ->sum('price');

                return [
                    'date' => $date,
                    'revenue' => $revenue
                ];
            });
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid filter type'], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getItemSoldSummary(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');
        $filterType = $request->input('filter_type', 'month');

        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2000|max:' . date('Y'),
            'month' => 'integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()], 400);
        }

        if ($filterType === 'year') {
            $data = collect(range(1, 12))->map(function ($month) use ($year) {
                $itemSold = OrderProduct::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->sum('quantity');

                return [
                    'month' => $month,
                    'item sold' => $itemSold
                ];
            });
        } elseif ($filterType === 'month') {
            $startDate = Carbon::create($year, $month)->startOfMonth();
            $endDate = Carbon::create($year, $month)->endOfMonth();
            $daysInMonth = $endDate->diffInDays($startDate) + 1;

            $data = collect(range(1, $daysInMonth))->map(function ($day) use ($year, $month) {
                $date = Carbon::create($year, $month, $day)->format('d-m-Y');
                $itemSold = OrderProduct::whereDate('created_at', Carbon::create($year, $month, $day))
                    ->sum('quantity');

                return [
                    'date' => $date,
                    'item sold' => $itemSold
                ];
            });
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid filter type'], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getCostSummary(Request $request)
    {
        $year = $request->input('year');
        $month = $request->input('month');
        $filterType = $request->input('filter_type', 'month');

        $validator = Validator::make($request->all(), [
            'year' => 'required|integer|min:2000|max:' . date('Y'),
            'month' => 'integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()], 400);
        }

        if ($filterType === 'year') {
            $data = collect(range(1, 12))->map(function ($month) use ($year) {
                $cost = OrderProduct::whereYear('order_product.created_at', $year)
                    ->whereMonth('order_product.created_at', $month)
                    ->join('products', 'order_product.product_id', '=', 'products.id')
                    // ->join('orders', 'order_product.order_id', '=', 'orders.id')
                    ->selectRaw('SUM(order_product.quantity * products.cost) as total_cost')
                    ->value('total_cost');

                return [
                    'month' => $month,
                    'cost' => $cost,
                ];
            });
        } elseif ($filterType === 'month') {
            $startDate = Carbon::create($year, $month)->startOfMonth();
            $endDate = Carbon::create($year, $month)->endOfMonth();
            $daysInMonth = $endDate->diffInDays($startDate) + 1;

            $data = collect(range(1, $daysInMonth))->map(function ($day) use ($year, $month) {
                $date = Carbon::create($year, $month, $day)->format('d-m-Y');

                $cost = OrderProduct::whereDate('order_product.created_at', Carbon::create($year, $month, $day))
                    ->join('products', 'order_product.product_id', '=', 'products.id')
                    ->join('orders', 'order_product.order_id', '=', 'orders.id')
                    ->selectRaw('SUM(order_product.quantity * products.cost) as total_cost')
                    ->value('total_cost');

                return [
                    'date' => $date,
                    'cost' => $cost,
                ];
            });
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid filter type'], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    // public function getTotalProduct()
    // {
    //     $totalProduct = Product::count();


    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Total of Product',
    //         'totalProduct' => $totalProduct
    //     ]);
    // }

    // public function getTotalVehicles()
    // {
    //     $vehicle = Vehicle::first(); // Lấy bản ghi đầu tiên
    //     $totalVehicles = $vehicle ? $vehicle->total_vehicles : 0; // Kiểm tra nếu bản ghi tồn tại

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Total of vehicles',
    //         'totalVehicles' => $totalVehicles
    //     ]);
    // }


    // public function getTotalDepots()
    // {
    //     $totalDepots = Depot::count();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Total of depots',
    //         'totalDepots' => $totalDepots
    //     ]);
    // }

    // public function getTotalOrders(Request $request)
    // {
    //     // Lấy tham số 'start_date', 'end_date' và 'status' từ request
    //     $startDate = $request->input('start_date');
    //     $endDate = $request->input('end_date');
    //     $status = $request->input('status');

    //     // Tạo query cơ bản
    //     $query = Order::query();

    //     // Áp dụng bộ lọc theo khoảng thời gian nếu có
    //     if ($startDate && $endDate) {
    //         $query->whereBetween('created_at', [$startDate, $endDate . ' 23:59:59']);
    //     }

    //     // Áp dụng bộ lọc theo trạng thái nếu có
    //     if ($status) {
    //         $query->where('status', $status);
    //     }

    //     // Đếm tổng số đơn hàng theo các bộ lọc đã áp dụng
    //     $totalOrders = $query->count();

    //     // Trả về dữ liệu dưới dạng JSON
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Total of orders',
    //         'totalOrders' => $totalOrders
    //     ]);
    // }


    // public function getTotalRevenue(Request $request)
    // {
    //     $startYear = Carbon::parse(Order::min('created_at'))->year;
    //     $endYear = Carbon::parse(Order::max('created_at'))->year;

    //     $monthlyRevenue = Order::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(price) as revenue')
    //         ->groupBy('year', 'month')
    //         ->orderBy('year', 'asc')
    //         ->orderBy('month', 'asc')
    //         ->get()
    //         ->map(function ($row) {
    //             // Transform the data to be more frontend friendly
    //             $date = str_pad($row->month, 2, '0', STR_PAD_LEFT) . '-' . $row->year;
    //             return ['date' => $date, 'revenue' => $row->revenue];
    //         });

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Monthly revenue',
    //         'monthlyRevenue' => $monthlyRevenue
    //     ]);
    // }
}

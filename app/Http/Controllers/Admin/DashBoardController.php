<?php

namespace App\Http\Controllers\Admin;

use App\Models\Depot;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class DashBoardController extends Controller
{
    public function getTotalOrders()
    {
        $totalOrders = Order::count();

        return response()->json([
            'success' => true,
            'message' => 'Total of orders',
            'totalOrders' => $totalOrders]);
    }

    public function getTotalRevenue()
    {
        $totalRevenue = Order::sum('price');

        return response()->json(['totalRevenue' => $totalRevenue]);
    }
    public function getTotalVehicles()
    {
        $totalVehicles = Vehicle::count();

        return response()->json([
            'success' => true,
            'message' => 'Total of vehicles',
            'totalVehicles' => $totalVehicles]);
    }

    public function getTotalDepots()
    {
        $totalDepots = Depot::count();

        return response()->json([
            'success' => true,
            'message' => 'Total of depots',
            'totalDepots' => $totalDepots]);
    }

    public function getTotalPartners()
    {
        $totalPartners = Partner::count();

        return response()->json([
            'success' => true,
            'message' => 'Total of partners',
            'totalPartners' => $totalPartners]);
    }

    public function getTopPartnersByRevenue()
    {
        $topPartners = Partner::where('revenue', '>', 0)
        ->orderByDesc('revenue')
        ->take(5)
        ->get();
        return response()->json([
            'success' => true,
            'message' => 'Top 5 partners by revenue',
            'topPartners' => $topPartners
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
            ->mapWithKeys(function ($row) {
                // Transform the data to be more frontend friendly
                $date = str_pad($row->month, 2, '0', STR_PAD_LEFT) . '-' . $row->year;
                return [$date => $row->revenue];
            });

        $allMonths = collect();
        foreach (range($startYear, $endYear) as $year) {
            foreach (range(1, 12) as $month) {
                $date = str_pad($month, 2, '0', STR_PAD_LEFT) . '-' .$year ;
                $allMonths->put($date, 0);
            }
        }

        $monthlyRevenue = $allMonths->merge($monthlyRevenue);

        return response()->json([
            'success' => true,
            'message' => 'Monthly revenue',
            'monthlyRevenue' => $monthlyRevenue
        ]);
    }
}

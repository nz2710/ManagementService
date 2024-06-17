<?php

namespace App\Http\Controllers\Admin;

use App\Models\Plan;
use App\Models\Depot;
use App\Models\Order;
use App\Models\Route;
use GuzzleHttp\Client;
use App\Models\Partner;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class RoutingController extends Controller
{
    public function generateFile(Request $request)
    {
        $validatedData = $request->validate([
            'num_vehicles' => 'nullable|integer',
            'time_limit' => 'nullable|numeric',
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);
        $selectedVehicleId = $validatedData['vehicle_id'];
        $selectedVehicle = Vehicle::find($selectedVehicleId);
        $numVehicles = $validatedData['num_vehicles'] ?? $selectedVehicle->total_vehicles;
        $numOrders = Order::where('status', 'pending')->count();
        $numDepots = Depot::where('status', 'Active')->count();
        $timeLimit = $validatedData['time_limit'] ?? 999999;
        $weightLimit = $selectedVehicle->capacity;
        $VValue = $selectedVehicle->speed;

        $fileContent = "6 $numVehicles $numOrders $numDepots\n";
        $fileContent .= str_repeat("$timeLimit $weightLimit\n", $numDepots);

        $orders = Order::where('status', 'pending')
            ->select('id', 'longitude', 'latitude', 'time_service', 'mass_of_order')
            ->get();

        $depots = Depot::where('status', 'Active')
            ->select('id', 'latitude', 'longitude')
            ->get();

        $index = 1;
        foreach ($orders as $order) {
            $fileContent .= "$index {$order->latitude} {$order->longitude} {$order->time_service} {$order->mass_of_order} {$order->id}\n";
            $index++;
        }

        foreach ($depots as $depot) {
            $fileContent .= "$index {$depot->latitude} {$depot->longitude} 0 0 {$depot->id}\n";
            $index++;
        }

        $filename = 'mdvrp_' . now()->format('Y-m-d_His') . '.txt';
        $filePath = 'routing/' . $filename;
        Storage::put($filePath, $fileContent);

        $responseData = $this->processFile($filePath, $filename, $VValue, $selectedVehicleId);

        return response()->json($responseData);
    }

    public function processFile($filePath, $filename, $VValue, $selectedVehicleId)
    {
        try {
            $client = new Client();
            $response = $client->post('http://192.168.100.4:8032/mvrp', [
                'multipart' => [
                    [
                        'name' => 'mvrpFile',
                        'contents' => Storage::get($filePath),
                        'filename' => $filename
                    ],
                    [
                        'name' => 'V',
                        'contents' => $VValue
                    ]
                ]
            ]);

            $responseData = json_decode($response->getBody(), true);
            // Lưu trữ dữ liệu vào cơ sở dữ liệu
            DB::transaction(function () use ($responseData, $selectedVehicleId) {
                // Lưu thông tin kế hoạch giao hàng vào bảng plans
                $plan = Plan::create([
                    'name' => 'Delivery Plan' . ' ' . now()->format('Y-m-d H:i:s'),
                    'total_demand' => $responseData['total_demand_served'],
                    'total_distance' => $responseData['total_distance_served'],
                    'total_time_serving' => $responseData['total_time_serving_served'],
                    'total_demand_without_allocating_vehicles' => $responseData['total_demand_without_allocating_vehicles'],
                    'total_distance_without_allocating_vehicles' => $responseData['total_distance_without_allocating_vehicles'],
                    'total_time_serving_without_allocating_vehicles' => $responseData['total_time_serving_without_allocating_vehicles'],
                    'status' => 'pending',
                    'total_vehicle_used' => $responseData['total_vehicle_used'],
                    'total_num_customer_served' => $responseData['total_num_customer_served'],
                    'total_num_customer_not_served' => $responseData['total_num_customer_not_served'],
                ]);

                // Lấy thông tin của vehicle được chọn
                $vehicle = Vehicle::find($selectedVehicleId);
                $fuelCost = $vehicle->fuel_cost;
                $fuelConsumption = $vehicle->fuel_consumption;

                // Tính toán fee dựa trên công thức
                $fee = $fuelCost * $fuelConsumption * $responseData['total_distance_served'];

                // Lưu fee vào bảng plans
                $plan->fee = $fee;
                $plan->save();

                // Lưu thông tin các tuyến đường vào bảng routes
                foreach ($responseData['route_served_List_return_id'] as $route) {
                    $depotId = null;
                    foreach ($route['route'] as $point) {
                        if (strpos($point, 'depot_') === 0) {
                            $depotId = substr($point, 6);
                            break;
                        }
                    }

                    Route::create([
                        'plan_id' => $plan->id,
                        'route' => json_encode(array_slice($route['route'], 1, -1)),
                        'total_demand' => $route['total_demand'],
                        'total_distance' => $route['total_distance'],
                        'total_time_serving' => $route['total_time_serving'],
                        'depot_id' => $depotId,
                        'is_served' => true,
                    ]);
                }

                // Lưu thông tin các tuyến đường không được phục vụ vào bảng routes
                foreach ($responseData['route_not_served_List_return_id'] as $route) {
                    Route::create([
                        'plan_id' => $plan->id,
                        'route' => json_encode(array_slice($route['route'], 1, -1)),
                        'total_demand' => 0,
                        'total_distance' => 0,
                        'total_time_serving' => 0,
                        'depot_id' => $depotId,
                        'is_served' => false,
                    ]);
                }
            });
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Log the error or handle it accordingly
            return response()->json(['error' => 'Unable to process the request'], 500);
        }

        return $responseData;
    }

    public function index(Request $request)
    {
        $name = $request->input('name');
        $orderBy = $request->input('order_by', 'id');
        $sortBy = $request->input('sort_by', 'asc');

        $plan = Plan::orderBy($orderBy, $sortBy);

        if ($name) {
            $plan = $plan->where('name', 'like', '%' . $name . '%');
        }

        $plan = $plan->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'List of all plans',
            'data' => $plan
        ]);
    }

    public function destroy($id)
    {
        $plan = Plan::find($id);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found'
            ], 404);
        }

        $plan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Plan deleted successfully'
        ]);
    }

    public function show(Request $request, $id)
    {
        $perPage = $request->input('pageSize', 10);
        $page = $request->input('page', 1);

        $plan = Plan::with('routes')->find($id);

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found'
            ], 404);
        }

        $routes = $plan->routes()->with('depot')->paginate($perPage, ['*'], 'page', $page);

        $planData = [
            'id' => $plan->id,
            'name' => $plan->name,
            'total_demand' => $plan->total_demand,
            'total_distance' => $plan->total_distance,
            'total_time_serving' => $plan->total_time_serving,
            'total_demand_without_allocating_vehicles' => $plan->total_demand_without_allocating_vehicles,
            'total_distance_without_allocating_vehicles' => $plan->total_distance_without_allocating_vehicles,
            'total_time_serving_without_allocating_vehicles' => $plan->total_time_serving_without_allocating_vehicles,
            'status' => $plan->status,
            'fee' => $plan->fee,
            'total_vehicle_used' => $plan->total_vehicle_used,
            'total_num_customer_served' => $plan->total_num_customer_served,
            'total_num_customer_not_served' => $plan->total_num_customer_not_served,
            'created_at' => $plan->created_at,
            'updated_at' => $plan->updated_at,
            'total_routes' => $routes->total(),
            'routes' => [],

        ];

        foreach ($routes as $route) {
            $routeData = [
                'id' => $route->id,
                'route' => json_decode($route->route),
                'total_demand' => $route->total_demand,
                'total_distance' => $route->total_distance,
                'total_time_serving' => $route->total_time_serving,
                'depot_id' => $route->depot_id,
                'depot_name' => $route->depot->name, // Thêm trường depot_name
                'is_served' => $route->is_served
            ];

            $planData['routes'][] = $routeData;
        }

        return response()->json([
            'success' => true,
            'message' => 'Plan details',
            'data' => $planData,
            'current_page' => $routes->currentPage(),
            'last_page' => $routes->lastPage(),

        ]);
    }

    public function showRoute($routeId)
    {
        $route = Route::with('depot')->find($routeId);
        if (!$route) {
            return response()->json([
                'success' => false,
                'message' => 'Route not found.'
            ], 404);
        }

        $orderIds = json_decode($route->route, true);
        $orders = Order::whereIn('id', $orderIds)
            ->get(['id', 'customer_name', 'address', 'longitude', 'latitude']);

        $routeData = [
            'id' => $route->id,
            'plan_id' => $route->plan_id,
            'depot_id' => $route->depot_id,
            'depot_name' => $route->depot->name,
            'route' => [],
            'total_demand' => $route->total_demand,
            'total_distance' => $route->total_distance,
            'total_time_serving' => $route->total_time_serving,
            'is_served' => $route->is_served,
            'created_at' => $route->created_at,
            'updated_at' => $route->updated_at,
        ];

        foreach ($orderIds as $orderId) {
            $order = $orders->firstWhere('id', $orderId);
            if ($order) {
                $routeData['route'][] = [
                    'id' => $order->id,
                    'customer_name' => $order->customer_name,
                    'address' => $order->address,
                    'longitude' => $order->longitude,
                    'latitude' => $order->latitude
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Route found.',
            'data' => $routeData
        ]);
    }
}

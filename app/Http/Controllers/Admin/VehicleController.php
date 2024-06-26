<?php

namespace App\Http\Controllers\Admin;

use App\Models\Vehicle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $name = $request->input('name');
        // $driver_name = $request->input('driver_name');
        // $vehicle_type = $request->input('vehicle_type');
        $orderBy = $request->input('order_by', 'id');
        $sortBy = $request->input('sort_by', 'asc');

        $vehicle = Vehicle::orderBy($orderBy, $sortBy);

        if ($name) {
            $vehicle = $vehicle->where('name', 'like', '%' . $name . '%');
        }

        // if ($driver_name) {
        //     $vehicle = $vehicle->where('driver_name', 'like', '%' . $driver_name . '%');
        // }

        // if ($vehicle_type) {
        //     $vehicle = $vehicle->where('vehicle_type', 'like', '%' . $vehicle_type . '%');
        // }

        $vehicle = $vehicle->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'List of all fleets',
            'data' => $vehicle
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'capacity' => 'required|numeric',
            'speed' => 'required|numeric',
            'total_vehicles' => 'required|numeric',
            'fuel_consumption' => 'required|numeric',
            'fuel_cost' => 'required|numeric'
        ]);
        $vehicle = new Vehicle();
        $vehicle->name = $request->name;
        $vehicle->capacity = $request->capacity;
        $vehicle->speed = $request->speed;
        $vehicle->fuel_consumption = $request->fuel_consumption;
        $vehicle->fuel_cost = $request->fuel_cost;
        $vehicle->total_vehicles = $request->total_vehicles;
        $vehicle->save();
        return response()->json([
            'success' => true,
            'message' => 'Vehicle created successfully',
            'data' => $vehicle
        ]);
    }
    public function show($id)
    {
        $vehicle = Vehicle::find($id);
        if ($vehicle) {
            return response()->json([
                'success' => true,
                'message' => 'Vehicle found',
                'data' => $vehicle
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found',
                'data' => null
            ]);
        }
    }
    public function update(Request $request, $id)
    {
        // $request->validate([
        //     'name' => 'sometimes|required|max:255',
        //     'capacity' => 'sometimes|required|numeric',
        //     'velocity' => 'sometimes|required|numeric',
        //     'status' => 'sometimes|required|boolean',
        // ]);
        $vehicle = Vehicle::find($id);
        if ($vehicle) {
            $vehicle->name = $request->name ?? $vehicle->name;
            $vehicle->capacity = $request->capacity ?? $vehicle->capacity;
            $vehicle->speed = $request->speed ?? $vehicle->speed;
            $vehicle->fuel_consumption = $request->fuel_consumption ?? $vehicle->fuel_consumption;
            $vehicle->fuel_cost = $request->fuel_cost ?? $vehicle->fuel_cost;
            $vehicle->total_vehicles = $request->total_vehicles ?? $vehicle->total_vehicles;
            $vehicle->status = $request->status ?? $vehicle->status;

            $vehicle->save();
            return response()->json([
                'success' => true,
                'message' => 'Vehicle updated successfully',
                'data' => $vehicle
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found',
                'data' => null
            ]);
        }
    }
    public function destroy($id)
    {
        $vehicle = Vehicle::find($id);
        if ($vehicle) {
            $vehicle->delete();
            return response()->json([
                'success' => true,
                'message' => 'Vehicle deleted successfully',
                'data' => $vehicle
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found',
                'data' => null
            ]);
        }
    }
}

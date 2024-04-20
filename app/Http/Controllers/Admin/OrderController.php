<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    protected $apiKey = 'pk.eyJ1IjoibmdvZHVuZzI3MTAiLCJhIjoiY2x2MjF1eTQxMGR4NjJsbWlsMWZmZHluYiJ9.zBLJ9oWBuSXllU5S0zsS2Q';

    public function index(Request $request)
    {
        $code_order = $request->input('code_order');
        $customer_name = $request->input('customer_name');
        $partner_name = $request->input('partner_name');
        $address = $request->input('address');
        $orderBy = $request->input('order_by', 'id');
		$sortBy = $request->input('sort_by', 'asc');

        $order = Order::orderBy($orderBy, $sortBy);

        if ($customer_name) {
            $order = $order->where('customer_name', 'like', '%' . $customer_name . '%');
        }

        if ($address) {
            $order = $order->where('address', 'like', '%' . $address . '%');
        }

        if ($code_order) {
            $order = $order->where('code_order', 'like', '%' . $code_order . '%');
        }

        if ($partner_name) {
            $order = $order->whereHas('partner', function ($query) use ($partner_name) {
                $query->where('name', 'like', '%' . $partner_name . '%');
            });
        }

        $order=$order->with('partner')->paginate(10);


        return response()->json([
            'success' => true,
            'message' => 'List of all partners',
            'data' => $order->map(function ($order) {
                return [
                    'id' => $order->id,
                    'code_order' => $order->code_order,
                    'partner_name' => $order->partner->name, // Access the partner's name through the relationship
                    'customer_name' => $order->customer_name,
                    'price' => $order->price,
                    'mass_of_order' => $order->mass_of_order,
                    'address' => $order->address,
                    'longitude' => $order->longitude,
                    'latitude' => $order->latitude,
                    'time_open' => $order->time_open,
                    'time_close' => $order->time_close,
                    'time_service' => $order->time_service,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $order->updated_at->format('Y-m-d H:i:s'),
                    // Include any other order attributes you want to return here...
                ];
            }),
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'partner_id' => 'required|exists:partners,id',
            'customer_name' => 'required|string|max:255',
            'price' => 'required',
            'mass_of_order' => 'required',
            'time_service' => 'required|numeric',
        ]);
        $client = new Client();
        $address = $request->address;
        $response = $client->get("https://api.mapbox.com/geocoding/v5/mapbox.places/$address.json?access_token=$this->apiKey");
        $responseBody = json_decode($response->getBody(), true);

        if (empty($responseBody['features'])) {
            return response()->json([
                'success' => false,
                'message' => 'Address does not exist',
            ], 400);
        }

        $coordinates = $responseBody['features'][0]['geometry']['coordinates'];
        $order = new Order();
        $order->code_order = 'ORD-' . Str::uuid();
        $order->partner_id = $request->partner_id;
        $order->customer_name = $request->customer_name;
        $order->price = $request->price;
        $order->mass_of_order = $request->mass_of_order;
        $order->address = $request->address;
        $order->longitude = $coordinates[0];
        $order->latitude = $coordinates[1];
        $order->time_service = $request->time_service;
        $order->save();
        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => $order
        ]);
    }
    public function show($id)
    {
        $order = Order::with('partner')->find($id);
        if ($order) {
            return response()->json([
                'success' => true,
                'message' => 'Order found',
                'data' => [
                        'id' => $order->id,
                        'code_order' => $order->code_order,
                        'partner_name' => $order->partner->name, // Access the partner's name through the relationship
                        'customer_name' => $order->customer_name,
                        'price' => $order->price,
                        'mass_of_order' => $order->mass_of_order,
                        'address' => $order->address,
                        'longitude' => $order->longitude,
                        'latitude' => $order->latitude,
                        'time_open' => $order->time_open,
                        'time_close' => $order->time_close,
                        'time_service' => $order->time_service,
                        'status' => $order->status,
                        'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $order->updated_at->format('Y-m-d H:i:s'),
                        // Include any other order attributes you want to return here...
                    ]
            ]);

        } else {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
        }
    }
    public function update(Request $request, $id)
    {
        $order = Order::with('partner')->find($id);
        if ($order) {
            $client = new Client();
            $address = $request->address ?? $order->address;
            $response = $client->get("https://api.mapbox.com/geocoding/v5/mapbox.places/$address.json?access_token=$this->apiKey");
            $responseBody = json_decode($response->getBody(), true);

            if (empty($responseBody['features'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Address does not exist',
                ], 400);
            }

            $coordinates = $responseBody['features'][0]['geometry']['coordinates'];
            $order->partner_id = $request->partner_id ?? $order->partner_id;
            $order->customer_name = $request->customer_name ?? $order->customer_name;
            $order->price = $request->price ?? $order->price;
            $order->mass_of_order = $request->mass_of_order ?? $order->mass_of_order;
            $order->address = $request->address ?? $order->address;
            $order->longitude = $coordinates[0];
            $order->latitude = $coordinates[1];
            $order->time_open = $request->time_open ?? $order->time_open;
            $order->time_close = $request->time_close ?? $order->time_close;
            $order->time_service = $request->time_service ?? $order->time_service;
            $order->status = $request->status ?? $order->status;
            $order->save();
            $order->load('partner');
            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => [
                    'id' => $order->id,
                    'code_order' => $order->code_order,
                    'partner_name' => $order->partner->name, // Access the partner's name through the relationship
                    'customer_name' => $order->customer_name,
                    'price' => $order->price,
                    'mass_of_order' => $order->mass_of_order,
                    'address' => $order->address,
                    'longitude' => $order->longitude,
                    'latitude' => $order->latitude,
                    'time_open' => $order->time_open,
                    'time_close' => $order->time_close,
                    'time_service' => $order->time_service,
                    'status' => $order->status,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $order->updated_at->format('Y-m-d H:i:s'),
                    // Include any other order attributes you want to return here...
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
        }
    }
    public function destroy($id)
    {
        $order = Order::find($id);
        if ($order) {
            $order->delete();
            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully',
                'data' => $order
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'data' => null
            ]);
        }
    }
}

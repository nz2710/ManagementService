<?php

namespace App\Http\Controllers\Admin;

use App\Models\Depot;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class DepotController extends Controller
{
    protected $apiKey = 'pk.eyJ1IjoibmdvZHVuZzI3MTAiLCJhIjoiY2x2MjF1eTQxMGR4NjJsbWlsMWZmZHluYiJ9.zBLJ9oWBuSXllU5S0zsS2Q';
    public function index(Request $request)
    {
        $name = $request->input('name');
        $address = $request->input('address');
        $orderBy = $request->input('order_by', 'id');
		$sortBy = $request->input('sort_by', 'asc');

        $depot = Depot::orderBy($orderBy, $sortBy);

        if ($name) {
            $depot = $depot->where('name', 'like', '%' . $name . '%');
        }

        if ($address) {
            $depot = $depot->where('address', 'like', '%' . $address . '%');
        }

        $depot=$depot->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'List of all depots',
            'data' => $depot
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255|unique:depots',
            'address' => 'required|max:255|unique:depots',
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
        $depot = new Depot();
        $depot->address = $request->address;
        $depot->longitude = $coordinates[0];
        $depot->latitude = $coordinates[1];
        $depot->name = $request->name;
        $depot->save();

        return response()->json([
            'success' => true,
            'message' => 'Depot created successfully',
            'data' => $depot
        ]);
    }

    public function show($id)
    {
        $depot = Depot::find($id);
        if ($depot) {
            return response()->json([
                'success' => true,
                'message' => 'Depot found',
                'data' => $depot
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Depot not found',
                'data' => null
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $depot = Depot::find($id);
        if ($depot) {
            $client = new Client();
            $address = $request->address ?? $depot->address;
            $response = $client->get("https://api.mapbox.com/geocoding/v5/mapbox.places/$address.json?access_token=$this->apiKey");
            $responseBody = json_decode($response->getBody(), true);

            if (empty($responseBody['features'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Address does not exist',
                ], 400);
            }

            $coordinates = $responseBody['features'][0]['geometry']['coordinates'];
            $depot->address = $address;
            $depot->longitude = $coordinates[0];
            $depot->latitude = $coordinates[1];
            $depot->name = $request->name ?? $depot->name;
            $depot->status = $request->status ?? $depot->status;
            $depot->save();
            return response()->json([
                'success' => true,
                'message' => 'Depot updated successfully',
                'data' => $depot
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Depot not found',
                'data' => null
            ]);
        }
    }

    public function destroy($id)
    {
        $depot = Depot::find($id);
        if ($depot) {
            $depot->delete();
            return response()->json([
                'success' => true,
                'message' => 'Depot deleted successfully',
                'data' => $depot
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Depot not found',
                'data' => null
            ]);
        }
    }
}

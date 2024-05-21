<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use GuzzleHttp\Client;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Services\PartnerService;
use App\Services\ProductService;
use App\Http\Controllers\Controller;
use App\Rules\ValidateProductQuantityPrice;

class OrderController extends Controller
{
    protected $apiKey = 'pk.eyJ1IjoibmdvZHVuZzI3MTAiLCJhIjoiY2x2MjF1eTQxMGR4NjJsbWlsMWZmZHluYiJ9.zBLJ9oWBuSXllU5S0zsS2Q';

    protected $productService;
    protected $partnerService;
    protected $orderService;

    public function __construct(ProductService $productService, PartnerService $partnerService, OrderService $orderService)
    {
        $this->productService = $productService;
        $this->partnerService = $partnerService;
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $code_order = $request->input('code_order');
        $customer_name = $request->input('customer_name');
        $partner_name = $request->input('partner_name');
        $status = $request->input('status');
        $phone = $request->input('phone');
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

        if ($phone) {
            $order = $order->where('phone', 'like', '%' . $phone . '%');
        }
        if ($code_order) {
            $order = $order->where('code_order', 'like', '%' . $code_order . '%');
        }

        if ($partner_name) {
            $order = $order->whereHas('partner', function ($query) use ($partner_name) {
                $query->where('name', 'like', '%' . $partner_name . '%');
            });
        }

        if ($status) {
            $order = $order->where('status', $status);
        }

        $order = $order->with('partner')->paginate(10);


        return response()->json([
            'success' => true,
            'message' => 'List of all partners',
            'data' => $order
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string',
            'partner_id' => 'required|exists:partners,id',
            'customer_name' => 'required|string|max:255',
            'phone'=>'required|string',
            'mass_of_order' => 'required',
            'time_service' => 'required|numeric',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.price' => ['required', 'numeric', 'min:0'],
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
        $order->code_order = $order->generateCodeOrder($request->partner_id);
        $order->partner_id = $request->partner_id;
        $order->customer_name = $request->customer_name;
        $order->phone = $request->phone;
        $order->mass_of_order = $request->mass_of_order;
        $order->address = $request->address;
        $order->longitude = $coordinates[0];
        $order->latitude = $coordinates[1];
        $order->time_service = $request->time_service;
        $order->discount = $order->partner->discount;
        $order->save();

        // Kiểm tra số lượng, giá và trạng thái sản phẩm trước khi thêm vào đơn hàng
        foreach ($request->products as $product) {
            $productModel = Product::findOrFail($product['id']);

            if ($product['quantity'] > $productModel->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số lượng sản phẩm vượt quá số lượng trong kho',
                    'product_id' => $product['id']
                ], 400);
            }

            if ($product['price'] < $productModel->price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giá sản phẩm không hợp lệ',
                    'product_id' => $product['id']
                ], 400);
            }

            if ($productModel->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không ở trạng thái active',
                    'product_id' => $product['id']
                ], 400);
            }
        }

        foreach ($request->products as $product) {
            $order->products()->attach($product['id'], [
                'quantity' => $product['quantity'],
                'price' => $product['price']
            ]);

            $productModel = Product::findOrFail($product['id']);
            $this->productService->updateProductQuantity($productModel, $product['quantity']);
        }

        $order->price = $order->calculateTotalPrice();
        $order->save();

        $this->partnerService->updatePartnerOnNewOrder($order->partner, $order->price);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => $order->load('products')
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
    // public function update(Request $request, $id)
    // {
    //     $order = Order::with('partner')->find($id);
    //     if ($order) {
    //         $client = new Client();
    //         $address = $request->address ?? $order->address;
    //         $response = $client->get("https://api.mapbox.com/geocoding/v5/mapbox.places/$address.json?access_token=$this->apiKey");
    //         $responseBody = json_decode($response->getBody(), true);

    //         if (empty($responseBody['features'])) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Address does not exist',
    //             ], 400);
    //         }

    //         $coordinates = $responseBody['features'][0]['geometry']['coordinates'];
    //         $order->partner_id = $request->partner_id ?? $order->partner_id;
    //         $order->customer_name = $request->customer_name ?? $order->customer_name;
    //         $order->price = $request->price ?? $order->price;
    //         $order->mass_of_order = $request->mass_of_order ?? $order->mass_of_order;
    //         $order->address = $request->address ?? $order->address;
    //         $order->longitude = $coordinates[0];
    //         $order->latitude = $coordinates[1];
    //         $order->time_open = $request->time_open ?? $order->time_open;
    //         $order->time_close = $request->time_close ?? $order->time_close;
    //         $order->time_service = $request->time_service ?? $order->time_service;
    //         $order->status = $request->status ?? $order->status;
    //         $order->save();
    //         $order->load('partner');
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Order updated successfully',
    //             'data' => [
    //                 'id' => $order->id,
    //                 'code_order' => $order->code_order,
    //                 'partner_name' => $order->partner->name, // Access the partner's name through the relationship
    //                 'customer_name' => $order->customer_name,
    //                 'price' => $order->price,
    //                 'mass_of_order' => $order->mass_of_order,
    //                 'address' => $order->address,
    //                 'longitude' => $order->longitude,
    //                 'latitude' => $order->latitude,
    //                 'time_open' => $order->time_open,
    //                 'time_close' => $order->time_close,
    //                 'time_service' => $order->time_service,
    //                 'status' => $order->status,
    //                 'created_at' => $order->created_at->format('Y-m-d H:i:s'),
    //                 'updated_at' => $order->updated_at->format('Y-m-d H:i:s'),
    //                 // Include any other order attributes you want to return here...
    //             ]
    //         ]);
    //     } else {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Order not found',
    //             'data' => null
    //         ]);
    //     }
    // }
    public function destroy($id)
    {
        $order = $this->orderService->deleteOrder($id);

        if ($order) {
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

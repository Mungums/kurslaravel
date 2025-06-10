<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            \Log::info('Orders request', [
                'user_id' => $request->user()->id,
                'photo_paths' => Order::where('user_id', $request->user()->id)
                ->pluck('photo_path')
                ->toArray()
            ]);
            $orders = Order::where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders: ' . $e->getMessage()
            ], 500);
        }
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048'
        ]);

        try {
            // Создаем заказ
            $order = new Order([
                'title' => $validated['title'],
                'due_date' => $validated['due_date'],
                'description' => $validated['description'],
                'user_id' => $request->user()->id
            ]);

            // Сохраняем фото если есть
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('orders', 'public');
                $order->photo_path = $path;
            }

            $order->save();

            return response()->json([
                'success' => true,
                'order' => $order,
                'message' => 'Order created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating order: ' . $e->getMessage()
            ], 500);
        }
    }
}
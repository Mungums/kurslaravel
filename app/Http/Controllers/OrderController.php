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
            $order = new Order([
                'title' => $validated['title'],
                'due_date' => $validated['due_date'],
                'description' => $validated['description'] ?? null,
                'user_id' => $request->user()->id
            ]);

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

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048'
        ]);

        try {
            $order = Order::where('id', $id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $order->title = $validated['title'];
            $order->due_date = $validated['due_date'];
            $order->description = $validated['description'] ?? null;

            if ($request->hasFile('photo')) {
                // Удаляем старое фото
                if ($order->photo_path && Storage::disk('public')->exists($order->photo_path)) {
                    Storage::disk('public')->delete($order->photo_path);
                }

                // Сохраняем новое
                $path = $request->file('photo')->store('orders', 'public');
                $order->photo_path = $path;
            }

            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'order' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $order = Order::where('id', $id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Удаляем фото
            if ($order->photo_path && Storage::disk('public')->exists($order->photo_path)) {
                Storage::disk('public')->delete($order->photo_path);
            }

            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting order: ' . $e->getMessage()
            ], 500);
        }
    }
}

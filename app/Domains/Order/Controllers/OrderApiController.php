<?php

namespace App\Domains\Order\Controllers;

use App\Domains\Order\Models\Order;
use App\Domains\Order\Actions\PlaceOrderAction;
use App\Domains\Order\Requests\StoreOrderRequest;
use App\Domains\Order\Resources\OrderResource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
//* Display a listing of the orders with optimized Eager Loading
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderApiController extends Controller
{
    /**
     * Display the specified order using Redis Caching
     */
    public function show(int $id): JsonResponse
    {
        $cacheKey = "order_show_{$id}";

        // High-Performance Redis Check
        $orderData = Cache::remember($cacheKey, now()->addHour(), function () use ($id) {
            return Order::with('invoice')->findOrFail($id);
        });

        return response()->json(new OrderResource($orderData));
    }

    /**
     * Display a listing of the orders with optimized Eager Loading
     */
    public function index(): AnonymousResourceCollection
    {
        // Using Eager Loading 'with()' to completely eliminate the N+1 problem
        $orders = Order::with('invoice')
            ->where('user_id', auth()->id() ?? 1) // Safe fallback for testing
            ->latest()
            ->paginate(15);

        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created order using our DDD Action
     */
    public function store(StoreOrderRequest $request, PlaceOrderAction $action): JsonResponse
    {
        // Execute the Senior Action from Day 3
        $order = $action->execute($request->validated());

        return response()->json(new OrderResource($order), 201);
    }
}
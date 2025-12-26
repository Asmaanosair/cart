<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $perPage = request('per_page', 15); // Default: 15 items per page
        $perPage = min($perPage, 100); // Max: 100 items per page

        $products = Product::orderBy('name')->paginate($perPage);

        return $this->paginatedResponse($products);
    }

    public function show(Product $product): JsonResponse
    {
        return $this->successResponse([
            'product' => $product,
        ]);
    }
}

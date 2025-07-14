<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\TemplateProductsResource;
use App\Services\ProductService;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService)
    {
        return [
            'role_or_permission:view-products|create-products|edit-products|delete-products',
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('delete-products'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('view-products'), only: ['index', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('edit-products'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('create-products'), only: ['store']),
        ];
    }

    public function index()
    {
        return response()->json($this->productService->getAll());
    }

    public function store(ProductRequest $request)
    {
        return response()->json($this->productService->create($request->validated()));
    }

    public function show($id)
    {
        return response()->json($this->productService->getById($id));
    }

    public function update(ProductRequest $request, $id)
    {
        return response()->json($this->productService->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        return response()->json(['deleted' => $this->productService->delete($id)]);
    }

    public function getTemplateProducts()
    {
        return response()->json(TemplateProductsResource::collection(
            $this->productService->getTemplateProducts()
        ));
    }
}

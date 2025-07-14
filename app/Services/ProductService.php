<?php
// app/Services/ProductService.php

namespace App\Services;

use App\Models\Product;
use App\Models\Activity;
use App\Models\Template;
use Illuminate\Support\Facades\Auth;

class ProductService extends Services
{
    public function getAll()
    {
        return Product::with('template')->get();
    }

    public function getById($id)
    {
        return Product::with('template')->findOrFail($id);
    }

    public function create(array $data)
    {
        $product = Product::create($data);
        $this->logActivity('Created product ID: ' . $product->id);
        return $product;
    }

    public function update($id, array $data)
    {
        $product = Product::findOrFail($id);
        $product->update($data);
        $this->logActivity('Updated product ID: ' . $product->id);
        return $product;
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        $this->logActivity('Deleted product ID: ' . $product->id);
        return true;
    }

    public function createMany(Template $template, array|null $data)
    {
        if (!empty($data))
            foreach ($data as $productData) {
                $product = new Product($productData);
                // $product->price = $productData['price'];
                // $product->price = $productData['price'];
                $product->template()->associate($template);
                $product->save();
            }
    }

    public function updateMany(Template $template, array|null $data)
    {
        $template->products()->delete();
        if (!empty($data)) {
            $this->createMany($template, $data);
        }
    }

    public function getTemplateProducts()
    {
        return Template::query()->get();
    }
}

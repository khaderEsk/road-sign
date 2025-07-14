<?php

namespace App\Services;

use App\Models\Template;
use Illuminate\Support\Facades\DB;

class TemplateService extends Services
{
    public function __construct(private ProductService $productService)
    {
    }
    public function getAll(string $type = null, string $model = null)
    {
        $query = Template::query()->with('products');

        if (!empty($type))
            $query->where('type', 'like', $type);


        if (!empty($model))
            $query->where('model', 'like', $model);


        return $query->orderbyDesc('created_at')->get();
    }
    public function getTemplatesType()
    {
        return Template::query()->pluck('type');
    }

    public function getTemplatesModel()
    {
        return Template::select('model', DB::raw('count(*) as count'))
            ->groupBy('model')
            ->get();
    }

    public function getById($id)
    {
        return Template::with('products')->findOrFail($id);
    }

    public function create(array $data)
    {
        $template = Template::create($data);
        $this->productService->createMany($template, $data['products']);
        $this->logActivity('تم إنشاء القالب: ' . $template->model . " بواسطة المستخدم: " . auth()->user()->username);
        return $template;
    }

    public function update($id, array $data)
    {
        $template = Template::findOrFail($id);
        $template->update($data);
        $this->productService->updateMany($template,$data['products']);
        $this->logActivity('تم تحديث القالب: ' . $template->model . " بواسطة المستخدم: " . auth()->user()->username);
        return $template;
    }

    public function delete($id)
    {
        $template = Template::findOrFail($id);
        $this->logActivity('تم حذف القالب: ' . $template->model . " بواسطة المستخدم: " . auth()->user()->username);
        $template->delete();
        return true;
    }

}

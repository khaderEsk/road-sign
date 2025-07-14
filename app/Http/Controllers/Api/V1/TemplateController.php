<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TemplateRequest;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class TemplateController extends Controller
{
    public function __construct(protected TemplateService $templateService)
    {
        return [
            'role_or_permission:view-templates|create-templates|edit-templates|delete-templates',
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('delete-templates'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('view-templates'), only: ['index', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('edit-templates'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('create-templates'), only: ['store']),
        ];
    }

    public function index(Request $request)
    {
        return response()->json($this->templateService->getAll($request->query('type'), $request->query('model')));
    }

    public function store(TemplateRequest $request)
    {
        return response()->json($this->templateService->create($request->validated()));
    }

    public function show($id)
    {
        return response()->json($this->templateService->getById($id));
    }

    public function update(TemplateRequest $request, $id)
    {
        return response()->json($this->templateService->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        return response()->json(['deleted' => $this->templateService->delete($id)]);
    }

    public function getTemplatesType()
    {
        return response()->json($this->templateService->getTemplatesType());
    }
    public function getTemplatesModel()
    {
        return response()->json($this->templateService->getTemplatesModel());
    }
}

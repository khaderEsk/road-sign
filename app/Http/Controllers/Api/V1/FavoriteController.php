<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\FavoriteRequest;
use App\Services\FavoriteService;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(protected FavoriteService $favoriteService)
    {
        $this->favoriteService = $favoriteService;
    }

    public function index()
    {
        return $this->favoriteService->getAll();
    }

    public function show($id)
    {
        // return "yes";
        return $this->favoriteService->add($id);
    }

    public function destroy($id)
    {
        return $this->favoriteService->delete($id);
    }
}

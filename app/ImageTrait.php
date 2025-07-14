<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait ImageTrait
{

    public function uploadImage(Request $request, string $attribute_name, string $folder_name)
    {
        $imagePath = null;
        if ($request->hasFile($attribute_name)) {
            $imagePath = Storage::disk('public')->put($folder_name, $request->file($attribute_name));
        }
        return $imagePath;
    }
}

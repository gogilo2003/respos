<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    public function uploadMenuItemImage(UploadedFile $file): string
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('menu-items', $filename, 'public');

        return Storage::url($path);
    }

    public function deleteMenuItemImage(?string $imageUrl): void
    {
        if (! $imageUrl) {
            return;
        }

        $path = str_replace('/storage/', '', $imageUrl);
        Storage::disk('public')->delete($path);
    }
}

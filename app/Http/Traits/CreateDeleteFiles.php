<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;

trait CreateDeleteFiles
{
    public function storeFile($row, $file, $request): ?string
    {
        if ($request->hasFile($row)) {

            return $request->file($row)
                ->store($file);
        }

        return null;
    }

    public function deleteFile($fileName, $row, $request): void
    {
        if ($request->hasFile($row)) {
            if (Storage::exists($fileName)) {
                Storage::delete($fileName);
            }
        }
    }
}

<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;

class CreateDeleteFiles
{
    public function storeFile($row, $file, $request): ?string
    {
        if ($request->hasFile($row)) {

            $path = $request->file($row)
                ->store($file)
            ;

            return "storage/" . $path;
        }

        return null;
    }

    public function deleteFile($fileName, $row, $request): void
    {
        if ($request->hasFile($row)) {
            $fileName = str_replace('storage/', '', $fileName);
            if (Storage::exists($fileName)) {
                Storage::delete($fileName);
            }
        }
    }
}

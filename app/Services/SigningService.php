<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SigningService
{
    public function sign(string $filename, UploadedFile $file): void
    {
        Storage::disk('signatures')->put($filename, $file->getContent());
        sleep(2);
    }
}

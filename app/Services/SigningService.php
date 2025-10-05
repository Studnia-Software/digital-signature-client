<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SigningService
{

    protected string $filename;
    protected string $signedName;

    /**
     * @throws \Exception
     */
    public function sign(UploadedFile $file): mixed
    {
        $this->filename = $file->getClientOriginalName();
        $this->signedName = 'signed_' . $this->filename;

        Storage::disk('signatures')->put($this->filename, $file->getContent());

        $ogPath = Storage::disk('signatures')->path($this->filename);
        $signedPath = Storage::disk('signatures')->path($this->signedName);

        chdir('C:\Users\w.kowalinski\hakaton\app\digital-signature-client');
        return shell_exec("C:\Users\w.kowalinski\hakaton\app\digital-signature-client\sign_file.exe $ogPath $signedPath 2>&1");
    }

    public function getSignedFile(): string
    {
        return $this->signedName;
    }
}

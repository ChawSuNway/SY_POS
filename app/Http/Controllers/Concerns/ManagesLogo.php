<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;

/** ဆိုင် logo image များ public/uploads/ တွင် သိမ်း/ဖျက်ခြင်း (shared) */
trait ManagesLogo
{
    protected function storeLogo(UploadedFile $file): string
    {
        $dir = public_path('uploads');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = 'logo_'.time().'_'.mt_rand(1000, 9999).'.'.$file->getClientOriginalExtension();
        $file->move($dir, $name);

        return 'uploads/'.$name;
    }

    protected function deleteLogoFile(?string $path): void
    {
        if ($path && is_file(public_path($path))) {
            @unlink(public_path($path));
        }
    }
}

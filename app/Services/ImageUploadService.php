<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageUploadService
{
    public function storeAsWebp(UploadedFile $file, string $directory, string $filename, string $disk = 'r2', int $quality = 80)
    {
        $manager = new ImageManager(new Driver());
        $encoded = $manager->read($file->getRealPath())->toWebp($quality);

        $path = trim($directory, '/').'/'.pathinfo($filename, PATHINFO_FILENAME).'.webp';

        Storage::disk($disk)->put($path, (string) $encoded);

        return $path;
    }
}

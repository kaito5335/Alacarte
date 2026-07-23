<?php

namespace Database\Factories\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlaceholderImage
{
    /**
     * Generate a placeholder PNG and store it on the public disk, returning its relative path.
     */
    public static function store(string $directory, string $label): string
    {
        $width = 640;
        $height = 480;

        $image = imagecreatetruecolor($width, $height);
        $background = imagecolorallocate($image, random_int(80, 200), random_int(80, 200), random_int(80, 200));
        imagefill($image, 0, 0, $background);

        $textColor = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, 5, 20, (int) ($height / 2), mb_substr($label, 0, 30), $textColor);

        ob_start();
        imagepng($image);
        $contents = ob_get_clean();
        imagedestroy($image);

        $path = $directory.'/'.Str::uuid().'.png';
        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}

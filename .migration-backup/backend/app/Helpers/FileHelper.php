<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Models\Gallery;
use App\Models\Settings;
use Illuminate\Http\UploadedFile;
use Str;
use Throwable;

class FileHelper
{
    public const imageExtensions = [
        'png',
        'jpg',
        'jpeg',
        'webp',
        'svg',
        'jfif',
        'avif',
        'gif',
    ];

    /**
     * Upload file function
     * @param UploadedFile $file
     * @param string $path
     * @return array
     */
    public static function uploadFile(UploadedFile $file, string $path): array
    {
        try {
            $isAws = Settings::where('key', 'aws')->first();

            $options = [];

            if (data_get($isAws, 'value')) {
                $options = ['disk' => 's3'];
            }

            $id   = auth('sanctum')->id() ?? '0001';
            $uuid = Str::uuid();
            $selectedImage = SelectedImageUpload::selected($path);
            $ext  = $selectedImage ? $file->guessExtension() : $file->getClientOriginalExtension();
            $dir  = $ext;

            if ($selectedImage) {
                $dir = 'images';
            } elseif (in_array($file->getClientOriginalExtension(), self::imageExtensions)) {

                $dir  = 'images';

                $ext = strtolower(
                    preg_replace('#.+\.([a-z]+)$#i', '$1',
                        str_replace(self::imageExtensions, '.webp', $file->getClientOriginalName())
                    )
                );

            }

            $fileName = "$id-$uuid.$ext";

            $url = $file->storeAs("public/$dir/$path", $fileName, $options);

            // config('app.img_host') is a plain env('IMG_HOST') with no
            // default (see config/app.php) - if it's ever unset on a given
            // environment, string concatenation silently turns this into
            // "" . "storage/images/...", a bare relative path with no
            // host. That's not a URL a browser (or next/image, which
            // rejects it outright rather than trying to load it) can ever
            // resolve - it crashed the entire storefront the one time this
            // happened for real (a Settings logo upload).
            //
            // config('app.url') is the next fallback, but it defaults to
            // Laravel's own literal 'http://localhost' (no port) when
            // APP_URL is unset too - that placeholder crashed the same way
            // a second time, just missing a port instead of a host
            // entirely. A bare 'http://localhost' is never a value anyone
            // deliberately configured (it's indistinguishable from "not
            // set"), so treat it as absent and fall back further to the
            // current request's own scheme+host - the exact host:port the
            // browser just used to reach this server, which is correct by
            // construction regardless of what .env does or doesn't have
            // set. Every caller of uploadFile() runs inside an HTTP
            // request, so request() is always available here.
            $imgHost = config('app.img_host')
                ?: (config('app.url') !== 'http://localhost' ? config('app.url') : null)
                ?: request()?->getSchemeAndHttpHost();
            $imgHost = rtrim($imgHost ?: 'http://localhost', '/') . '/';

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $imgHost . (!data_get($isAws, 'value') ? str_replace('public/', 'storage/', $url) : $url)
            ];
        } catch (Throwable $e) {

            $message = $e->getMessage();

            if ($message === "Class \"finfo\" not found") {
                $message = 'You need on php file info extension';
            }

            return [
                'status'  => false,
                'code'    => ResponseError::ERROR_400,
                'message' => $message
            ];
        }
    }

    /**
     * Delete file function
     * @param $path
     * @return mixed
     */
    public static function deleteFile($path): mixed
    {
        return Gallery::where('path', $path)->delete();
    }

}

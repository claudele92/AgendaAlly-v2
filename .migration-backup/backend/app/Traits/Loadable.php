<?php
declare(strict_types=1);

namespace App\Traits;

use App\Models\Gallery;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * @property Collection|Gallery[] $galleries
 * @property Gallery|null $gallery
 * @property int $galleries_count
 */
trait Loadable
{
    public function uploads($files, ?string $type = ''): void
    {
        // $files is routinely null (callers almost always pass
        // data_get($data, 'images') with no default, and that key is
        // absent whenever a create/update request has no image at all) —
        // that's "no image provided", not an error, so it's a no-op here
        // rather than a foreach-on-null warning.
        if (!is_iterable($files)) {
            return;
        }

        // config('app.img_host') (env('IMG_HOST')) is optional and unset
        // by default (not in .env.example) — with strict_types declared
        // above, passing that null straight into str_replace()'s $search
        // parameter is a fatal TypeError, not a graceful "no host prefix".
        $imgHost = (string) config('app.img_host');

        foreach ($files as $key => $file) {

            // An individual entry can be blank too (e.g. images: [null]),
            // which is likewise "no image for this slot", not an error.
            if (empty($file)) {
                continue;
            }

            $file = str_replace($imgHost, '', $file);
            $fileName = str_replace(['storage/images', 'public/images'], '', $file);

            $title = Str::of($fileName)->afterLast('/');
            $type  = !empty($type) ? $type : Str::of($fileName)->after('/');
            $type  = Str::of($type)->before('/');

            $image          = new Gallery;
            $image->title   = $title;
            $image->path    = $imgHost . $file;
            $image->type    = $type;
            $image->size    = data_get($file, 'size');
            $image->mime    = data_get($file, 'mimeType');
            $image->preview = request("previews.$key");

            $this->galleries()->save($image);
        }
    }

    public function galleries(): MorphMany
    {
        return $this->morphMany(Gallery::class, 'loadable');
    }

    public function gallery(): MorphOne
    {
        return $this->morphOne(Gallery::class, 'loadable');
    }
}


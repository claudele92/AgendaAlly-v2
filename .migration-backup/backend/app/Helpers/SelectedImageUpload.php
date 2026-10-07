<?php
declare(strict_types=1);
namespace App\Helpers;

final class SelectedImageUpload
{
    public static function selected(string $type): bool
    {
        return in_array($type, ['users', 'services', 'shops', 'shops/logo', 'shops/background'], true);
    }

    public static function rules(string $type): array
    {
        return self::selected($type)
            ? ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp,gif,avif', 'max:5120']
            : ['required', 'file'];
    }
}
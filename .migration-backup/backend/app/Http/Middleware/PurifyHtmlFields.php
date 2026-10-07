<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Mews\Purifier\Facades\Purifier;

class PurifyHtmlFields
{
    protected array $fieldsToPurify = [
        'title',
        'desc',
        'text',
        'content',
        'description',
        'desc',
        'address',
        'button_text',
        'about',
        'term',
    ];

    protected array $subFieldsToContinue = [
        'latitude',
        'longitude',
    ];

    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();

        foreach ($this->fieldsToPurify as $field) {

            if (!$request->has($field)) {
                continue;
            }

            $value = $request->input($field);

            if (!is_array($value)) {
                $input[$field] = Purifier::clean($value);
                continue;
            }

            foreach ($value as $key => $val) {
                if (!in_array($key, $this->subFieldsToContinue)) {
                    $value[$key] = Purifier::clean($val);
                }
            }

            $input[$field] = $value;
        }

        $request->merge($input);

        return $next($request);
    }
}

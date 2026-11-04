<?php

namespace App\Filament\Concerns;

use Closure;
use Illuminate\Validation\ValidationException;

/**
 * Runs a business-layer (service) call from a Filament form page and re-maps
 * ValidationException keys (e.g. "code") onto form state paths ("data.code"),
 * so service rule violations are displayed next to the relevant form field.
 */
trait MapsServiceValidationErrors
{
    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    protected function callService(Closure $callback): mixed
    {
        try {
            return $callback();
        } catch (ValidationException $e) {
            $messages = [];
            foreach ($e->errors() as $key => $fieldMessages) {
                $messages[str_starts_with($key, 'data.') ? $key : "data.{$key}"] = $fieldMessages;
            }

            throw ValidationException::withMessages($messages);
        }
    }
}

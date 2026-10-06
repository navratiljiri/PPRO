<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:courses,code'],
            'name' => ['required', 'string', 'max:255'],
            'annotation' => ['required', 'string', 'min:10'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'is_accredited' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kód kurzu je povinný údaj.',
            'code.unique' => 'Kurz s tímto kódem již existuje.',
            'name.required' => 'Název kurzu je povinný.',
            'annotation.required' => 'Anotace kurzu je povinná.',
            'annotation.min' => 'Anotace musí mít alespoň 10 znaků.',
            'duration_hours.required' => 'Hodinový rozsah je povinný.',
            'duration_hours.min' => 'Rozsah kurzu musí být alespoň 1 vyučovací hodina.',
            'price.required' => 'Cena kurzu je povinná.',
            'price.min' => 'Cena kurzu nesmí být záporná.',
        ];
    }
}

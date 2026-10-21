<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLecturerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191', 'unique:lecturers,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Jméno lektora je povinné.',
            'last_name.required' => 'Příjmení lektora je povinné.',
            'email.required' => 'Kontaktní e-mail je povinný.',
            'email.email' => 'Zadejte platnou e-mailovou adresu.',
            'email.unique' => 'Lektor s tímto e-mailem již v databázi existuje.',
        ];
    }
}

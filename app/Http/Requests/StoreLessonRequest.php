<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lecturer_id' => ['required', 'exists:lecturers,id'],
            'topic' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:24'],
        ];
    }

    public function messages(): array
    {
        return [
            'lecturer_id.required' => 'Přiřazení lektora je povinné.',
            'lecturer_id.exists' => 'Vybraný lektor neexistuje v databázi.',
            'topic.required' => 'Téma lekce / obsahový blok je povinný.',
            'starts_at.required' => 'Čas začátku lekce je povinný.',
            'ends_at.required' => 'Čas konce lekce je povinný.',
            'ends_at.after' => 'Konec lekce musí následovat po jejím začátku.',
            'duration_hours.required' => 'Počet hodin je povinný.',
            'duration_hours.min' => 'Délka lekce musí být alespoň 1 vyučovací hodina.',
        ];
    }
}

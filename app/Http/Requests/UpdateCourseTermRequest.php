<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourseTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'classroom' => ['required', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
            'status' => ['required', 'in:enrolling,in_progress,completed,cancelled'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'course_id.required' => 'Výběr kurzu je povinný.',
            'start_date.required' => 'Datum a čas zahájení je povinné.',
            'end_date.required' => 'Datum a čas ukončení je povinné.',
            'end_date.after_or_equal' => 'Datum ukončení nemůže předcházet datu zahájení.',
            'classroom.required' => 'Označení učebny je povinné.',
            'capacity.required' => 'Kapacita učebny je povinná.',
            'capacity.min' => 'Kapacita musí být alespoň 1 účastník.',
            'status.required' => 'Stav termínu je povinný.',
        ];
    }
}

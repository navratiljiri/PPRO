<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseTermController;
use App\Http\Controllers\LecturerController;
use Illuminate\Support\Facades\Route;

/*
| Public website is read-only (catalog of courses, terms and lecturers).
| All administration (create / edit / delete, registrations, attendance, certificates)
| is done exclusively in the Filament panel at /admin.
*/

Route::redirect('/', '/courses');

Route::resource('courses', CourseController::class)->only(['index', 'show']);

Route::resource('terms', CourseTermController::class)
    ->only(['index', 'show'])
    ->parameters(['terms' => 'courseTerm']);

Route::resource('lecturers', LecturerController::class)
    ->only(['index', 'show'])
    ->whereNumber('lecturer');

<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseTermController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\LessonController;
use Illuminate\Support\Facades\Route;

// Hlavní stránka přesměrovává přímo do katalogu kurzů
Route::redirect('/', '/courses');

// RESTful routy pro správu entit Kurzu
Route::resource('courses', CourseController::class);

// RESTful routy pro správu Termínů kurzů
Route::resource('terms', CourseTermController::class)->parameters(['terms' => 'courseTerm']);

// RESTful routy pro správu Lektorů
Route::resource('lecturers', LecturerController::class);

// Správa lekcí v rámci termínu (střídání lektorů)
Route::post('terms/{courseTerm}/lessons', [LessonController::class, 'store'])->name('terms.lessons.store');
Route::delete('lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');

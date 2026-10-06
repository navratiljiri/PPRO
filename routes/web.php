<?php

use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;

// Hlavní stránka přesměrovává přímo do katalogu kurzů
Route::redirect('/', '/courses');

// RESTful routy pro správu entit Kurzu
Route::resource('courses', CourseController::class);

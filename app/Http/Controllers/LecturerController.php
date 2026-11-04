<?php

namespace App\Http\Controllers;

use App\Services\LecturerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public, read-only list of lecturers (presentation layer).
 * Lecturer administration is available only in the Filament panel (/admin).
 */
class LecturerController extends Controller
{
    public function __construct(
        protected LecturerService $lecturerService
    ) {}

    /**
     * Display lecturers catalog with stats
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $lecturers = $this->lecturerService->getLecturersPaginated($search, perPage: 12);
        $stats = $this->lecturerService->getLecturerStats();

        return view('lecturers.index', compact('lecturers', 'stats', 'search'));
    }

    /**
     * Display lecturer detail
     */
    public function show(int $id): View
    {
        $lecturer = $this->lecturerService->getLecturerById($id);

        return view('lecturers.show', compact('lecturer'));
    }
}

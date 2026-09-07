<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\LessonController;

//Route::resource('lessons', LessonController::class);

Route::get('/dashboard', function () {

    $user = auth()->user();

    if ($user->role === 'teacher') {
        return view('dashboard');
    }

    return view('student.dashboard');

})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->get('/lessons-json', function () {
    $lessons = \App\Models\Lesson::where(
        'teacher_id',
        auth()->id()
    )->get();

    return response()->json($lessons);
});

Route::middleware('auth')->post(
    '/lessons-json',
    [LessonController::class, 'apiStore']
);

Route::middleware('auth')->put(
    '/lessons-json/{lesson}',
    [LessonController::class, 'apiUpdate']
);

Route::middleware('auth')->delete(
    '/lessons-json/{lesson}',
    [LessonController::class, 'apiDestroy']
);

Route::middleware('auth')->put(
    '/lessons-json/{lesson}/publish',
    [LessonController::class, 'apiTogglePublish']
);

Route::middleware('auth')->put(
    '/lessons-json/{lesson}/canvas',
    [LessonController::class, 'apiSaveCanvas']
);

Route::middleware('auth')->get('/student-lessons-json', function () {
    $lessons = \App\Models\Lesson::where('is_public', true)
        ->with('teacher')
        ->get();

    return response()->json($lessons);
});

Route::middleware('auth')->post(
    '/lessons-json/{lesson}/duplicate',
    [LessonController::class, 'apiDuplicate']
);

require __DIR__ . '/auth.php';

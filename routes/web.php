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
    $lessons = \App\Models\Lesson::with('teacher')->get();

    $lessons->each(function ($lesson) {
        $lesson->can_edit = $lesson->teacher_id === auth()->id();
    });

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

Route::middleware('auth')->get(
    '/lessons-json/{lesson}/canvas',
    [LessonController::class, 'apiGetCanvas']
);

Route::middleware('auth')->get('/student-lessons-json', function () {
    $lessons = \App\Models\Lesson::where('is_public', true)
        ->with('teacher')
        ->get();

    return response()->json($lessons);
});

Route::middleware('auth')->get(
    '/student-lessons-json/{lesson}/canvas',
    [LessonController::class, 'apiGetPublicCanvas']
);

Route::middleware('auth')->post(
    '/lessons-json/{lesson}/duplicate',
    [LessonController::class, 'apiDuplicate']
);


// クラス管理
Route::middleware('auth')->get(
    '/classes-json',
    [\App\Http\Controllers\ClassController::class, 'index']
);

Route::middleware('auth')->post(
    '/classes-json',
    [\App\Http\Controllers\ClassController::class, 'store']
);

Route::middleware('auth')->get(
    '/classes-json/{class}',
    [\App\Http\Controllers\ClassController::class, 'show']
);

Route::middleware('auth')->get(
    '/students-json',
    [\App\Http\Controllers\ClassController::class, 'students']
);

Route::middleware('auth')->post(
    '/classes-json/{class}/students',
    [\App\Http\Controllers\ClassController::class, 'addStudent']
);

Route::middleware('auth')->get(
    '/classes-json/{class}/lessons',
    [\App\Http\Controllers\ClassController::class, 'lessons']
);

Route::middleware('auth')->get(
    '/classes-json/{class}/available-lessons',
    [\App\Http\Controllers\ClassController::class, 'availableLessons']
);

Route::middleware('auth')->post(
    '/classes-json/{class}/lessons',
    [\App\Http\Controllers\ClassController::class, 'addLesson']
);


require __DIR__ . '/auth.php';

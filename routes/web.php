<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;


// ========================================
// トップページ
// ========================================

Route::get('/', function () {
    return view('welcome');
});


// ========================================
// ダッシュボード
// ========================================

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->role === 'teacher') {
        return view('dashboard');
    }

    return view('student.dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


// ========================================
// プロフィール
// ========================================

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});


// ========================================
// 教材管理（先生）
// ========================================

// 教材一覧を取得
Route::middleware('auth')->get('/lessons-json', function () {

    $lessons = \App\Models\Lesson::with('teacher')->get();

    $lessons->each(function ($lesson) {
        $lesson->can_edit = $lesson->teacher_id === auth()->id();
    });

    return response()->json($lessons);

});

// 教材を新規作成
Route::middleware('auth')->post(
    '/lessons-json',
    [LessonController::class, 'apiStore']
);

// 教材を更新
Route::middleware('auth')->put(
    '/lessons-json/{lesson}',
    [LessonController::class, 'apiUpdate']
);

// 教材を削除
Route::middleware('auth')->delete(
    '/lessons-json/{lesson}',
    [LessonController::class, 'apiDestroy']
);

// 教材の公開・非公開を切り替え
Route::middleware('auth')->put(
    '/lessons-json/{lesson}/publish',
    [LessonController::class, 'apiTogglePublish']
);

// 教材のCanvasデータを保存
Route::middleware('auth')->put(
    '/lessons-json/{lesson}/canvas',
    [LessonController::class, 'apiSaveCanvas']
);

// 教材のCanvasデータを取得
Route::middleware('auth')->get(
    '/lessons-json/{lesson}/canvas',
    [LessonController::class, 'apiGetCanvas']
);

// 教材を複製
Route::middleware('auth')->post(
    '/lessons-json/{lesson}/duplicate',
    [LessonController::class, 'apiDuplicate']
);


// ========================================
// 生徒用教材
// ========================================

// 生徒が所属クラスの教材一覧を取得
Route::middleware('auth')->get(
    '/student-lessons-json',
    [ClassController::class, 'studentLessons']
);

// 生徒が教材のCanvasデータを取得
Route::middleware('auth')->get(
    '/student-lessons-json/{lesson}/canvas',
    [LessonController::class, 'apiGetPublicCanvas']
);


// ========================================
// 生徒の回答・提出
// ========================================

// 生徒が回答を保存
Route::middleware('auth')->post(
    '/student-lessons-json/{lesson}/submission',
    [SubmissionController::class, 'save']
);

// 生徒が保存済み回答を取得
Route::middleware('auth')->get(
    '/student-lessons-json/{lesson}/submission',
    [SubmissionController::class, 'show']
);

// 生徒が回答を提出
Route::middleware('auth')->post(
    '/student-lessons-json/{lesson}/submission/submit',
    [SubmissionController::class, 'submit']
);

// 生徒が提出を取り消す
Route::middleware('auth')->post(
    '/student-lessons-json/{lesson}/submission/withdraw',
    [SubmissionController::class, 'withdraw']
);

// 先生が教材の提出状況を取得
Route::middleware('auth')->get(
    '/lessons/{lesson}/submissions',
    [SubmissionController::class, 'index']
);

Route::middleware('auth')->post(
    '/submissions/{submission}/grade',
    [SubmissionController::class, 'grade']
);
//採点結果を生徒へ返却
Route::middleware('auth')->post(
    '/submissions/{submission}/return',
    [SubmissionController::class, 'returnSubmission']
);

// ========================================
// クラス管理（先生）
// ========================================

// クラス一覧を取得
Route::middleware('auth')->get(
    '/classes-json',
    [ClassController::class, 'index']
);

// クラスを新規作成
Route::middleware('auth')->post(
    '/classes-json',
    [ClassController::class, 'store']
);

// クラスの詳細を取得
Route::middleware('auth')->get(
    '/classes-json/{class}',
    [ClassController::class, 'show']
);

// クラスに所属する生徒を追加
Route::middleware('auth')->post(
    '/classes-json/{class}/students',
    [ClassController::class, 'addStudent']
);

// クラスから生徒を外す
Route::middleware('auth')->delete(
    '/classes-json/{class}/students/{student}',
    [ClassController::class, 'removeStudent']
);

// クラスに割り当てられた教材一覧
Route::middleware('auth')->get(
    '/classes-json/{class}/lessons',
    [ClassController::class, 'lessons']
);

// クラスに追加できる教材一覧
Route::middleware('auth')->get(
    '/classes-json/{class}/available-lessons',
    [ClassController::class, 'availableLessons']
);

// クラスに教材を追加
Route::middleware('auth')->post(
    '/classes-json/{class}/lessons',
    [ClassController::class, 'addLesson']
);

// クラスから教材を外す
Route::middleware('auth')->delete(
    '/classes-json/{class}/lessons/{lesson}',
    [ClassController::class, 'removeLesson']
);


// ========================================
// 生徒アカウント管理（先生）
// ========================================

// 生徒一覧を取得
Route::middleware('auth')->get(
    '/students-json',
    [ClassController::class, 'students']
);

// 生徒アカウントを新規作成
Route::middleware('auth')->post(
    '/students-json',
    [ClassController::class, 'storeStudent']
);

//生徒詳細を表示
Route::middleware('auth')->get(
    '/students-json/{student}',
    [ClassController::class, 'student']
);


// ========================================
// 認証
// ========================================

require __DIR__ . '/auth.php';
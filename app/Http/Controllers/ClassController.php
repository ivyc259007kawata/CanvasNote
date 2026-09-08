<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    // =========================
    // クラス一覧
    // =========================

    public function index()
    {
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        $classes = SchoolClass::withCount('users')
            ->orderBy('grade')
            ->orderBy('name')
            ->get();

        return response()->json($classes);
    }


    // =========================
    // クラス作成
    // =========================

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade' => 'required|integer|min:1|max:6',
        ]);

        $class = SchoolClass::create($validated);

        return response()->json($class, 201);
    }


    // =========================
    // クラス詳細
    // =========================

    public function show(SchoolClass $class)
    {
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        $class->load([
            'users' => function ($query) {
                $query
                    ->where('role', 'student')
                    ->orderBy('name');
            },
            'lessons'
        ]);

        return response()->json($class);
    }


    // =========================
    // 生徒一覧
    // =========================

    public function students()
    {
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        $students = User::where(
            'role',
            'student'
        )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
            ]);

        return response()->json($students);
    }


    // =========================
    // クラスに生徒を追加
    // =========================

    public function addStudent(
        Request $request,
        SchoolClass $class
    ) {
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $student = User::findOrFail(
            $validated['user_id']
        );

        // 生徒以外は追加できない
        if ($student->role !== 'student') {
            return response()->json([
                'message' => '生徒のみ追加できます'
            ], 422);
        }

        // すでに所属しているか確認
        if (
            $class->users()
                ->where('users.id', $student->id)
                ->exists()
        ) {
            return response()->json([
                'message' => 'この生徒はすでに所属しています'
            ], 422);
        }

        // クラスに追加
        $class->users()->attach(
            $student->id
        );

        return response()->json([
            'message' => '生徒を追加しました'
        ], 201);
    }


    // =========================
    // クラスに登録されている教材一覧
    // =========================

    public function lessons(SchoolClass $class)
    {
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        $lessons = $class->lessons()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($lessons);
    }


    // =========================
    // クラスにまだ追加されていない教材一覧
    // =========================

    public function availableLessons(SchoolClass $class)
    {
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        $lessons = Lesson::whereDoesntHave(
            'schoolClasses',
            function ($query) use ($class) {
                $query->where(
                    'classes.id',
                    $class->id
                );
            }
        )
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($lessons);
    }


    // =========================
    // クラスに教材を追加
    // =========================

    public function addLesson(
        Request $request,
        SchoolClass $class
    ) {
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        $validated = $request->validate([
            'lesson_id' => 'required|integer|exists:lessons,id',
        ]);

        // すでに追加されているか確認
        if (
            $class->lessons()
                ->where(
                    'lessons.id',
                    $validated['lesson_id']
                )
                ->exists()
        ) {
            return response()->json([
                'message' => 'この教材はすでに追加されています'
            ], 422);
        }

        // クラスに教材を追加
        $class->lessons()->attach(
            $validated['lesson_id']
        );

        return response()->json([
            'message' => '教材を追加しました'
        ], 201);
    }
}

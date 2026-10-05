<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * クイズ一覧を取得
     */
    public function index()
    {
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        $quizzes = Quiz::with(['createdBy', 'classes', 'lesson'])
            ->withCount('questions')
            ->latest()
            ->get();

        return response()->json($quizzes);
    }

    /**
     * クイズ編集用の詳細を取得
     */
    public function show(Quiz $quiz)
    {
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        $quiz->load([
            'questions' => function ($query) {
                $query->orderBy('sort_order');
            },
            'classes',
            'lesson',
        ]);

        return response()->json($quiz);
    }

    public function results(Quiz $quiz)
    {
        $quiz->load([
            'lesson',
            'createdBy',
            'classes',
        ]);

        $students = $quiz->classes
            ->flatMap(function ($class) {
                return $class->students;
            })
            ->unique('id')
            ->values()
            ->map(function ($student) use ($quiz) {

                $answer = $quiz->results()
                    ->where('student_id', $student->id)
                    ->first();

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'class_name' => $student->class->name ?? '',
                    'answered' => $answer !== null,
                    'score' => $answer?->score,
                    'correct_count' => $answer?->correct_count,
                    'total_count' => $answer?->total_count,
                ];
            });

        return response()->json([
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'lesson' => $quiz->lesson,
                'created_by' => $quiz->createdBy,
            ],

            'classes' => $quiz->classes,

            // 回答済み人数
            'answered_students' => $students
                ->where('answered', true)
                ->count(),

            // 対象生徒数
            'total_students' => $students->count(),

            // 生徒一覧
            'students' => $students,
        ]);
    }
    /**
     * クイズを作成
     */
    public function store(Request $request)
    {

        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',   // ★ 追加
            'title' => 'required|string|max:255',
            'mode' => 'required|in:single,test',

            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.option_a' => 'required|string|max:255',
            'questions.*.option_b' => 'required|string|max:255',
            'questions.*.option_c' => 'required|string|max:255',
            'questions.*.option_d' => 'required|string|max:255',
            'questions.*.correct_answer' => 'required|in:A,B,C,D',

            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:classes,id',
        ]);

        $quiz = Quiz::create([
            'lesson_id' => $validated['lesson_id'],   // ★ 追加
            'created_by' => Auth::id(),
            'title' => $validated['title'],
            'mode' => $validated['mode'],
            'status' => 'draft',
        ]);

        if (!empty($validated['class_ids'])) {
            $quiz->classes()->sync($validated['class_ids']);
        }

        foreach ($validated['questions'] as $index => $question) {
            QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question' => $question['question'],
                'option_a' => $question['option_a'],
                'option_b' => $question['option_b'],
                'option_c' => $question['option_c'],
                'option_d' => $question['option_d'],
                'correct_answer' => $question['correct_answer'],
                'sort_order' => $index,
            ]);
        }

        return response()->json([
            'message' => 'クイズを作成しました',
            'quiz' => $quiz->load('questions'),
        ], 201);
    }

    /**
     * クイズを編集
     */
    public function update(Request $request, Quiz $quiz)
    {
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        // 公開中のクイズは編集不可
        if ($quiz->status === 'active') {
            return response()->json([
                'message' => '公開中のクイズは編集できません。先に非公開にしてください。'
            ], 422);
        }

        // すでに生徒が回答している場合は編集不可
        if ($quiz->results()->exists()) {
            return response()->json([
                'message' => 'すでに生徒が回答しているクイズは編集できません。'
            ], 422);
        }

        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string|max:255',
            'mode' => 'required|in:single,test',

            'questions' => 'required|array|min:1',

            'questions.*.question' => 'required|string',
            'questions.*.option_a' => 'required|string|max:255',
            'questions.*.option_b' => 'required|string|max:255',
            'questions.*.option_c' => 'required|string|max:255',
            'questions.*.option_d' => 'required|string|max:255',
            'questions.*.correct_answer' => 'required|in:A,B,C,D',

            'class_ids' => 'nullable|array',
            'class_ids.*' => 'exists:classes,id',
        ]);

        DB::transaction(function () use ($quiz, $validated) {

            // クイズ本体を更新
            $quiz->update([
                'lesson_id' => $validated['lesson_id'],
                'title' => $validated['title'],
                'mode' => $validated['mode'],
            ]);

            // 対象クラスを更新
            $quiz->classes()->sync(
                $validated['class_ids'] ?? []
            );

            // 既存問題を一度削除
            $quiz->questions()->delete();

            // 編集後の問題を登録し直す
            foreach ($validated['questions'] as $index => $question) {

                QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question' => $question['question'],
                    'option_a' => $question['option_a'],
                    'option_b' => $question['option_b'],
                    'option_c' => $question['option_c'],
                    'option_d' => $question['option_d'],
                    'correct_answer' => $question['correct_answer'],
                    'sort_order' => $index,
                ]);
            }
        });

        return response()->json([
            'message' => 'クイズを更新しました',
            'quiz' => $quiz->load([
                'questions',
                'classes',
                'lesson',
            ]),
        ]);
    }


    /**
     * クイズを削除
     */
    public function destroy(Quiz $quiz)
    {
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        // 出題中のクイズは削除不可
        if ($quiz->status === 'active') {
            return response()->json([
                'message' => '出題中のクイズは削除できません。先に非公開にしてください。'
            ], 422);
        }

        DB::transaction(function () use ($quiz) {

            // 生徒の回答結果を削除
            $quiz->results()->delete();

            // クイズに紐づく問題を削除
            $quiz->questions()->delete();

            // クイズとクラスの関連を削除
            $quiz->classes()->detach();

            // クイズ本体を削除
            $quiz->delete();
        });

        return response()->json([
            'message' => 'クイズと回答データを削除しました。',
        ]);
    }



    public function activate(Quiz $quiz)
    {
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        $quiz->update([
            'status' => 'active',
        ]);

        return response()->json([
            'message' => 'クイズを出題しました',
            'quiz' => $quiz,
        ]);
    }
    public function deactivate(Quiz $quiz)
    {
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        $quiz->update([
            'status' => 'draft',
        ]);

        return response()->json([
            'message' => 'クイズを非公開にしました',
            'quiz' => $quiz,
        ]);
    }


    /**
     * 生徒が教材のクイズを取得
     */

    public function studentQuiz(Lesson $lesson)
    {
        if (Auth::user()->role !== 'student') {
            abort(403);
        }

        $quiz = Quiz::with([
            'questions' => function ($query) {
                $query->select(
                    'id',
                    'quiz_id',
                    'question',
                    'option_a',
                    'option_b',
                    'option_c',
                    'option_d',
                    'sort_order'
                );
            }
        ])
            ->where('lesson_id', $lesson->id)
            ->first();

        if (!$quiz) {
            return response()->json(null);
        }

        return response()->json($quiz);
    }


    /**
     * 生徒のクイズ回答を採点
     */
    public function submitStudentQuiz(Request $request, Lesson $lesson)
    {
        if (Auth::user()->role !== 'student') {
            abort(403);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        $quiz = Quiz::with('questions')
            ->where('lesson_id', $lesson->id)
            ->first();

        if (!$quiz) {
            return response()->json([
                'message' => 'この教材にはクイズがありません。'
            ], 404);
        }

        $correctCount = 0;
        $totalCount = $quiz->questions->count();

        foreach ($quiz->questions as $question) {

            $studentAnswer =
                $validated['answers'][$question->id] ?? null;

            if ($studentAnswer === $question->correct_answer) {
                $correctCount++;
            }
        }

        $score = $totalCount > 0
            ? round(($correctCount / $totalCount) * 100)
            : 0;

        $result = \App\Models\QuizResult::updateOrCreate(
            [
                'quiz_id' => $quiz->id,
                'student_id' => Auth::id(),
            ],
            [
                'score' => $score,
                'correct_count' => $correctCount,
                'total_count' => $totalCount,
                'answered_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'クイズを採点しました。',
            'result' => $result,
        ]);
    }

    public function activeQuizzes()
    {
        if (Auth::user()->role !== 'student') {
            abort(403);
        }

        $student = Auth::user();

        $classIds = $student->schoolClasses()
            ->pluck('classes.id');

        $quizzes = Quiz::with([
            'questions' => function ($query) {
                $query->select(
                    'id',
                    'quiz_id',
                    'question',
                    'option_a',
                    'option_b',
                    'option_c',
                    'option_d',
                    'sort_order'
                );
            }
        ])
            ->where('status', 'active')
            ->whereHas('classes', function ($query) use ($classIds) {
                $query->whereIn('classes.id', $classIds);
            })
            ->whereDoesntHave('results', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->latest()
            ->get();

        return response()->json($quizzes);
    }

    public function submitRealtimeQuiz(Request $request, Quiz $quiz)
    {
        // 生徒だけ利用可能
        if (Auth::user()->role !== 'student') {
            abort(403);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        $student = Auth::user();

        // 生徒が所属しているクラスを取得
        $classIds = $student->schoolClasses()
            ->pluck('classes.id');

        // このクイズの対象クラスに生徒が所属しているか確認
        $isTargetStudent = $quiz->classes()
            ->whereIn('classes.id', $classIds)
            ->exists();

        if (!$isTargetStudent) {
            abort(403);
        }

        // 出題中のクイズだけ回答可能
        if ($quiz->status !== 'active') {
            return response()->json([
                'message' => 'このクイズは現在回答できません。'
            ], 400);
        }

        // 正解を含む問題を取得
        $questions = $quiz->questions()->get();

        $correctCount = 0;
        $totalCount = $questions->count();

        foreach ($questions as $question) {

            // 生徒が選んだ回答
            $studentAnswer =
                $validated['answers'][$question->id] ?? null;

            // 正解と比較
            if ($studentAnswer === $question->correct_answer) {
                $correctCount++;
            }
        }

        // 正答率
        $score = $totalCount > 0
            ? round(($correctCount / $totalCount) * 100)
            : 0;

        // 結果を保存
        $result = \App\Models\QuizResult::updateOrCreate(
            [
                'quiz_id' => $quiz->id,
                'student_id' => $student->id,
            ],
            [
                'score' => $score,
                'correct_count' => $correctCount,
                'total_count' => $totalCount,
                'answered_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'クイズを採点しました。',
            'result' => [
                'score' => $result->score,
                'correct_count' => $result->correct_count,
                'total_count' => $result->total_count,
            ],
        ]);
    }
}
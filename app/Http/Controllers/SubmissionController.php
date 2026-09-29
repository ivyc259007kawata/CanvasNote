<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    /**
     * 生徒の回答を保存する
     */
    public function save(
        Request $request,
        Lesson $lesson
    ) {
        // 生徒以外はアクセス禁止
        if (Auth::user()->role !== 'student') {
            abort(403);
        }

        $student = Auth::user();

        // この教材が、生徒の所属クラスに追加されているか確認
        $hasAccess = $student->schoolClasses()
            ->whereHas('lessons', function ($query) use ($lesson) {
                $query->where('lessons.id', $lesson->id);
            })
            ->exists();

        if (!$hasAccess) {
            abort(403);
        }

        // 送られてきた回答データを確認
        $validated = $request->validate([
            'pages' => 'required|array',
            'pages.*.page_number' => 'required|integer',
            'pages.*.content' => 'required|array',
        ]);

        // すでに回答があれば取得、なければ新しく作成
        $submission = Submission::firstOrCreate(
            [
                'lesson_id' => $lesson->id,
                'student_id' => $student->id,
            ],
            [
                'status' => 'draft',
            ]
        );

        // 回答を下書き状態にする
        $submission->update([
            'status' => 'draft',
            'submitted_at' => null,
            'score' => null,
            'comment' => null,
        ]);

        // 以前の回答データを削除
        $submission->elements()->delete();

        // 各ページの回答を保存
        foreach ($validated['pages'] as $page) {
            $submission->elements()->create([
                'page_number' => $page['page_number'],
                'element_type' => 'canvas',
                'content' => $page['content'],
            ]);
        }

        return response()->json([
            'message' => '回答を保存しました',
            'submission_id' => $submission->id,
        ]);
    }

    public function submit(
        Lesson $lesson
    ) {
        // 生徒以外はアクセス禁止
        if (Auth::user()->role !== 'student') {
            abort(403);
        }

        $student = Auth::user();

        // この教材が生徒の所属クラスに追加されているか確認
        $hasAccess = $student->schoolClasses()
            ->whereHas('lessons', function ($query) use ($lesson) {
                $query->where('lessons.id', $lesson->id);
            })
            ->exists();

        if (!$hasAccess) {
            abort(403);
        }

        // 保存済みの回答を取得
        $submission = Submission::where(
            'lesson_id',
            $lesson->id
        )
            ->where(
                'student_id',
                $student->id
            )
            ->first();

        // 回答がまだ保存されていない場合
        if (!$submission) {
            return response()->json([
                'message' => '先に回答を保存してください。'
            ], 422);
        }

        // 提出済みに変更
        $submission->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json([
            'message' => '回答を提出しました。',
            'submission_id' => $submission->id,
        ]);
    }

    /**
     * 生徒が提出を取り消す
     */
    public function withdraw(Lesson $lesson)
    {
        // 生徒以外はアクセス禁止
        if (Auth::user()->role !== 'student') {
            abort(403);
        }

        $student = Auth::user();

        // この教材が、生徒の所属クラスに追加されているか確認
        $hasAccess = $student->schoolClasses()
            ->whereHas('lessons', function ($query) use ($lesson) {
                $query->where('lessons.id', $lesson->id);
            })
            ->exists();

        if (!$hasAccess) {
            abort(403);
        }

        // 自分の提出データを取得
        $submission = Submission::where(
            'lesson_id',
            $lesson->id
        )
            ->where(
                'student_id',
                $student->id
            )
            ->first();

        // 提出データがない場合
        if (!$submission) {
            return response()->json([
                'message' => '提出データがありません。'
            ], 422);
        }

        // 提出済みでない場合
        if ($submission->status !== 'submitted') {
            return response()->json([
                'message' => '現在、提出を取り消せる状態ではありません。'
            ], 422);
        }

        // 提出を取り消して下書きに戻す
        $submission->update([
            'status' => 'draft',
            'submitted_at' => null,
        ]);

        return response()->json([
            'message' => '提出を取り消しました。',
            'submission_id' => $submission->id,
        ]);
    }

    /**
     * 生徒自身の保存済み回答を取得する
     */
    public function show(Lesson $lesson)
    {
        // 生徒以外はアクセス禁止
        if (Auth::user()->role !== 'student') {
            abort(403);
        }

        $student = Auth::user();

        // この教材が、生徒の所属クラスに追加されているか確認
        $hasAccess = $student->schoolClasses()
            ->whereHas('lessons', function ($query) use ($lesson) {
                $query->where('lessons.id', $lesson->id);
            })
            ->exists();

        if (!$hasAccess) {
            abort(403);
        }

        // 生徒自身の保存済み回答を取得
        $submission = Submission::with('elements')
            ->where('lesson_id', $lesson->id)
            ->where('student_id', $student->id)
            ->first();

        // まだ回答を保存していない場合
        if (!$submission) {
            return response()->json([
                'submission' => null,
                'elements' => [],
            ]);
        }

        return response()->json([
            'submission' => [
                'id' => $submission->id,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at,
                'score' => $submission->score,
                'comment' => $submission->comment,
            ],
            'elements' => $submission->elements,
        ]);
    }

    /**
     * 先生が採点結果を保存する
     */
    public function grade(
        Request $request,
        Submission $submission
    ) {
        // 先生以外はアクセス禁止
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        // 点数・コメントを確認
        $validated = $request->validate([
            'score' => 'nullable|integer|min:0|max:100',
            'comment' => 'nullable|string',
        ]);

        // 採点結果を保存
        $submission->update([
            'score' => $validated['score'] ?? null,
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json([
            'message' => '採点結果を保存しました。',
            'submission' => [
                'id' => $submission->id,
                'score' => $submission->score,
                'comment' => $submission->comment,
            ],
        ]);
    }

    /**
     * 先生が採点結果を返却する
     */
    public function returnSubmission(
        Submission $submission
    ) {
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        if ($submission->status !== 'submitted') {
            return response()->json([
                'message' => '提出済みの回答のみ返却できます。'
            ], 422);
        }

        $submission->update([
            'status' => 'returned',
        ]);

        return response()->json([
            'message' => '採点結果を返却しました。',
            'submission' => [
                'id' => $submission->id,
                'status' => $submission->status,
                'score' => $submission->score,
                'comment' => $submission->comment,
            ],
        ]);
    }

    public function index(Lesson $lesson)
    {
        // 先生以外はアクセス禁止
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        // この教材が割り当てられているクラスを取得
        $classes = $lesson->schoolClasses()
            ->with('users')
            ->get();

        // クラスに所属している生徒を取得
        $students = $classes
            ->pluck('users')
            ->flatten()
            ->where('role', 'student')
            ->unique('id')
            ->values();

        // 各生徒の提出状況を作成
        $submissions = $students->map(function ($student) use ($lesson) {

            $submission = Submission::with('elements')
                ->where('lesson_id', $lesson->id)
                ->where('student_id', $student->id)
                ->first();

            return [
                'id' => $submission?->id,
                'lesson_id' => $lesson->id,
                'student_id' => $student->id,

                'status' =>
                    $submission?->status ?? 'not_submitted',

                'score' =>
                    $submission?->score,

                'comment' =>
                    $submission?->comment,

                'submitted_at' =>
                    $submission?->submitted_at,

                'created_at' =>
                    $submission?->created_at,

                'updated_at' =>
                    $submission?->updated_at,

                'student' =>
                    $student,

                'elements' =>
                    $submission?->elements ?? [],
            ];
        });

        return response()->json(
            $submissions->values()
        );
    }

}


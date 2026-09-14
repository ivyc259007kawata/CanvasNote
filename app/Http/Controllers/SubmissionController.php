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

    public function index(Lesson $lesson)
    {
        // 先生以外はアクセス禁止
        if (Auth::user()->role !== 'teacher') {
            abort(403);
        }

        // この教材の提出一覧を取得

        $submissions = Submission::with([
            'student',
            'elements'
        ])
            ->where('lesson_id', $lesson->id)
            ->orderBy('created_at')
            ->get();

        return response()->json($submissions);
    }
}


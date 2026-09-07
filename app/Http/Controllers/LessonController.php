<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lessons = Lesson::all();

        return view('lessons.index', compact('lessons'));
    }

    public function apiIndex()
    {
        $lessons = Lesson::where('teacher_id', Auth::id())->get();

        return response()->json($lessons);
    }


    public function apiStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $lesson = Lesson::create([
            'teacher_id' => Auth::id(),
            'title' => $request->title,
            'description' => null,
            'is_public' => false,
        ]);

        return response()->json($lesson, 201);
    }

    public function apiUpdate(Request $request, Lesson $lesson)
    {
        // 自分の教材か確認
        if ($lesson->teacher_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $lesson->update([
            'title' => $request->title,
        ]);

        return response()->json($lesson);
    }

    public function apiDestroy(Lesson $lesson)
    {
        // 自分の教材か確認
        if ($lesson->teacher_id !== Auth::id()) {
            abort(403);
        }

        $lesson->delete();

        return response()->json([
            'message' => '教材を削除しました'
        ]);
    }

    public function apiTogglePublish(Lesson $lesson)
    {
        // 自分の教材か確認
        if ($lesson->teacher_id !== Auth::id()) {
            abort(403);
        }

        // 公開 ⇔ 非公開を切り替える
        $lesson->update([
            'is_public' => !$lesson->is_public,
        ]);

        return response()->json($lesson);
    }

    public function apiDuplicate(Lesson $lesson)
    {
        // 自分の教材か確認
        if ($lesson->teacher_id !== Auth::id()) {
            abort(403);
        }

        $copy = Lesson::create([
            'teacher_id' => Auth::id(),
            'title' => $lesson->title . '（コピー）',
            'description' => $lesson->description,
            'is_public' => false,
        ]);

        return response()->json($copy, 201);
    }

    public function apiSaveCanvas(Request $request, Lesson $lesson)
    {
        // 自分の教材か確認
        if ($lesson->teacher_id !== Auth::id()) {
            abort(403);
        }

        // 送られてきたデータを確認
        $request->validate([
            'pages' => 'required|array',
            'pages.*.page_number' => 'required|integer|min:1',
            'pages.*.content' => 'nullable|array',
        ]);

        // 既存のCanvasデータを削除
        $lesson->canvasElements()->delete();

        // 全ページを保存
        foreach ($request->pages as $page) {

            $lesson->canvasElements()->create([
                'page_number' => $page['page_number'],
                'element_type' => 'canvas',
                'content' => $page['content'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Canvasを保存しました'
        ]);
    }

    public function apiGetCanvas(Lesson $lesson)
    {
        // 自分の教材か確認
        if ($lesson->teacher_id !== Auth::id()) {
            abort(403);
        }

        $pages = $lesson->canvasElements()
            ->orderBy('page_number')
            ->get();

        return response()->json([
            'pages' => $pages
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //教材作成
        return view('lessons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
        ]);

        Lesson::create([
            'teacher_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'is_public' => $request->has('is_public'),
        ]);

        return redirect()->route('lessons.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        return view('lessons.show', compact('lesson'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $lesson = Lesson::findOrFail($id);

        // 自分が作成した教材だけ変更できるようにする
        if ($lesson->teacher_id !== Auth::id()) {
            abort(403);
        }

        $lesson->update([
            'title' => $request->title,
            'description' => $request->description,
            'is_public' => $request->has('is_public'),
        ]);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

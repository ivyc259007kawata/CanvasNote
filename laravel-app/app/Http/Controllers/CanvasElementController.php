<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CanvasElement;

class CanvasElementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $lessonId = $request->query('lesson_id');

        $elements = CanvasElement::where('lesson_id', $lessonId)->get();

        return response()->json($elements);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'element_type' => 'required|string',
            'x' => 'required|integer',
            'y' => 'required|integer',
            'width' => 'nullable|integer',
            'height' => 'nullable|integer',
            'content' => 'nullable',
        ]);

        $element = CanvasElement::create($validated);

        return response()->json($element);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

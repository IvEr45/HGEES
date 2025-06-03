<?php

namespace App\Http\Controllers;

use App\Models\Line;
use Illuminate\Http\Request;

class LineController extends Controller
{
    public function index() {
        return Line::all();
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'coordinates' => 'required|array',
            'coordinates.*.lat' => 'required|numeric',
            'coordinates.*.lng' => 'required|numeric',
            'stroke_color' => 'nullable|string',
            'stroke_opacity' => 'nullable|numeric|between:0,1',
            'stroke_weight' => 'nullable|integer|min:1',
        ]);

        return Line::create($validated);
    }

    public function show($id) {
        return Line::findOrFail($id);
    }

    public function update(Request $request, $id) {
        $line = Line::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'coordinates' => 'required|array',
            'coordinates.*.lat' => 'required|numeric',
            'coordinates.*.lng' => 'required|numeric',
            'stroke_color' => 'nullable|string',
            'stroke_opacity' => 'nullable|numeric|between:0,1',
            'stroke_weight' => 'nullable|integer|min:1',
        ]);

        $line->update($validated);
        return $line;
    }

    public function destroy($id) {
        $line = Line::findOrFail($id);
        $line->delete();
        return response()->json(['message' => 'Line deleted successfully']);
    }
}


<?php

namespace App\Http\Controllers;

use App\Models\Polygon;
use Illuminate\Http\Request;

class PolygonController extends Controller
{
    public function index() {
        return Polygon::all();
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
            'fill_color' => 'nullable|string',
            'fill_opacity' => 'nullable|numeric|between:0,1',
        ]);

        return Polygon::create($validated);
    }

    public function show($id) {
        return Polygon::findOrFail($id);
    }

    public function update(Request $request, $id) {
        $polygon = Polygon::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'coordinates' => 'required|array',
            'coordinates.*.lat' => 'required|numeric',
            'coordinates.*.lng' => 'required|numeric',
            'stroke_color' => 'nullable|string',
            'stroke_opacity' => 'nullable|numeric|between:0,1',
            'stroke_weight' => 'nullable|integer|min:1',
            'fill_color' => 'nullable|string',
            'fill_opacity' => 'nullable|numeric|between:0,1',
        ]);

        $polygon->update($validated);
        return $polygon;
    }

    public function destroy($id) {
        $polygon = Polygon::findOrFail($id);
        $polygon->delete();
        return response()->json(['message' => 'Polygon deleted successfully']);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use Illuminate\Http\Request;

class MarkerController extends Controller
{
    public function index() {
        return Marker::all();
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        return Marker::create($validated);
    }

    public function show($id) {
        return Marker::findOrFail($id);
    }

    public function update(Request $request, $id) {
        $marker = Marker::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $marker->update($validated);
        return $marker;
    }

    public function destroy($id) {
        $marker = Marker::findOrFail($id);
        $marker->delete();
        return response()->json(['message' => 'Marker deleted successfully']);
    }
}
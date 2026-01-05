<?php

namespace App\Http\Controllers;

use App\Models\County;
use Illuminate\Http\Request;

class CountyController extends Controller
{
    public function index(Request $request)
    {
        $query = County::query();

        // Opcionális needle keresési paraméter
        if ($request->has('needle')) {
            $query->where('name', 'LIKE', '%' . $request->needle . '%');
        }

        $counties = $query->get();
        
        return response()->json($counties);
    }

    public function show($id)
    {
        $county = County::with('places')->findOrFail($id);
        return response()->json($county);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:counties,name',
        ]);

        $county = County::create($validated);

        return response()->json([
            'message' => 'County created successfully',
            'data' => $county
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $county = County::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:counties,name,' . $id,
        ]);

        $county->update($validated);

        return response()->json([
            'message' => 'County updated successfully',
            'data' => $county
        ]);
    }

    public function destroy($id)
    {
        $county = County::findOrFail($id);
        $county->delete();

        return response()->json([
            'message' => 'County deleted successfully'
        ]);
    }
}

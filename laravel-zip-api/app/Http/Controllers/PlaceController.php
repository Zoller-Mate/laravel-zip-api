<?php

namespace App\Http\Controllers;

use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlaceController extends Controller
{
    public function index(Request $request)
    {
        // Ha distinct_letters paraméter van, visszaadjuk a rendelkezésre álló kezdőbetűket
        if ($request->has('distinct_letters') && $request->distinct_letters == 'true') {
            $letters = Place::select(DB::raw('DISTINCT UPPER(SUBSTRING(name, 1, 1)) as letter'))
                ->orderBy('letter')
                ->pluck('letter')
                ->toArray();
            
            return response()->json($letters);
        }

        $query = Place::with(['county', 'postalCodes']);

        // Szűrés megye szerint
        if ($request->has('county_id')) {
            $query->where('county_id', $request->county_id);
        }

        // Szűrés kezdőbetű szerint
        if ($request->has('letter')) {
            $query->where('name', 'LIKE', $request->letter . '%');
        }

        $places = $query->get();
        
        return response()->json($places);
    }

    public function show($id)
    {
        $place = Place::with(['county', 'postalCodes'])->findOrFail($id);
        return response()->json($place);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'county_id' => 'required|exists:counties,id',
        ]);

        $place = Place::create($validated);
        $place->load(['county', 'postalCodes']);

        return response()->json([
            'message' => 'Place created successfully',
            'data' => $place
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $place = Place::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'county_id' => 'sometimes|required|exists:counties,id',
        ]);

        $place->update($validated);
        $place->load(['county', 'postalCodes']);

        return response()->json([
            'message' => 'Place updated successfully',
            'data' => $place
        ]);
    }

    public function destroy($id)
    {
        $place = Place::findOrFail($id);
        $place->delete();

        return response()->json([
            'message' => 'Place deleted successfully'
        ]);
    }
}

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
            $query = Place::select(DB::raw('DISTINCT UPPER(SUBSTRING(name, 1, 1)) as letter'));
            
            // Szűrés megye szerint a distinct letters esetén is
            if ($request->has('county_id')) {
                $query->where('county_id', $request->county_id);
            }
            
            $letters = $query->orderBy('letter')
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
            'postal_code' => 'required|string|max:10',
        ]);

        $place = Place::create([
            'name' => $validated['name'],
            'county_id' => $validated['county_id'],
        ]);

        // Create postal code for this place
        $place->postalCodes()->create([
            'postal_code' => $validated['postal_code'],
        ]);

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
            'postal_code' => 'sometimes|required|string|max:10',
        ]);

        $place->update([
            'name' => $validated['name'] ?? $place->name,
            'county_id' => $validated['county_id'] ?? $place->county_id,
        ]);

        // Update or create postal code
        if (isset($validated['postal_code'])) {
            // Get the first postal code or create new one
            $postalCode = $place->postalCodes()->first();
            if ($postalCode) {
                $postalCode->update(['postal_code' => $validated['postal_code']]);
            } else {
                $place->postalCodes()->create(['postal_code' => $validated['postal_code']]);
            }
        }

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

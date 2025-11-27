<?php

namespace App\Http\Controllers;

use App\Models\County;
use App\Models\Place;
use App\Models\PostalCode;
use Illuminate\Http\Request;

class PostalCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
        public function index()
    {
        $postalCodes = PostalCode::with('place.county')->get();
        return response()->json($postalCodes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'postal_code' => 'required',
            'place_name' => 'required',
            'county_name' => 'required',
        ]);

        $county = County::firstOrCreate(['name' => $request->county_name]);
        $place = Place::firstOrCreate([
            'name' => $request->place_name,
            'county_id' => $county->id
        ]);
        $postal = PostalCode::create([
            'postal_code' => $request->postal_code,
            'place_id' => $place->id
        ]);

        return response()->json($postal, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $postalCode = PostalCode::with('place.county')->findOrFail($id);
        return response()->json($postalCode);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $postal = PostalCode::findOrFail($id);

        $request->validate([
            'postal_code' => 'sometimes|required|string',
            'place_name' => 'sometimes|required|string',
            'county_name' => 'sometimes|required|string',
        ]);

        $placeId = $postal->place_id;

        if ($request->has('county_name') && $request->has('place_name')) {
            $county = County::firstOrCreate(['name' => $request->county_name]);
            $place = Place::firstOrCreate([
                'name' => $request->place_name,
                'county_id' => $county->id
            ]);
            $placeId = $place->id;
        }

        $updateData = [];
        
        if ($request->has('postal_code')) {
            $updateData['postal_code'] = $request->postal_code;
        }
        
        if ($placeId !== $postal->place_id) {
            $updateData['place_id'] = $placeId;
        }

        if (!empty($updateData)) {
            $postal->update($updateData);
        }

        $postal->refresh();
        $postal->load('place.county');

        return response()->json([
            'message' => 'Postal code updated successfully',
            'data' => $postal
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $postal = PostalCode::findOrFail($id);
        $postal->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
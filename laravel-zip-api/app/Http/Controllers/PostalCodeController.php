<?php

namespace App\Http\Controllers;

use App\Models\County;
use App\Models\Place;
use App\Models\PostalCode;
use Illuminate\Http\Request;

class PostalCodeController extends Controller
{
    /**
     * @api {get} /postal-codes Get all postal codes
     * @apiName GetPostalCodes
     * @apiGroup PostalCodes
     * @apiVersion 1.0.0
     *
     * @apiSuccess {Object[]} postal_codes List of postal codes
     * @apiSuccess {Number} postal_codes.id Postal code ID
     * @apiSuccess {String} postal_codes.postal_code The postal code
     * @apiSuccess {Number} postal_codes.place_id Place ID
     * @apiSuccess {Object} postal_codes.place Place details
     * @apiSuccess {String} postal_codes.place.name Place name
     * @apiSuccess {Object} postal_codes.place.county County details
     * @apiSuccess {String} postal_codes.place.county.name County name
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     [
     *       {
     *         "id": 1,
     *         "postal_code": "1011",
     *         "place_id": 1,
     *         "place": {
     *           "id": 1,
     *           "name": "Budapest",
     *           "county": {
     *             "id": 1,
     *             "name": "Budapest"
     *           }
     *         }
     *       }
     *     ]
     */
    public function index()
    {
        $postalCodes = PostalCode::with('place.county')->get();
        return response()->json($postalCodes);
    }

    /**
     * @api {post} /postal-codes Create a new postal code
     * @apiName CreatePostalCode
     * @apiGroup PostalCodes
     * @apiVersion 1.0.0
     * @apiPermission authenticated
     *
     * @apiHeader {String} Authorization Bearer token
     *
     * @apiBody {String} postal_code The postal code
     * @apiBody {String} place_name Name of the place
     * @apiBody {String} county_name Name of the county
     *
     * @apiSuccess {Number} id Postal code ID
     * @apiSuccess {String} postal_code The postal code
     * @apiSuccess {Number} place_id Place ID
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 201 Created
     *     {
     *       "id": 1,
     *       "postal_code": "1011",
     *       "place_id": 1
     *     }
     *
     * @apiError (401) Unauthenticated User is not authenticated
     * @apiError (422) ValidationError Validation failed
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
     * @api {get} /postal-codes/:id Get a postal code by ID
     * @apiName GetPostalCode
     * @apiGroup PostalCodes
     * @apiVersion 1.0.0
     *
     * @apiParam {Number} id Postal code unique ID
     *
     * @apiSuccess {Number} id Postal code ID
     * @apiSuccess {String} postal_code The postal code
     * @apiSuccess {Number} place_id Place ID
     * @apiSuccess {Object} place Place details
     * @apiSuccess {String} place.name Place name
     * @apiSuccess {Object} place.county County details
     * @apiSuccess {String} place.county.name County name
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "id": 1,
     *       "postal_code": "1011",
     *       "place_id": 1,
     *       "place": {
     *         "id": 1,
     *         "name": "Budapest",
     *         "county": {
     *           "id": 1,
     *           "name": "Budapest"
     *         }
     *       }
     *     }
     *
     * @apiError (404) NotFound Postal code not found
     */
    public function show(string $id)
    {
        $postalCode = PostalCode::with('place.county')->findOrFail($id);
        return response()->json($postalCode);
    }

    /**
     * @api {put} /postal-codes/:id Update a postal code
     * @apiName UpdatePostalCode
     * @apiGroup PostalCodes
     * @apiVersion 1.0.0
     * @apiPermission authenticated
     *
     * @apiHeader {String} Authorization Bearer token
     *
     * @apiParam {Number} id Postal code unique ID
     *
     * @apiBody {String} [postal_code] The postal code
     * @apiBody {String} [place_name] Name of the place (requires county_name)
     * @apiBody {String} [county_name] Name of the county (requires place_name)
     *
     * @apiSuccess {String} message Success message
     * @apiSuccess {Object} data Updated postal code data
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "message": "Postal code updated successfully",
     *       "data": {
     *         "id": 1,
     *         "postal_code": "1012",
     *         "place_id": 1
     *       }
     *     }
     *
     * @apiError (401) Unauthenticated User is not authenticated
     * @apiError (404) NotFound Postal code not found
     * @apiError (422) ValidationError Validation failed
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
     * @api {delete} /postal-codes/:id Delete a postal code
     * @apiName DeletePostalCode
     * @apiGroup PostalCodes
     * @apiVersion 1.0.0
     * @apiPermission authenticated
     *
     * @apiHeader {String} Authorization Bearer token
     *
     * @apiParam {Number} id Postal code unique ID
     *
     * @apiSuccess {String} message Success message
     *
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "message": "Deleted successfully"
     *     }
     *
     * @apiError (401) Unauthenticated User is not authenticated
     * @apiError (404) NotFound Postal code not found
     */
    public function destroy(string $id)
    {
        $postal = PostalCode::findOrFail($id);
        $postal->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
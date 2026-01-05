<?php

namespace App\Http\Controllers;

use App\Models\County;
use Illuminate\Http\Request;

class CountyController extends Controller
{
    public function index()
    {
        return County::all();
    }

    public function show($id)
    {
        return County::findOrFail($id);
    }
}

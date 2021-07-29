<?php

namespace App\Http\Controllers;

use App\Quote;
use Illuminate\Http\Request;

class QuotesController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    public function random(Request $request)
    {
        $count = $request->get('count');

        if (!$count || $count === 1) {
            return response()->json(Quote::inRandomOrder()->first());
        }

        return Quote::inRandomOrder()->limit($count)->get();
    }

    public function create(Request $request)
    {
        Quote::create(['quote' => $request->get('quote')]);

        return response()->json(['message' => 'Quote successfully added']);
    }
}

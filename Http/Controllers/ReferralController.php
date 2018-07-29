<?php

namespace App\Http\Controllers;

use Auth;

class ReferralController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('referrals.index');
    }

    public function datatables()
    {
        $user = Auth::user();

        $query = $user->referrals();

        $datatables = datatables();
        $datatables = $datatables->of($query);

        return $datatables->toJson();
    }
}

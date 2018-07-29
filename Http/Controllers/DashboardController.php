<?php

namespace App\Http\Controllers;

use Auth;
use App\Investment;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        $balance = $user->bitcoin_balance;
        $totalInvested = $user->investments()->ofStatus(['started', 'completed'])->sum('amount');
        $totalProfit = $user->investments()->ofStatus(['started', 'completed'])->sum('profit');

        $data = [];

        $data['balance'] = $balance;
        $data['totalInvested'] = $totalInvested;
        $data['totalProfit'] = $totalProfit;

        return view('dashboard', $data);
    }
}

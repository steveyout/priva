<?php

namespace App\Http\Controllers;

use Auth;
use App\Investment;

class InvestmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('investments.index');
    }

    public function datatables()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $query = Investment::query();
        } else {
            $query = $user->investments()->where('status', '!=', 'waiting');
        }

        return datatables()->of($query)->toJson();
    }

    public function start(Investment $investment)
    {
        if (!$investment->isPending()) {
            abort(404);
        }

        $investment->status = Investment::STATUS_STARTED;
        $saved = $investment->save();

        $redirect = redirect()->back();

        if ($saved) {
            return $redirect->with('status:ok', 'The investment successfully updated.');
        } else {
            return $redirect->with('status:error', 'Unable to update investment.');
        }
    }

    public function reject(Investment $investment)
    {
        if (!$investment->isPending()) {
            abort(404);
        }

        $investment->status = Investment::STATUS_REJECTED;
        $saved = $investment->save();

        $redirect = redirect()->back();

        if ($saved) {
            return $redirect->with('status:ok', 'The investment successfully updated.');
        } else {
            return $redirect->with('status:error', 'Unable to update investment.');
        }
    }
}

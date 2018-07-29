<?php

namespace App\Http\Controllers;

use Auth;
use App\Transaction;
use App\ReferralCommission;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['complete', 'check']);
    }

    public function index()
    {
        return view('transactions.index');
    }

    public function datatables()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $query = Transaction::query();
        } else {
            $query = $user->transactions();
        }

        $datatables = datatables();
        $datatables = $datatables->of($query);
        $datatables->addColumn('bitcoin_address', function (Transaction $transaction) {
            if ('bitcoin' === $transaction->method) {
                return $transaction->methodTransaction->address;
            }
        });

        return $datatables->toJson();
    }

    public function complete(Transaction $transaction)
    {
        if (!$transaction->isWaiting()) {
            abort(404);
        }

        $address = $transaction->address;

        $uri = 'bitcoin:' . $address . '?' . http_build_query([
            'amount' => config('app.investment.bitcoin.min'),
            'label' => config('app.name') . ' Investment',
        ]);

        $qrCode = new QrCode();
        $qrCode->setText($uri);
        $qrCode->setSize(128);
        $qrCode->setMargin(0);

        $data = [];

        $data['id'] = $transaction->id;
        $data['expireTime'] = $transaction->expireTime();
        $data['address'] = $address;
        $data['uri'] = $uri;
        $data['qrCode'] = $qrCode;

        return view('transactions.bitcoin', $data);
    }

    public function check(Request $request, Transaction $transaction)
    {
        if (!$transaction->isWaiting()) {
            goto response;
        }

        $address = $transaction->address;

        $min = config('app.investment.bitcoin.min');

        $blockio = resolve('blockio');

        $balance = 0;

        // use this if you want to check confirmations
        // $transactions = $blockio->get_transactions(['type' => 'received', 'addresses' => $address]);
        //
        // foreach ($transactions->data->txs as $tx) {
        //     if ($tx->confirmations >= 1) {
        //         foreach ($tx->amounts_received as $amount_received) {
        //             $balance += $amount_received->amount;
        //         }
        //     }
        // }

        $result = $blockio->get_address_balance(['addresses' => $address]);

        $balance += $result->data->available_balance;
        $balance += $result->data->pending_received_balance;

        if ($balance < $min) {
            goto response;
        }

        $transaction->update([
            'amount' => $balance,
            'status' => 'completed',
        ]);

        $transaction->transactable->update([
            'amount' => $balance,
            'status' => 'pending',
        ]);

        $user = $transaction->user;

        if ($user->referrer) {
            ReferralCommission::addInvestment($user, $user->referrer, $balance);
        }

        response:

        if ($request->expectsJson()) {
            return response()->json(['status' => $transaction->status]);
        } else {
            return redirect()->back();
        }
    }
}

<?php

namespace App\Http\Controllers;

use Telegram;
use Auth;
use App\Withdrawal;

class WithdrawalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('withdrawals.index');
    }

    public function datatables()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $query = Withdrawal::query();
        } else {
            $query = $user->withdrawals();
        }

        $datatables = datatables();
        $datatables = $datatables->of($query);
        $datatables->addColumn('bitcoin_address', function (Withdrawal $withdrawal) {
            if ('bitcoin' === $withdrawal->method) {
                return $withdrawal->methodWithdrawal->address;
            }
        });

        return $datatables->toJson();
    }

    public function process(Withdrawal $withdrawal)
    {
        if (!$withdrawal->isPending()) {
            abort(404);
        }

        $blockio = resolve('blockio');

        $blockio->withdraw(['amounts' => $withdrawal->amount, 'to_addresses' => $withdrawal->methodWithdrawal->address]);

        $withdrawal->status = Withdrawal::STATUS_PROCEEDED;
        $saved = $withdrawal->save();

        if ($saved) {
            Telegram::sendMessage([
                'chat_id' => $withdrawal->user->telegramAccounts()->first()->telegram_chat_id,
                'parse_mode' => 'HTML',
                'text' => <<<EOL
Withdrawal of <b>฿{$withdrawal->amount}</b> Bitcoin has been processed.
EOL
            ]);
        }

        $redirect = redirect()->back();

        if ($saved) {
            return $redirect->with('status:ok', 'The withdrawal successfully updated.');
        } else {
            return $redirect->with('status:error', 'Unable to update withdrawal.');
        }
    }

    public function reject(Withdrawal $withdrawal)
    {
        if (!$withdrawal->isPending()) {
            abort(404);
        }

        $withdrawal->status = Withdrawal::STATUS_REJECTED;
        $saved = $withdrawal->save();

        $redirect = redirect()->back();

        if ($saved) {
            return $redirect->with('status:ok', 'The withdrawal successfully updated.');
        } else {
            return $redirect->with('status:error', 'Unable to update withdrawal.');
        }
    }
}

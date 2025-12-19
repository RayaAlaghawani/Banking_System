<?php
namespace App\Transactions\Handlers;

use App\Models\Transaction;

class TellerApprovalHandler extends BaseHandler
{
    protected function check(Transaction $transaction)
    {
        // مبالغ بين 500 و 5000 تحتاج موافقة Teller
        if ($transaction->amount >= 500 && $transaction->amount <= 5000) {
            $transaction->status = 'pending';
            $transaction->save();
            // أوقف السلسلة — ينتظر موافقة بشريّة
            return false;
        }
        return true;
    }
}

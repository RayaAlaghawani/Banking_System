<?php
namespace App\Transactions\Handlers;

use App\Models\Transaction;

class AutoApprovalHandler extends BaseHandler
{
    protected function check(Transaction $transaction)
    {
        if ($transaction->amount < 500) {
            $transaction->status = 'approved';
            $transaction->save();
        }

        return true;
    }
}

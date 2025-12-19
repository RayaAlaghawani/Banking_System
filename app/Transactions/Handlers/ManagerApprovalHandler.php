<?php
namespace App\Transactions\Handlers;

use App\Models\Transaction;

class ManagerApprovalHandler extends BaseHandler
{
    protected function check(Transaction $transaction)
    {
        // مبالغ أكبر من 5000 تحتاج موافقة Manager
        if ($transaction->amount > 5000) {
            $transaction->status = 'pending';
            $transaction->save();
            return false;
        }
        return true;
    }
}

<?php


namespace App\Transactions\Handlers;



use App\Models\Transaction;

class BalanceCheckHandler extends BaseHandler
{
    protected function check(Transaction $transaction)
    {
        $account = $transaction->fromAccount;

        if ($account->balance < $transaction->amount) {
            $transaction->update(['status' => 'rejected']);
            return false; // وقف السلسلة
        }

        return true; // استمرار السلسلة
    }

}

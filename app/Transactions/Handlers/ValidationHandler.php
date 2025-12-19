<?php
namespace App\Transactions\Handlers;

use App\Models\Transaction;

class ValidationHandler extends BaseHandler
{
    protected function check(Transaction $transaction)
    {
        if ($transaction->amount <= 0) {
            throw new \Exception("Amount must be greater than zero.");
        }

        if (!in_array($transaction->type, [
            Transaction::TYPE_DEPOSIT,
            Transaction::TYPE_WITHDRAW,
            Transaction::TYPE_TRANSFER,
        ])) {
            throw new \Exception("Invalid transaction type.");
        }

        // قواعد وجود الحسابات
        if ($transaction->type === Transaction::TYPE_WITHDRAW && !$transaction->from_account_id) {
            throw new \Exception("Withdraw requires from_account_id.");
        }
        if ($transaction->type === Transaction::TYPE_DEPOSIT && !$transaction->to_account_id) {
            throw new \Exception("Deposit requires to_account_id.");
        }
        if ($transaction->type === Transaction::TYPE_TRANSFER && (!$transaction->from_account_id || !$transaction->to_account_id)) {
            throw new \Exception("Transfer requires both from_account_id and to_account_id.");
        }

        return true;
    }
}

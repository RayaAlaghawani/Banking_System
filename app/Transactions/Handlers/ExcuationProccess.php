<?php
namespace App\Transactions\Handlers;

use App\Models\Account;
use App\Models\Transaction;
use App\Composition\AccountInterface;
use App\Composition\CompositionAccount;
use App\Composition\LeafAccount;
use App\Repositories\AccountRepository;

class ExcuationProccess extends BaseHandler
{
    protected AccountRepository $repo;

    public function __construct(AccountRepository $repo)
    {
        $this->repo = $repo;
    }

    protected function check(Transaction $transaction)
    {
        // إذا لم تكن المعاملة معتمدة، لا نفعل شيء
        if ($transaction->status !== 'approved') {
            return true;
        }
        $from = $this->repo->find($transaction->from_account_id);
        if (!$from) throw new \Exception("From account not found");
        $target = $transaction->to_account_id ? $this->repo->find($transaction->to_account_id) : null;
        $account = $this->wrapAccount($from);
        $amount = $transaction->amount;

        switch ($transaction->type) {
            case 'withdraw':
                $account->withdraw($amount);
                break;

            case 'deposit':
                $account->deposit($amount);
                break;

            case 'transfer':
                if (!$target) throw new \Exception("Target account not found");
                $t = $this->wrapAccount($target);

                if ($transaction->type === 'transfer') {
                    $account->withdraw($amount);
                    $t->deposit($amount);
                }

                $this->updateAccountRecursively($t);
                break;

            default:
                throw new \Exception("Invalid transaction type for execution");
        }

        $this->updateAccountRecursively($account);

        $transaction->status = 'completed';
        $transaction->save();

        return true;
    }
    /**
     * تغليف الحساب بالنمط المناسب (Leaf أو Composition)
     */
    public function wrapAccount($account): AccountInterface
    {
        // إذا لم يكن للحساب أبناء، فهو حساب مفرد
        if (empty($account->children) || $account->children->isEmpty()) {
            return new LeafAccount($account);
        }

        // إذا كان له أبناء، فهو حساب مركب ولكن لا نحتاج لتحميلهم جميعاً
        // لأننا سنقوم بالتحديث بشكل منفصل
        return new CompositionAccount($account); // لا تحميل الأطفال تلقائياً
    }    /**
     * تحديث الحساب وجميع حساباته الفرعية بشكل متكرر
     */
    protected function updateAccountRecursively(AccountInterface $account)
    {
        try {
            // بالنسبة لـ LeafAccount، احفظ النموذج مباشرة
            $model = $account->getModel();

            // تأكد من أن النموذج صالح
            if ($model->exists) {
                $model->save();
            } else {
                throw new \Exception("Account model does not exist in database");
            }

        } catch (\Exception $e) {
            error_log("Error updating account {$account->getId()}: " . $e->getMessage());
            throw $e;
        }
    }}

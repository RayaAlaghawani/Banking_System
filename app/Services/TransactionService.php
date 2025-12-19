<?php
namespace App\Services;
use App\Http\Requests\TransferRequest;
use App\Models\Transaction;
use App\Repositories\AccountRepository;
use App\Transactions\Handlers\ValidationHandler;
use App\Transactions\Handlers\BalanceCheckHandler;
use App\Transactions\Handlers\AutoApprovalHandler;
use App\Transactions\Handlers\TellerApprovalHandler;
use App\Transactions\Handlers\ManagerApprovalHandler;
use App\Transactions\Handlers\ExcuationProccess;
use Illuminate\Support\Facades\Auth;

class TransactionService
{protected TransactionService $repo;

    public function __construct(TransactionService $repo)
    {
        $this->repo = $repo;
    }

    public function createAndProcess( TransferRequest  $request)
    {
        $transaction = Transaction::create([
            'amount' => $request->amount,
            'type' => $request->type,
            'from_account_id' => $request->from_account_id?? null,
            'to_account_id' => $request->to_account_id?? null,
            //   'status'          => ,
        ]);

        $chain = $this->buildChain();
        $chain->handle($transaction);

        return $transaction->fresh();
    }

    private function buildChain()
    {
        //  $validation = new ValidationHandler();
        //     $balance    = new BalanceCheckHandler($this->repo);
        $auto = new AutoApprovalHandler();
        $teller = new TellerApprovalHandler();
        $manager = new ManagerApprovalHandler();
        $exec = new ExcuationProccess($this->repo);

        //    $validation
        //   $balance
        $auto
            ->setNext($teller)
            ->setNext($manager)
            ->setNext($exec);

        return $auto;
    }

    //عرض كل معاملات
    public function getAll(){
        $transcation = $this->repo->getAll();
        return $transcation;
    }

    //عرض معاملاتي
    public function get(){
        $user = Auth::user();
        $userId=$user->id;
        $parentModel = $this->repo->get($userId);
        return $parentModel;
    }

    //////////
    public function approveTransaction(int $transactionId): Transaction
    {
        $transaction = \App\Models\Transaction::find($transactionId);
        if (!$transaction) throw new \Exception("Transaction not found");

        if ($transaction->status !== 'pending') {
            throw new \Exception("Transaction state must be pending to approve");
        }

        // تغيير الحالة للموافقة
        $transaction->status = 'approved';
        $transaction->save();

        // تمرير المعاملة للسلسلة ليتم تنفيذها
        $chain = $this->buildChain();
        $chain->handle($transaction);

        return $transaction->fresh();
    }

}

<?php
namespace App\Composition;

use App\Models\Account;

class LeafAccount implements AccountInterface
{
    protected Account $model;

    public function __construct(Account $account)
    {
        $this->model = $account;
    }

    public function getModel(): Account
    {
        return $this->model;
    }

    public function withdraw(float $amount): void
    {
        if ($this->model->balance < $amount) {
            throw new \Exception("Insufficient funds in account {$this->getId()}. Balance: {$this->model->balance}, Amount: {$amount}");
        }

        $this->model->balance -= $amount;

        try {
            $saved = $this->model->save();
            if (!$saved) {
                throw new \Exception("Failed to save account after withdrawal");
            }
        } catch (\Exception $e) {
            throw new \Exception("Error saving account: " . $e->getMessage());
        }
    }

    public function deposit(float $amount): void
    {
        $this->model->balance += $amount;

        try {
            $saved = $this->model->save();
            if (!$saved) {
                throw new \Exception("Failed to save account after deposit");
            }
        } catch (\Exception $e) {
            throw new \Exception("Error saving account: " . $e->getMessage());
        }
    }

    public function transfer(AccountInterface $target, float $amount): void
    {
        $this->withdraw($amount);
        $target->deposit($amount);
    }

    public function getBalance(): float
    {
        // تأكد من تحديث القيمة من قاعدة البيانات
        $this->model->refresh();
        return (float) $this->model->balance;
    }

    public function getId(): int
    {
        return $this->model->id;
    }

    public function addChild(AccountInterface $child): void {}
    public function removeChild(int $childId): void {}
    public function getChild(int $childId): ?AccountInterface { return null; }

    public function refresh(): void
    {
        $this->model->refresh();
    }
}

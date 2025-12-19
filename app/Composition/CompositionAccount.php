<?php
namespace App\Composition;

use App\Models\Account;

class CompositionAccount implements AccountInterface
{
    protected Account $model;

    /** @var AccountInterface[] */
    protected array $children = [];

    public function __construct(Account $account)
    {
        $this->model = $account;

        // تأكد من تحميل العلاقة children
        if (!$account->relationLoaded('children')) {
            $account->load('children');
        }

        foreach ($account->children as $child) {
            // تأكد من تحميل children للطفل أيضاً إذا كان مطلوباً
            if (!$child->relationLoaded('children')) {
                $child->load('children');
            }

            $this->children[] =
                $child->children->isEmpty()
                    ? new LeafAccount($child)
                    : new CompositionAccount($child);
        }
    }    public function getId(): int
    {
        return $this->model->id;
    }

    public function getBalance(): float
    {
        // إذا كانت children فارغة، ارجع 0
        if (empty($this->children)) {
            return 0;
        }

        $total = 0;
        foreach ($this->children as $child) {
            $total += $child->getBalance();
        }
        return $total;
    }
    public function withdraw(float $amount): void
    {
        $remaining = $amount;

        foreach ($this->children as $child) {
            $childBalance = $child->getBalance();

            $take = min($childBalance, $remaining);

            if ($take > 0) {
                $child->withdraw($take);
                $remaining -= $take;
            }

            if ($remaining <= 0) break;
        }

        if ($remaining > 0) {
            throw new \Exception("Composite account {$this->getId()} has insufficient total funds");
        }
    }

    public function deposit(float $amount): void
    {
        if (empty($this->children))
            throw new \Exception("Composite account has no children to deposit into");

        $n = count($this->children);
        $each = floor(($amount / $n) * 100) / 100; // exact to cents
        $remaining = $amount;

        foreach ($this->children as $i => $child) {
            $give = ($i === $n - 1) ? $remaining : $each;
            $child->deposit($give);
            $remaining -= $give;
        }
    }


    /**
     * @throws \Exception
     */
    public function transfer(AccountInterface $target, float $amount): void
    {
        $this->withdraw($amount);
        $target->deposit($amount);
    }

    public function addChild(AccountInterface $child): void
    {
        $childModel = $child->getModel();
        $childModel->parent_id = $this->model->id;
        $childModel->save();

        $this->children[] = $child;
    }
    public function getModel(): Account
    {
        return $this->model;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function removeChild(int $childId): void
    {
        $childModel = Account::find($childId);
        if (!$childModel) {
            throw new \Exception("Child account not found");
        }

        if ($childModel->parent_id !== $this->model->id) {
            throw new \Exception("This child does not belong to this composite account");
        }

        $childModel->parent_id = null;
        $childModel->save();

        foreach ($this->children as $i => $child) {
            if ($child->getId() === $childId) {
                unset($this->children[$i]);
                $this->children = array_values($this->children);
                return;
            }
        }
    }

    public function getChild(int $childId): ?AccountInterface
    {
        foreach ($this->children as $child) {
            if ($child->getId() === $childId) {
                return $child;
            }
        }
        return null;
    }
}

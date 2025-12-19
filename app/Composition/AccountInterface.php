<?php
namespace App\Composition;

interface AccountInterface
{
    public function withdraw(float $amount): void;
    public function deposit(float $amount): void;
    public function transfer(AccountInterface $target, float $amount): void;
    public function getBalance(): float;
    public function getId(): int;

    public function addChild(AccountInterface $child): void;
    public function removeChild(int $childId): void;
    public function getChild(int $childId): ?AccountInterface;
}

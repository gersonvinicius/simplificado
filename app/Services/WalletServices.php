<?php

namespace App\Services;

use App\Repositories\WalletRepository;

class WalletServices
{
    protected $walletRepository;

    public function __construct(WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    public function getWalletByUserId($userId)
    {
        return $this->walletRepository->findByUserId($userId);
    }

    public function updateWalletBalance($userId, $amount)
    {
        return $this->walletRepository->updateBalance($userId, $amount);
    }
}

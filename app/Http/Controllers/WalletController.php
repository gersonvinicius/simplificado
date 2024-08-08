<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function show($userId)
    {
        $wallet = $this->walletService->getWalletByUserId($userId);
        return response()->json($wallet);
    }

    public function updateBalance(Request $request, $userId)
    {
        $validatedData = $request->validate([
            'amount' => 'required|numeric',
        ]);

        $result = $this->walletService->updateWalletBalance($userId, $validatedData['amount']);

        return response()->json(['success' => $result]);
    }
}


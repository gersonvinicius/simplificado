<?php

namespace App\Http\Controllers;

use App\Services\TransferServices;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    protected $transferService;

    public function __construct(TransferServices $transferService)
    {
        $this->transferService = $transferService;
    }

    public function transfer(Request $request)
    {
        $validatedData = $request->validate([
            'value' => 'required|numeric|min:0.01',
            'payer' => 'required|exists:users,id',
            'payee' => 'required|exists:users,id',
        ]);

        try {
            $transfer = $this->transferService->createTransfer(
                $validatedData['payer'],
                $validatedData['payee'],
                $validatedData['value']
            );

            return response()->json($transfer, 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}

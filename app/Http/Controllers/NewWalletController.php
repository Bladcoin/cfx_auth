<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddWalletRequest;
use App\Models\Wallet;

class NewWalletController extends Controller
{
    public function addNewWalletToUser(AddWalletRequest $request)
    {
        try {
            Wallet::create(
                [
                    "user_id" => $request->user_id,
                    "public_key" => $request->public_key,
                    "wallet_type" => $request->wallet_type
                ]
            );
            return response()->json([
                "status" => 200,
                "message" => "OK"
            ]);
        } catch (\Throwable $error) {
            $error = $error === "" ? "Произошла неизвестная ошибка! Попробуйте позже!" : $error;
            return response()->json([
                "status" => 400,
                "message" => $error
            ], 400);
        }
    }

    public function deleteWalletToUser($id)
    {
        try {
            Wallet::where("id", $id)->delete();
            return response()->json([
                "status" => 200,
                "message" => "OK"
            ]);
        } catch (\Throwable $error) {
            $error = $error === "" ? "Произошла неизвестная ошибка! Попробуйте позже!" : $error;
            return response()->json([
                "status" => 400,
                "message" => $error
            ], 400);
        }
    }


    public function getWalletsByUserId( $id) {
        return Wallet::where("user_id", $id)->get();
    }
}

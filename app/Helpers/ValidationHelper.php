<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Validator;

class ValidationHelper
{
    public static function register($data)
    {
        return Validator::make(
            $data,
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|numeric',
                'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            ],
            [
                'name.required' => 'Nama wajib diisi.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'phone.required' => 'Nomor telepon wajib diisi.',
                'phone.numeric' => 'Nomor telepon harus berupa angka.',
                'password.required' => 'Kata sandi wajib diisi.',
                'password.min' => 'Kata sandi minimal harus terdiri dari 8 karakter.',
                'password.regex' => 'Kata sandi harus mengandung setidaknya 1 huruf besar, 1 huruf kecil, 1 angka, dan 1 karakter khusus.',
            ],
        );
    }

    public static function login($data)
    {
        return Validator::make(
            $data,
            [
                'email' => 'required|email',
                'password' => 'required',
            ],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'password.required' => 'Kata sandi wajib diisi.',
            ],
        );
    }

    public static function validateTransaction($data, $isStore = false)
    {
        return Validator::make(
            $data,
            [
                'title' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
                'amount' => ($isStore ? 'required' : 'sometimes|required') . '|integer|min:0',
                'type' => ($isStore ? 'required' : 'sometimes|required') . '|in:income,expense',
                'category' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
                'transaction_date' => ($isStore ? 'required' : 'sometimes|required') . '|date',
            ],
            [
                'title.required' => 'Judul transaksi wajib diisi.',
                'title.string' => 'Judul transaksi harus berupa teks.',
                'title.max' => 'Judul transaksi maksimal 255 karakter.',
                'amount.required' => 'Jumlah transaksi wajib diisi.',
                'amount.integer' => 'Jumlah transaksi harus berupa angka bulat.',
                'amount.min' => 'Jumlah transaksi tidak boleh negatif.',
                'type.required' => 'Tipe transaksi wajib diisi.',
                'type.in' => 'Tipe transaksi harus income atau expense.',
                'category.required' => 'Kategori transaksi wajib diisi.',
                'category.string' => 'Kategori transaksi harus berupa teks.',
                'category.max' => 'Kategori transaksi maksimal 255 karakter.',
                'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
                'transaction_date.date' => 'Tanggal transaksi tidak valid.',
                'user_id.uuid' => 'ID pengguna harus berupa UUID yang valid.',
                'user_id.exists' => 'ID pengguna tidak ditemukan.',
            ]
        );
    }
}

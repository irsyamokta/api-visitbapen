<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Validator;

class ValidationHelper
{
    public static function validateUser($data, $isStore = false)
    {
        return Validator::make(
            $data,
            [
                'name' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
                'email' => ($isStore ? 'required' : 'sometimes|required') . '|email',
                'phone' => ($isStore ? 'required' : 'sometimes|required') . '|numeric',
                'instagram' => ($isStore ? 'nullable' : 'sometimes|nullable') . '|string',
                'password' => ($isStore ? 'required' : 'sometimes|required') . '|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            ],
            [
                'name.required' => 'Nama wajib diisi.',
                'name.string' => 'Nama harus berupa teks.',
                'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'phone.required' => 'Nomor telepon wajib diisi.',
                'phone.numeric' => 'Nomor telepon harus berupa angka.',
                'instagram.required' => 'Instagram wajib diisi.',
                'instagram.string' => 'Instagram harus berupa teks.',
                'password.required' => 'Kata sandi wajib diisi.',
                'password.min' => 'Kata sandi minimal harus terdiri dari 8 karakter.',
                'password.regex' => 'Kata sandi harus mengandung setidaknya 1 huruf besar, 1 huruf kecil, 1 angka, dan 1 karakter khusus.',
            ]
        );
    }

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

    // public static function validateTransaction($data, $isStore = false)
    // {
    //     return Validator::make(
    //         $data,
    //         [
    //             'title' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
    //             'amount' => ($isStore ? 'required' : 'sometimes|required') . '|integer|min:0',
    //             'type' => ($isStore ? 'required' : 'sometimes|required') . '|in:income,expense',
    //             'category' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
    //             'transaction_date' => ($isStore ? 'required' : 'sometimes|required') . '|date',
    //         ],
    //         [
    //             'title.required' => 'Judul transaksi wajib diisi.',
    //             'title.string' => 'Judul transaksi harus berupa teks.',
    //             'title.max' => 'Judul transaksi maksimal 255 karakter.',
    //             'amount.required' => 'Jumlah transaksi wajib diisi.',
    //             'amount.integer' => 'Jumlah transaksi harus berupa angka bulat.',
    //             'amount.min' => 'Jumlah transaksi tidak boleh negatif.',
    //             'type.required' => 'Tipe transaksi wajib diisi.',
    //             'type.in' => 'Tipe transaksi harus income atau expense.',
    //             'category.required' => 'Kategori transaksi wajib diisi.',
    //             'category.string' => 'Kategori transaksi harus berupa teks.',
    //             'category.max' => 'Kategori transaksi maksimal 255 karakter.',
    //             'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
    //             'transaction_date.date' => 'Tanggal transaksi tidak valid.',
    //             'user_id.uuid' => 'ID pengguna harus berupa UUID yang valid.',
    //             'user_id.exists' => 'ID pengguna tidak ditemukan.',
    //         ]
    //     );
    // }

    public static function validateTransaction($data, $isStore = false)
    {
        return Validator::make(
            $data,
            [
                'title' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
                'type' => ($isStore ? 'required' : 'sometimes|required') . '|in:income,expense',
                'category' => ($isStore ? 'required' : 'sometimes|required') . '|string',
                'amount' => ($isStore ? 'required' : 'sometimes|required') . '|numeric|min:0',
                'transaction_date' => ($isStore ? 'required' : 'sometimes|required') . '|date',
                'name' => 'nullable|string|max:255',
                'ticket_id' => 'nullable|string|exists:tickets,id',
                'quantity' => 'nullable|integer|min:1',
            ],
            [
                'title.required' => 'Judul wajib diisi.',
                'title.string' => 'Judul harus berupa teks.',
                'title.max' => 'Judul maksimal 255 karakter.',
                'type.required' => 'Tipe wajib diisi.',
                'type.in' => 'Tipe harus pendapatan atau pengeluaran.',
                'category.required' => 'Kategori wajib diisi.',
                'category.string' => 'Kategori harus berupa teks.',
                'amount.required' => 'Jumlah wajib diisi.',
                'amount.numeric' => 'Jumlah harus berupa angka.',
                'amount.min' => 'Jumlah tidak boleh negatif.',
                'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
                'transaction_date.date' => 'Tanggal transaksi tidak valid.',
                'name.string' => 'Nama harus berupa teks.',
                'name.max' => 'Nama maksimal 255 karakter.',
                'ticket_id.exists' => 'Tiket tidak valid.',
                'quantity.integer' => 'Jumlah pembelian harus berupa angka.',
                'quantity.min' => 'Jumlah pembelian minimal 1.',
            ]
        );
    }

    public static function validateArticle($data, $isStore = false)
    {
        return Validator::make(
            $data,
            [
                'title' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
                'content' => ($isStore ? 'required' : 'sometimes|required') . '|string',
                'writer' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
            ],
            [
                'title.required' => 'Judul artikel wajib diisi.',
                'title.string' => 'Judul artikel harus berupa teks.',
                'title.max' => 'Judul artikel maksimal 255 karakter.',
                'content.required' => 'Isi artikel wajib diisi.',
                'content.string' => 'Isi artikel harus berupa teks.',
                'writer.required' => 'Penulis artikel wajib diisi.',
                'writer.string' => 'Penulis artikel harus berupa teks.',
                'writer.max' => 'Penulis artikel maksimal 255 karakter.',
            ]
        );
    }

    public static function validateEvent($data, $isStore = false)
    {
        return Validator::make(
            $data,
            [
                'title' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
                'description' => ($isStore ? 'required' : 'sometimes|required') . '|string',
                'date' => ($isStore ? 'required' : 'sometimes|required') . '|date_format:Y-m-d|after_or_equal:today',
                'time' => ($isStore ? 'required' : 'sometimes|required') . '|date_format:H:i',
                'place' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
                'price' => ($isStore ? 'nullable' : 'sometimes|required') . '|numeric|min:0',
            ],
            [
                'title.required' => 'Judul acara wajib diisi.',
                'title.string' => 'Judul acara harus berupa teks.',
                'title.max' => 'Judul acara maksimal 255 karakter.',
                'description.required' => 'Deskripsi acara wajib diisi.',
                'description.string' => 'Deskripsi acara harus berupa teks.',
                'date.required' => 'Tanggal acara wajib diisi.',
                'date.date_format' => 'Format tanggal tidak valid.',
                'date.after_or_equal' => 'Tanggal acara harus setelah tanggal hari ini.',
                'time.required' => 'Waktu acara wajib diisi.',
                'time.date_format' => 'Format waktu tidak valid.',
                'place.required' => 'Tempat acara wajib diisi.',
                'place.string' => 'Tempat acara harus berupa teks.',
                'place.max' => 'Tempat acara maksimal 255 karakter.',
                'price.numeric' => 'Harga acara harus berupa angka.',
                'price.min' => 'Harga acara tidak boleh negatif.',
            ]
        );
    }

    public static function validateGallery($data, $isStore = false)
    {
        return Validator::make(
            $data,
            [
                'title' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
                'caption' => ($isStore ? 'required' : 'sometimes|required') . '|string',
            ],
            [
                'title.required' => 'Judul galeri wajib diisi.',
                'title.string' => 'Judul galeri harus berupa teks.',
                'title.max' => 'Judul galeri maksimal 255 karakter.',
                'caption.required' => 'Deskripsi galeri wajib diisi.',
                'caption.string' => 'Deskripsi galeri harus berupa teks.',
            ]
        );
    }

    public static function validatePackage($data, $isStore = false)
    {
        return Validator::make(
            $data,
            [
                'title' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
                'price' => ($isStore ? 'required' : 'sometimes|required') . '|numeric|min:0',
                'benefit' => ($isStore ? 'required' : 'sometimes|required') . '|string',
            ],
            [
                'title.required' => 'Judul paket wajib diisi.',
                'title.string' => 'Judul paket harus berupa teks.',
                'title.max' => 'Judul paket maksimal 255 karakter.',
                'price.required' => 'Harga paket wajib diisi.',
                'price.numeric' => 'Harga paket harus berupa angka.',
                'price.min' => 'Harga paket tidak boleh negatif.',
                'benefit.required' => 'Manfaat paket wajib diisi.',
                'benefit.string' => 'Manfaat paket harus berupa teks.',
            ]
        );
    }

    public static function validateSetting($data, $isStore = false)
    {
        return Validator::make(
            $data,
            [
                'name' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
                'category' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
            ],
            [
                'name.required' => 'Nama wajib diisi.',
                'name.string' => 'Nama harus berupa teks.',
                'name.max' => 'Nama maksimal 255 karakter.',
                'category.required' => 'Kategori wajib diisi.',
                'category.string' => 'Kategori harus berupa teks.',
                'category.max' => 'Kategori maksimal 255 karakter.',
            ]
        );
    }

    public static function validateTour($data, $isStore = false)
    {
        return Validator::make(
            $data,
            [
                'title' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
                'about' => ($isStore ? 'required' : 'sometimes|required') . '|string',
                'operational' => ($isStore ? 'required' : 'sometimes|required') . '|string',
                'location' => ($isStore ? 'required' : 'sometimes|required') . '|string',
                'start' => ($isStore ? 'required' : 'sometimes|required') . '|date_format:H:i',
                'end' => ($isStore ? 'required' : 'sometimes|required') . '|date_format:H:i',
                'facility' => ($isStore ? 'required' : 'sometimes|required') . '|string',
                'maps' => ($isStore ? 'required' : 'sometimes|required') . '|string',
                'price' => ($isStore ? 'required' : 'sometimes|required') . '|numeric|min:0',
            ],
            [
                'title.required' => 'Judul wajib diisi.',
                'title.string' => 'Judul harus berupa teks.',
                'title.max' => 'Judul maksimal 255 karakter.',
                'about.required' => 'Deskripsi wajib diisi.',
                'about.string' => 'Deskripsi harus berupa teks.',
                'operational.required' => 'Operasional wajib diisi.',
                'operational.string' => 'Operasional harus berupa teks.',
                'location.required' => 'Lokasi wajib diisi.',
                'location.string' => 'Lokasi harus berupa teks.',
                'start.required' => 'Jam mulai wajib diisi.',
                'start.date_format' => 'Jam mulai harus berupa waktu.',
                'end.required' => 'Jam selesai wajib diisi.',
                'end.date_format' => 'Jam selesai harus berupa waktu.',
                'facility.required' => 'Fasilitas wajib diisi.',
                'facility.string' => 'Fasilitas harus berupa teks.',
                'maps.required' => 'Maps wajib diisi.',
                'maps.string' => 'Maps harus berupa teks.',
                'price.required' => 'Harga wajib diisi.',
                'price.numeric' => 'Harga harus berupa angka.',
                'price.min' => 'Harga tidak boleh negatif.',
            ]
        );
    }

    public static function validateTicket($data, $isStore = false)
    {
        return Validator::make(
            $data,
            [
                'title' => ($isStore ? 'required' : 'sometimes|required') . '|string|max:255',
                'description' => ($isStore ? 'required' : 'sometimes|required') . '|string',
                'location' => ($isStore ? 'required' : 'sometimes|required') . '|string',
                'price' => ($isStore ? 'required' : 'sometimes|required') . '|numeric|min:0',
            ],
            [
                'title.required' => 'Judul tiket wajib diisi.',
                'title.string' => 'Judul tiket harus berupa teks.',
                'title.max' => 'Judul tiket maksimal 255 karakter.',
                'description.required' => 'Deskripsi tiket wajib diisi.',
                'description.string' => 'Deskripsi tiket harus berupa teks.',
                'location.required' => 'Lokasi tiket wajib diisi.',
                'location.string' => 'Lokasi tiket harus berupa teks.',
                'price.required' => 'Harga tiket wajib diisi.',
                'price.numeric' => 'Harga tiket harus berupa angka.',
                'price.min' => 'Harga tiket tidak boleh negatif.',
            ]
        );
    }

    public static function validateOrder($data)
    {
        return Validator::make(
            $data,
            [
                'ticket_id' => 'required|exists:tickets,id',
                'name' => 'required|string|max:255',
                'quantity' => 'required|integer|min:1',
                'total_price' => 'required|numeric|min:0',
            ],
            [
                'ticket_id.required' => 'Tiket wajib diisi.',
                'ticket_id.exists' => 'Tiket tidak ditemukan.',
                'name.required' => 'Nama wajib diisi.',
                'name.string' => 'Nama harus berupa teks.',
                'name.max' => 'Nama maksimal 255 karakter.',
                'quantity.required' => 'Jumlah wajib diisi.',
                'quantity.integer' => 'Jumlah harus berupa angka.',
                'quantity.min' => 'Jumlah tidak boleh negatif.',
                'total_price.required' => 'Total harga wajib diisi.',
                'total_price.numeric' => 'Total harga harus berupa angka.',
                'total_price.min' => 'Total harga tidak boleh negatif.',
            ]
        );
    }
}

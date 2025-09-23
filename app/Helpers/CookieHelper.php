<?php

namespace App\Helpers;

class CookieHelper
{
    public static function make($token, $minutes = 60 * 24)
    {
        return cookie(
            'token',
            $token,
            $minutes,
            '/',
            null,
            true,
            true,
            false,
            'Strict'
        );
    }

    public static function forget()
    {
        return cookie('token', '', -1);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class PreventDuplicateSubmission
{
    /**
     * Menolak submit berulang dengan payload yang sama dalam waktu singkat.
     * Ini melindungi aplikasi dari klik ganda saat jaringan lambat.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('POST') && ! $request->isMethod('PUT') && ! $request->isMethod('PATCH') && ! $request->isMethod('DELETE')) {
            return $next($request);
        }

        $userId = optional($request->user())->id ?: 'guest';
        $payload = $request->except([
            '_token',
            '_method',
            'password',
            'password_confirmation',
            'current_password',
        ]);

        ksort($payload);

        $fingerprint = sha1($request->method().'|'.$request->path().'|'.$userId.'|'.json_encode($payload));
        $key = 'duplicate-submit:'.$fingerprint;

        // Cache::add hanya berhasil jika key belum ada. File cache Laravel juga mendukung operasi ini.
        if (! Cache::add($key, true, now()->addSeconds(12))) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation', 'current_password']))
                ->withErrors(['duplicate_submit' => 'Permintaan yang sama sedang diproses. Jangan tekan tombol submit berulang kali.']);
        }

        return $next($request);
    }
}

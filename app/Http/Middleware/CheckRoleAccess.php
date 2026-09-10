<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Belum login, biarkan middleware 'auth' yang urus (harusnya sudah kepasang
        // sebelum middleware ini, tapi jaga-jaga saja).
        if (! $user) {
            return $next($request);
        }

        // Admin selalu full akses, gak perlu dicek lebih lanjut.
        if ($user->isAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        // Route tanpa nama (jarang terjadi di project ini) dibiarkan lewat.
        if (! $routeName) {
            return $next($request);
        }

        $config = config('role_access');

        // Route yang boleh diakses semua role, apapun rolenya.
        foreach ($config['always_allowed'] ?? [] as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return $next($request);
            }
        }

        $roleConfig = $config[$user->role] ?? null;

        // Role gak dikenal / gak ada aturan sama sekali -> tolak demi keamanan.
        if (! $roleConfig) {
            return $this->deny($request, 'Anda tidak memiliki akses untuk role ini.');
        }

        // Deny dicek duluan, deny menang walau ada di allow juga.
        foreach ($roleConfig['deny'] ?? [] as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return $this->deny($request, 'Anda tidak memiliki akses untuk melakukan aksi ini.');
            }
        }

        foreach ($roleConfig['allow'] ?? [] as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return $next($request);
            }
        }

        return $this->deny($request, 'Anda tidak memiliki akses ke halaman ini.');
    }

    /**
     * Tolak request dengan format yang sesuai:
     * - Request AJAX/fetch (kayak submit form hapus lewat JS) -> balas JSON,
     *   supaya JS bisa baca `data.message` dan tampilkan toast Bahasa Indonesia,
     *   bukan error "Unexpected token '<'" gara-gara nerima halaman HTML.
     * - Request browser biasa (buka URL langsung) -> tetap halaman error 403 biasa.
     */
    private function deny(Request $request, string $message): Response
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        abort(403, $message);
    }
}
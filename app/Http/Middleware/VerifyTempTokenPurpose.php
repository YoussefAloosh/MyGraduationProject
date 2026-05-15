<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTempTokenPurpose
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$expectedPurposes)
    {
        $tempToken = $request->header('temp_token') ?? $request->input('temp_token');

        if (!$tempToken) {
            return response()->json(['message' => 'Temp token is required'], 401);
        }

        try {
            $data = decrypt($tempToken);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid temp token'], 401);
        }

        if (now()->greaterThan($data['expires_at'])) {
            return response()->json(['message' => 'Temp token expired'], 401);
        }

        if (!isset($data['purpose']) || !in_array($data['purpose'], $expectedPurposes)) {
            return response()->json(['message' => 'Invalid token purpose'], 401);
        }

        $request->merge(['temp_token_data' => $data]);

        return $next($request);
    }
}

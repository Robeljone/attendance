<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QrAttendanceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QrStationController extends Controller
{
    public function station(): View
    {
        return view('admin.qr.station', [
            'ttl' => (int) config('attendance.qr_token_ttl_seconds', 60),
        ]);
    }

    public function token(Request $request): JsonResponse
    {
        $token = QrAttendanceToken::issue(
            stationName: $request->string('station', 'Main Lobby')->toString(),
            userId: $request->user()->id,
        );

        $payloadUrl = route('portal.attendance.scan-payload', ['token' => $token->token]);

        return response()->json([
            'token' => $token->token,
            'expires_at' => $token->expires_at->toIso8601String(),
            'payload_url' => $payloadUrl,
            'ttl' => (int) config('attendance.qr_token_ttl_seconds', 60),
        ]);
    }
}

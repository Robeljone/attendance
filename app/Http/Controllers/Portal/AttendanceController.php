<?php

namespace App\Http\Controllers\Portal;

use App\Actions\ClockInAction;
use App\Actions\ClockOutAction;
use App\Enums\AttendanceMethod;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\QrAttendanceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $request->user()->employee;

        abort_unless($employee, 403, 'No employee profile linked to this account.');

        $today = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->whereDate('work_date', today())
            ->first();

        $history = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->latest('work_date')
            ->limit(14)
            ->get();

        $verifiedToken = null;

        if ($request->filled('scanned_token')) {
            $token = $this->findValidQrToken($request->string('scanned_token')->toString());
            $verifiedToken = $token?->token;
        }

        return view('portal.attendance.index', [
            'employee' => $employee,
            'todayRecord' => $today,
            'history' => $history,
            'verifiedToken' => $verifiedToken,
        ]);
    }

    public function clockIn(Request $request, ClockInAction $clockIn): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);

        $token = $this->validatedQrToken($request);

        $clockIn->handle(
            $employee,
            AttendanceMethod::Qr,
            $request->ip() ?? '',
            'QR station: '.$token->station_name,
        );

        return back()->with('success', 'Clocked in successfully.');
    }

    public function clockOut(Request $request, ClockOutAction $clockOut): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);

        $token = $this->validatedQrToken($request);

        $clockOut->handle(
            $employee,
            AttendanceMethod::Qr,
            $request->ip() ?? '',
            'QR station: '.$token->station_name,
        );

        return back()->with('success', 'Clocked out successfully.');
    }

    public function scan(Request $request): RedirectResponse|JsonResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee, 403);

        $token = $this->findValidQrToken($request->validate([
            'token' => ['required', 'string'],
        ])['token']);

        if (! $token) {
            return $this->attendanceResponse($request, 'QR code is invalid or expired. Please scan again.', false);
        }

        return $this->attendanceResponse($request, 'Station QR verified. You can clock in or out.');
    }

    public function scanPayload(string $token): RedirectResponse
    {
        $valid = $this->findValidQrToken($token);

        if (! $valid) {
            return redirect()
                ->route('portal.attendance.index')
                ->with('error', 'QR code is invalid or expired. Please scan again.');
        }

        return redirect()
            ->route('portal.attendance.index', ['scanned_token' => $valid->token])
            ->with('success', 'Station QR verified. You can clock in or out.');
    }

    private function validatedQrToken(Request $request): QrAttendanceToken
    {
        $token = $this->findValidQrToken($request->validate([
            'token' => ['required', 'string'],
        ])['token']);

        abort_unless($token, 403, 'Scan a valid station QR code before clocking in or out.');

        return $token;
    }

    private function findValidQrToken(string $tokenValue): ?QrAttendanceToken
    {
        if (str_contains($tokenValue, '/scan/')) {
            $tokenValue = basename(parse_url($tokenValue, PHP_URL_PATH) ?: $tokenValue);
        }

        $token = QrAttendanceToken::query()->where('token', $tokenValue)->first();

        if (! $token || ! $token->isValid()) {
            return null;
        }

        return $token;
    }

    private function attendanceResponse(Request $request, string $message, bool $success = true): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $success ? 200 : 422);
        }

        return $success
            ? back()->with('success', $message)
            : back()->with('error', $message);
    }
}

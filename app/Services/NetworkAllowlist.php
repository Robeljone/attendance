<?php

namespace App\Services;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Log;

class NetworkAllowlist
{
    /**
     * @return array{enforced: bool, on_allowlist: bool, permitted: bool, cidrs: list<string>}
     */
    public function evaluate(string $ip): array
    {
        $settings = CompanySetting::current();
        $cidrs = array_values(array_map(
            'strval',
            $settings->allowed_ip_cidrs ?: config('attendance.allowed_ip_cidrs', []),
        ));
        $enforced = (bool) $settings->enforce_company_network;
        $onAllowlist = $this->matchesAllowlist($ip, $cidrs);

        return [
            'enforced' => $enforced,
            'on_allowlist' => $onAllowlist,
            'permitted' => ! $enforced || $onAllowlist,
            'cidrs' => $cidrs,
        ];
    }

    public function allows(string $ip): bool
    {
        $evaluation = $this->evaluate($ip);

        if ($evaluation['permitted']) {
            return true;
        }

        Log::info('Attendance blocked: client IP not on company network', [
            'ip' => $ip,
            'allowed' => $evaluation['cidrs'],
        ]);

        return false;
    }

    /**
     * @param  list<string>|null  $cidrs
     */
    public function matchesAllowlist(string $ip, ?array $cidrs = null): bool
    {
        $cidrs ??= array_values(array_map(
            'strval',
            CompanySetting::current()->allowed_ip_cidrs ?: config('attendance.allowed_ip_cidrs', []),
        ));

        if ($cidrs === []) {
            return false;
        }

        foreach ($cidrs as $cidr) {
            if ($this->ipInCidr($ip, (string) $cidr)) {
                return true;
            }
        }

        return false;
    }

    public function ipInCidr(string $ip, string $cidr): bool
    {
        if (! str_contains($cidr, '/')) {
            return $ip === $cidr;
        }

        [$subnet, $mask] = explode('/', $cidr, 2);
        $mask = (int) $mask;

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = -1 << (32 - $mask);

            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
            && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ipBin = inet_pton($ip);
            $subnetBin = inet_pton($subnet);

            if ($ipBin === false || $subnetBin === false) {
                return false;
            }

            $bytes = intdiv($mask, 8);
            $bits = $mask % 8;

            if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($subnetBin, 0, $bytes)) {
                return false;
            }

            if ($bits === 0) {
                return true;
            }

            $maskByte = (~(0xFF >> $bits)) & 0xFF;

            return (ord($ipBin[$bytes]) & $maskByte) === (ord($subnetBin[$bytes]) & $maskByte);
        }

        return false;
    }
}

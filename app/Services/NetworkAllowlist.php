<?php

namespace App\Services;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Log;

class NetworkAllowlist
{
    public function allows(string $ip): bool
    {
        $settings = CompanySetting::current();

        if (! $settings->enforce_company_network) {
            return true;
        }

        $cidrs = $settings->allowed_ip_cidrs ?: config('attendance.allowed_ip_cidrs', []);

        foreach ($cidrs as $cidr) {
            if ($this->ipInCidr($ip, (string) $cidr)) {
                return true;
            }
        }

        Log::info('Attendance blocked: client IP not on company network', [
            'ip' => $ip,
            'allowed' => $cidrs,
        ]);

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

<?php

namespace Tests\Unit;

use App\Services\NetworkAllowlist;
use PHPUnit\Framework\TestCase;

class NetworkAllowlistTest extends TestCase
{
    public function test_it_matches_ipv4_cidr_ranges(): void
    {
        $allowlist = new NetworkAllowlist;

        $this->assertTrue($allowlist->ipInCidr('192.168.1.50', '192.168.0.0/16'));
        $this->assertFalse($allowlist->ipInCidr('10.0.0.5', '192.168.0.0/16'));
        $this->assertTrue($allowlist->ipInCidr('127.0.0.1', '127.0.0.1/32'));
    }
}

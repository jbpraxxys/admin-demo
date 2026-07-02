<?php

namespace Tests\Feature;

use App\Services\HtpasswdService;
use Tests\TestCase;

class HtpasswdServiceTest extends TestCase
{
    private HtpasswdService $service;
    private string $testSlug = 'test-project-htpasswd';

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HtpasswdService();
    }

    protected function tearDown(): void
    {
        $path = storage_path("htpasswd/{$this->testSlug}");
        if (file_exists($path)) {
            unlink($path);
        }
        parent::tearDown();
    }

    public function test_write_creates_htpasswd_file(): void
    {
        $this->service->write($this->testSlug, 'secret123');
        $path = storage_path("htpasswd/{$this->testSlug}");
        $this->assertFileExists($path);
    }

    public function test_htpasswd_file_contains_demo_user(): void
    {
        $this->service->write($this->testSlug, 'secret123');
        $contents = file_get_contents(storage_path("htpasswd/{$this->testSlug}"));
        $this->assertStringStartsWith('demo:', $contents);
    }

    public function test_htpasswd_file_hash_verifies_password(): void
    {
        $password = 'mypassword';
        $this->service->write($this->testSlug, $password);
        $contents = trim(file_get_contents(storage_path("htpasswd/{$this->testSlug}")));
        [, $hash] = explode(':', $contents, 2);
        $this->assertStringStartsWith('{SHA}', $hash);
        $expectedHash = '{SHA}' . base64_encode(sha1($password, true));
        $this->assertEquals($expectedHash, $hash);
    }

    public function test_delete_removes_htpasswd_file(): void
    {
        $this->service->write($this->testSlug, 'secret123');
        $this->service->delete($this->testSlug);
        $this->assertFileDoesNotExist(storage_path("htpasswd/{$this->testSlug}"));
    }

    public function test_delete_is_safe_if_file_does_not_exist(): void
    {
        $this->service->delete('nonexistent-slug');
        $this->assertTrue(true);
    }
}

<?php
use PHPUnit\Framework\TestCase;

class PersonalizedGreetingTest extends TestCase
{
    private string $envPath;
    private string $originalEnv;
    private string $dummyPath = '/tmp/dummy_credentials.json';

    protected function setUp(): void
    {
        $this->envPath = dirname(__DIR__) . '/.env';
        $this->originalEnv = file_exists($this->envPath) ? file_get_contents($this->envPath) : '';
        file_put_contents($this->envPath, "TTS_CREDENTIALS_PATH={$this->dummyPath}\n");
    }

    protected function tearDown(): void
    {
        file_put_contents($this->envPath, $this->originalEnv);
    }

    public function testThrowsWhenCredentialsFileMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            "Text-to-Speech credentials file not found or unreadable at '{$this->dummyPath}'."
        );
        require_once __DIR__ . '/../functions/PersonalizedGreeting.php';
        new PersonalizedGreeting();
    }
}

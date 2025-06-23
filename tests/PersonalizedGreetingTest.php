<?php
use PHPUnit\Framework\TestCase;

class PersonalizedGreetingTest extends TestCase
{
    private ?string $originalTtsPath;
    private string $dummyPath = '/tmp/dummy_credentials.json';

    protected function setUp(): void
    {
        $this->originalTtsPath = getenv('TTS_CREDENTIALS_PATH') ?: null;
        putenv('TTS_CREDENTIALS_PATH=' . $this->dummyPath);
        $_ENV['TTS_CREDENTIALS_PATH'] = $this->dummyPath;
    }

    protected function tearDown(): void
    {
        if ($this->originalTtsPath !== null) {
            putenv('TTS_CREDENTIALS_PATH=' . $this->originalTtsPath);
            $_ENV['TTS_CREDENTIALS_PATH'] = $this->originalTtsPath;
        } else {
            putenv('TTS_CREDENTIALS_PATH');
            unset($_ENV['TTS_CREDENTIALS_PATH']);
        }
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

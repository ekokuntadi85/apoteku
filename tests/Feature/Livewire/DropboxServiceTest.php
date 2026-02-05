<?php

namespace Tests\Feature\Livewire;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\DatabaseBackupManager;

/**
 * @group dropbox-service
 */
class DropboxServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!config('filesystems.disks.dropbox.authorization_token')) {
            $this->markTestSkipped('Dropbox access token is not configured.');
        }
    }

    /** @test */
    public function it_can_list_files_in_dropbox()
    {
        // This test makes real API calls to Dropbox.
        // Ensure your .env file has valid Dropbox credentials.
        $files = Storage::disk('dropbox')->files('/');
        $this->assertIsArray($files);
    }

    /** @test */
    public function it_can_write_a_file_to_dropbox()
    {
        // This test makes real API calls to Dropbox.
        // Ensure your .env file has valid Dropbox credentials.
        $testFile = 'test-file.txt';
        $testContent = 'This is a test file from Gemini CLI.';

        // Write the file
        $result = Storage::disk('dropbox')->put($testFile, $testContent);
        $this->assertTrue($result, 'Failed to write file to Dropbox.');

        // Verify the file exists
        $this->assertTrue(Storage::disk('dropbox')->exists($testFile), 'File does not exist on Dropbox after writing.');

        // Verify file content
        $content = Storage::disk('dropbox')->get($testFile);
        $this->assertEquals($testContent, $content, 'File content on Dropbox does not match.');

        // Clean up the test file
        Storage::disk('dropbox')->delete($testFile);
        $this->assertFalse(Storage::disk('dropbox')->exists($testFile), 'Failed to delete test file from Dropbox.');
    }
}

<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test Dropbox connection
use Illuminate\Support\Facades\Storage;

try {
    echo "Testing Dropbox connection...\n";
    
    // Test 1: List root directory
    echo "Test 1: Listing root directory...\n";
    $directories = Storage::disk('dropbox')->directories('/');
    echo "✓ Success! Found " . count($directories) . " directories\n";
    print_r($directories);
    
    // Test 2: Create test file
    echo "\nTest 2: Creating test file...\n";
    $testFile = '/test_' . time() . '.txt';
    $result = Storage::disk('dropbox')->put($testFile, 'Test content from Fedora Server');
    echo $result ? "✓ File created successfully\n" : "✗ Failed to create file\n";
    
    // Test 3: Read file
    echo "\nTest 3: Reading test file...\n";
    $content = Storage::disk('dropbox')->get($testFile);
    echo "✓ Content: " . $content . "\n";
    
    // Test 4: Delete file
    echo "\nTest 4: Deleting test file...\n";
    Storage::disk('dropbox')->delete($testFile);
    echo "✓ File deleted\n";
    
    echo "\n✅ All tests passed! Dropbox connection is working.\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

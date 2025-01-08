<?php

declare(strict_types=1);

namespace GearboxSolutions\HasOneFile\Tests\Unit;

use GearboxSolutions\HasOneFile\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Workbench\App\Models\Document;

class HasOneFileTest extends TestCase
{
    private Document $document;

    protected function setUp(): void
    {
        parent::setUp();

        $this->document = Document::create();
    }

    #[Test]
    public function it_can_store_a_file(): void
    {
        $file = UploadedFile::fake()->create('test.pdf');

        $path = $this->document->storeFile($file);

        Storage::disk('local')->assertExists($path);

        // refresh the document to make sure the file_name is saved
        $this->document->refresh();
        $this->assertEquals('test.pdf', $this->document->file_name);
    }

    #[Test]
    public function it_can_store_a_file_without_saving(): void
    {
        $file = UploadedFile::fake()->create('test.pdf');

        $this->document->storeFile($file, false);

        $this->document->refresh();
        $this->assertNull($this->document->file_name);
    }

    #[Test]
    public function it_can_delete_a_file(): void
    {
        $file = UploadedFile::fake()->create('test.pdf');
        $path = $this->document->storeFile($file);

        // assert the file exists before we try to delete it
        Storage::disk('local')->assertExists($path);

        $this->document->deleteFile();

        Storage::disk('local')->assertMissing($path);
        $this->assertNull($this->document->file_name);
    }

    #[Test]
    public function it_can_delete_a_file_without_saving(): void
    {
        $file = UploadedFile::fake()->create('test.pdf');
        // this will save the file and set the file_name
        $path = $this->document->storeFile($file);

        // this will delete the file but not save the model
        $this->document->deleteFile(false);

        // refresh the document to make sure the file_name is not saved
        $this->document->refresh();

        $this->assertEquals('test.pdf', $this->document->file_name);
    }

    #[Test]
    public function it_can_check_if_file_exists(): void
    {
        $this->assertFalse($this->document->fileExists());

        $file = UploadedFile::fake()->create('test.pdf');
        $this->document->storeFile($file);

        $this->assertTrue($this->document->fileExists());
    }

    #[Test]
    public function it_can_get_file_contents(): void
    {
        $file = UploadedFile::fake()->create('test.pdf', 'Test content');
        $this->document->storeFile($file);

        $contents = $this->document->getFile();

        $this->assertNotEmpty($contents);
    }

    #[Test]
    public function it_can_get_file_url(): void
    {
        $file = UploadedFile::fake()->create('test.pdf');
        $this->document->storeFile($file);

        $expectedUrl = Storage::disk('local')->url($this->document->getFilePath());
        $this->assertEquals($expectedUrl, $this->document->file_url);
    }

    #[Test]
    public function it_deletes_file_when_model_is_deleted(): void
    {
        $file = UploadedFile::fake()->create('test.pdf');
        $path = $this->document->storeFile($file);

        $this->document->delete();

        Storage::disk('local')->assertMissing($path);
    }

    #[Test]
    public function it_can_use_custom_storage_disk(): void
    {
        Storage::fake('custom');

        $document = new class extends Document
        {
            protected $table = 'documents';

            public string $fileStorageDisk = 'custom';
        };

        $customDocument = $document->create();
        $document->save();

        $file = UploadedFile::fake()->create('test.pdf');
        $path = $document->storeFile($file);

        Storage::disk('custom')->assertExists($path);
    }

    #[Test]
    public function it_can_use_custom_filename_field(): void
    {
        // Create a new table with custom filename field
        $this->app['db']->connection()->getSchemaBuilder()->create('custom_documents', function ($table) {
            $table->id();
            $table->string('custom_file_field')->nullable();
            $table->timestamps();
        });

        // Create anonymous class extending Document with custom filename field
        $document = new class extends Document
        {
            protected $table = 'custom_documents';

            public string $fileNameField = 'custom_file_field';
        };
        $document->save();

        $file = UploadedFile::fake()->create('test.pdf');
        $document->storeFile($file);

        $this->assertEquals('test.pdf', $document->custom_file_field);
    }
}

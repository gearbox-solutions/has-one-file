<?php

declare(strict_types=1);

namespace GearboxSolutions\HasOneFile\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\Concerns\WithWorkbench;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test database structure
        $this->setUpDatabase();

        // Configure storage fake
        // Storage::fake('local');
    }

    protected function defineEnvironment($app): void
    {
        // Set default filesystem to local
        config()->set('filesystems.default', 'local');
        // config()->set('database.default', 'sqlite');

    }

    protected function setUpDatabase(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('file_name')->nullable();
            $table->timestamps();
        });
    }
}

<?php

namespace GearboxSolutions\HasOneFile\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Trait HasOneFile
 *
 * Provides file management functionality for Eloquent models.
 *
 * @property string|null $file_name The name of the stored file
 * @property string|null $file_url The URL to access the stored file
 * @property string|null $fileStorageDisk Override the default storage disk
 * @property string|null $fileNameField Override the default file name field
 */
trait HasOneFile
{
    /**
     * Boot the trait's delete listener.
     */
    protected static function bootHasOneFile(): void
    {
        static::deleting(function (Model $model) {
            // delete the file before deleting the associated model from the database
            $model->deleteFile();
        });
    }

    /**
     * Store a new file.
     * This will also update the file name field in the database with the new file name and save the model.
     * If the file is an instance of UploadedFile, the file name will be the ClientOriginalName.
     * If the file is an instance of File, the file name will be the filename of the file.
     *
     * @param  File  $file  The file to store.
     * @return string The storage path of the file
     *
     * @throws \Exception If file deletion fails
     */
    public function storeFile(File $file, bool $save = true): string
    {
        $storageDirectory = $this->getStorageDirectory();

        // delete any existing file
        Storage::disk($this->getFileStorageDisk())->deleteDirectory($storageDirectory);

        if ($file instanceof UploadedFile) {
            $fileName = $file->getClientOriginalName();
        } else {
            $fileName = $file->getFilename();
        }
        $path = Storage::disk($this->getFileStorageDisk())->putFileAs($storageDirectory, $file, $fileName);
        if ($path === false) {
            throw new \Exception('Failed to store file');
        }

        // record the file name in the database
        $this->{$this->getFileNameField()} = $fileName;
        if ($save) {
            $this->save();
        }

        return $path;
    }

    /**
     * Delete the associated file.
     * This will also clear the file name field in the database and save the model.
     *
     * @throws \Exception If file deletion fails
     */
    public function deleteFile(bool $save = true): bool
    {
        $storagePath = $this->getStorageDirectory();
        $result = Storage::disk($this->getFileStorageDisk())->deleteDirectory($storagePath);

        if (! $result) {
            return $result;
        }

        $this->{$this->getFileNameField()} = null;
        if ($save) {
            $this->save();
        }

        return $result;
    }

    /**
     * Check if a file exists for this model.
     */
    public function fileExists(): bool
    {
        return ! empty($this->{$this->getFileNameField()});
    }

    /**
     * Get the contents of the file.
     */
    public function getFile(): string
    {
        return Storage::disk($this->getFileStorageDisk())->get($this->getFilePath());
    }

    /**
     * Get the URL for accessing the file.
     */
    protected function fileUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (empty($this->{$this->getFileNameField()})) {
                    return null;
                }

                $disk = Storage::disk($this->getFileStorageDisk());

                // Check if the disk supports URL generation
                if (method_exists($disk, 'url')) {
                    return $disk->url($this->getStorageDirectory().$this->{$this->getFileNameField()});
                }

                // Return null if URL generation is not supported
                return null;
            },
        );
    }

    /**
     * Get the storage directory path for the file.
     */
    protected function getStorageDirectory(): string
    {
        return '/'.$this->getTable().'/'.$this->{$this->primaryKey}.'/';
    }

    /**
     * Get the storage disk to use for file operations.
     */
    public function getFileStorageDisk(): string
    {
        // return the disk the user specified if they have it set
        if ($this->fileStorageDisk) {
            return $this->fileStorageDisk;
        }

        // otherwise return the default disk
        return config('filesystems.default');
    }

    /**
     * Get the database field name that stores the filename.
     */
    protected function getFileNameField(): string
    {
        // return the file name field the user specified if they have it set
        if ($this->fileNameField) {
            return $this->fileNameField;
        }

        // otherwise return the default file name field
        return 'file_name';
    }

    /**
     * Get the full storage path to the file.
     */
    public function getFilePath(): string
    {
        $filePath = $this->getStorageDirectory().$this->{$this->getFileNameField()};

        return $filePath;
    }
}

<?php

namespace GearboxSolutions\HasOneFile\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\File;

trait HasOneFile
{
    public function initializeHasOneFile()
    {
        $this->appends[] = 'file_url';
    }

    /** The following protected properties can be used to override defaults
     * protected $fileStorageDisk = 'local'; // default - config('filesystems.default')
     * protected $fileNameField = 'file_name'; // default - 'file_name'
     */
    protected static function bootHasOneFile(): void
    {
        static::deleting(function (Model $model) {
            // delete the file before deleting the associated model from the database
            $model->deleteFile();
        });
    }

    protected function fileUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (empty($this->{$this->getFileNameField()})) {
                    return null;
                }

                $path = Storage::disk($this->getFileStorageDisk())->url($this->getStorageDirectory()
                    .$this->{$this->getFileNameField()});

                return $path;
            },
        );
    }

    protected function getStorageDirectory(): string
    {
        return '/'.$this->getTable().'/'.$this->id.'/';
    }

    public function deleteFile(): void
    {
        $storagePath = $this->getStorageDirectory();
        $result = Storage::disk($this->getFileStorageDisk())->deleteDirectory($storagePath);

        if (! $result) {
            throw new \Exception('Failed to delete file');
        }

        $this->{$this->getFileNameField()} = null;
        $this->save();
    }

    public function storeFile(File $file): string
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

        // record the file name in the database
        $this->{$this->getFileNameField()} = $fileName;
        $this->save();

        return $path;
    }

    public function getFileStorageDisk(): string
    {
        // return the disk the user specified if they have it set
        if ($this->fileStorageDisk) {
            return $this->fileStorageDisk;
        }

        // otherwise return the default disk
        return config('filesystems.default');
    }

    protected function getFileNameField(): string
    {
        // return the file name field the user specified if they have it set
        if ($this->fileNameField) {
            return $this->fileNameField;
        }

        // otherwise return the default file name field
        return 'file_name';
    }

    public function getFilePath(): string
    {

        $filePath = $this->getStorageDirectory().$this->{$this->getFileNameField()};

        return $filePath;
    }

    public function fileExists(): bool
    {
        return ! empty($this->{$this->getFileNameField()});
    }

    public function getFile(): string
    {
        return Storage::disk($this->getFileStorageDisk())->get($this->getFilePath());
    }
}

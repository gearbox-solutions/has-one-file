# HasOneFile

A HasOneFile trait for easy file management for Laravel models.

## Introduction

When working with files in Laravel apps, it is common to have a model with a single file associated with it. An example of this might be a "Documents" model, where a record is created for each document uploaded by a user. This package provides a trait that can be added to a model to make it easy to manage files associated with that model.

This provides a few benefits:
 - Adds a few helper methods to the model to make it easier to work with files.
 - Files are stored in a consistent location
 - Files are automatically deleted from storage when the model is deleted as part of a lifecycle hook.

 Files are stored in a location based on the model's table name and the model's primary key so that there are no conflicts with other files in the same table.

 Example:
 - A model with a table name of `documents` and a primary key of `125` will have its file stored in a directory called `documents/125/`.



Here are a few quick examples of how to use the trait:
```php
// save a file to a model
$document->storeFile($file);

// delete a previously stored file from a model
$document->deleteFile();

// check if a file exists for a model
$document->fileExists();

// get the URL for a file from a model
// this can be nice to append for a link to the file
$document->fileUrl;

// get the contents of a file from a model
$document->getFile();

// get the storage path of a file from a model
$document->getFilePath();

// get the storage directory of a file from a model
$document->getStorageDirectory();

// get the storage disk of a file from a model
$document->getFileStorageDisk();

```

## Installation and setup
Summary:

[1. Install the package via composer](#1-install-the-package-via-composer)

[2. Add the trait to your model](#2-add-the-trait-to-your-model-to-enable-file-management-for-that-model)

[3. Add a migration to store the file path in the database](#3-add-a-migration-to-store-the-file-path-in-the-database)

### 1. Install the package via composer
```bash
composer require gearbox-solutions/has-one-file
```

### 2. Add the trait to your model to enable file management for that model.

```php
use GearboxSolutions\HasOneFile\Traits\HasOneFile;
...
...

class Document extends Model
{
    use HasOneFile;

```

### 3. Add a migration to store the file path in the database.
The HasOneFile trait needs to store the name of the file associated with the model in a column in your database. The default column name used by the trait is `file_path`. You can change this by setting a `protected $fileNameField` property in your model.

 Example:
```php
Schema::table('documents', function (Blueprint $table) {
    $table->string('file_name')->nullable();
});
```


### Configuring the storage disk

The default storage disk used by the trait is the default disk configured in your `config/filesystems.php` file. You can override this by setting the `fileStorageDisk` property in your model.

```php
class Document extends Model
{
    use HasOneFile;

    protected $fileStorageDisk = 's3';
}
```


### Configuring the file name field

The default file name field used by the trait is `file_name`. You can override this by setting the `fileNameField` property in your model.

```php
class Document extends Model
{
    use HasOneFile;

    protected $fileNameField = 'document_name';
}
```

## Testing

Tests are run using [Testbench](https://github.com/orchestral/testbench) and PHPUnit.

To run the tests, you can use the following command:
```bash
./vendor/bin/phpunit
```

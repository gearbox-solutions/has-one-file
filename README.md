# HasOneFile

Adds HasOneFile trait for easy file management for records

## Introduction

When working with files in Laravel apps, it is common to have a model with a single file associated with it. An example of this might be a "Documents" model, where a record is created for each document uploaded by a user. This package provides a trait that can be added to a model to make it easy to manage files associated with that model.

This provides a few benefits:
 - It provides a consistent way to manage files associated with a model.
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

// get contents of a file from a model
$document->getFile();
```

## Installation and setup

[1. Install the package via composer](#install-the-package-via-composer)

[2. Add the trait to your model](#add-the-trait-to-your-model-to-enable-file-management-for-that-model)

[3. Add a migration to store the file path in the database](#add-a-migration-to-store-the-file-path-in-the-database)

### Install the package via composer
```bash
composer require gearbox-solutions/has-one-file
```

### Add the trait to your model to enable file management for that model.

```php
use GearboxSolutions\HasOneFile\Traits\HasOneFile;
...
...

class Document extends Model
{
    use HasOneFile;

```

### Add a migration to store the file path in the database.
The HasOneFile trait needs to store the name of the file associated with the model in a column in your database. The default column name used by the trait is `file_path`. You can change this by overriding the `getFilePathColumn` method in your model.


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

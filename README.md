# HasOneFile

Adds HasOneFile trait for easy file management for records


## Installation

```bash
composer require gearbox-solutions/has-one-file
```

## Usage

```php
use GearboxSolutions\HasOneFile\Traits\HasOneFile;
...
...

class ProjectDocument extends Model
{
    use HasOneFile;

```
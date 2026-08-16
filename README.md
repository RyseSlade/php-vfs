# Virtual file system for PHP

The VFS is a virtual file system for unit tests written in PHP. It mimics the Unix file system.

## Usage

### Example test case

```php
use Aedon\VFS\VirtualFileSystem;

final class SomeTest
{
    private VirtualFileSystem $vfs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vfs = VirtualFileSystem::create([
            'myfile.txt' => 'Hello World!',
        ]);
    }

    public function testShouldReadFile(): void
    {
        $file = $this->vfs->path('myfile.txt');

        $content = file_get_contents($file);

        self::assertEquals('Hello World!', $content);
    }
}
```

### Accessing file properties

```php
$this->vfs->node('file')->path(); // the file system path e.g. "vfs://root/file"
$this->vfs->node('file')->path; // the relative file system path e.g. "file"
$this->vfs->node('file')->permissions; // the permissions e.g. 0777
$this->vfs->node('file')->mtime; // the modified timestamp
$this->vfs->node('file')->ctime; // the change timestamp
$this->vfs->node('file')->atime; // the access timestamp
$this->vfs->node('file')->size; // the size
$this->vfs->node('file')->userId; // the owner
$this->vfs->node('file')->groupId; // the group
$this->vfs->node('file')->content; // FileNode only: Access the file content
```

### Building the file system structure

#### On create

The static `create` method expects an array that contains the file system structure. Every file or directory is a "node".
The array key is the name of the node. If the value is a string it will be handled as a file. If the value is an array it will be handled as a directory.
The file system also supports symlinks. A symlink also has a string value but the string must start with "@" and point at another file in the file system.

```php
VirtualFileSystem::create([
    'file.txt' => '', // empty text file at "vfs://root/file.txt"
    'directory' => [ // directory as "vfs://root/directory"
        'file.txt' => '', // empty file at "vfs://root/directory/file.txt"
    ],
    'link' => '@file.txt', // symlink to "file.txt" at "vfs://root/link"
]);
```

The default permissions for the whole file system can be supplied as a second parameter to the `create` method. When the default permissions parameter is not supplied the file system will use `0755` for files and directories and `0777` for symlinks.

#### On demand

When the file system is already up and running and you want to add nodes on the fly.

Create a new file in an existing directory

```php
$this-vfs->addFile('directory/file.txt', 'content');
```

Create a new directory with a custom structure (just as you would do with `create`)

```php
$this->vfs->addDirectory('directory/anotherdirectory', [
    'file.txt' => '',
]);
```

Create a new symlink

```php
$this->vfs->addSymlink('link', 'directory/file.txt');
```

## Known issues

**file_put_contents**

The parameter `LOCK_EX` is only supported in conjunction with `FILE_APPEND`. As a stand-alone parameter it will bypass the virtual file system.

### Unsupported file system functions

These file system functions were tested but do not call stream wrappers and therefore can't be supported.

* disk_free_space
* fsync
* glob
* link
* readlink
* realpath
* symlink
* tempnam

## License

MIT

## Support

Join Discord: https://discord.gg/NEfRerY

PHP VFS created by Michael "Striker" Berger

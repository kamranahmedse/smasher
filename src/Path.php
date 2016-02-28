<?php namespace KamranAhmed\Smasher;

use KamranAhmed\Smasher\Exceptions\InvalidPathException;
use KamranAhmed\Smasher\Exceptions\UnreadablePathException;

/**
 * Path
 *
 * Responsible for managing everything related to Paths
 */
class Path
{
    /**
     * @var string Path which is being processed
     */
    private $path;

    /**
     * Path constructor.
     *
     * @param string $path
     */
    public function __construct($path = '')
    {
        $this->path = $path;
    }

    /**
     * Validates whether the path exists or not
     *
     * @throws \KamranAhmed\Smasher\Exceptions\UnreadablePathException
     * @return bool
     */
    public function validate()
    {
        if (!is_readable($this->path)) {
            throw new UnreadablePathException("Unable to read the path" . $this->path);
        }

        return true;
    }

    /**
     * Writes content to the specified path
     *
     * @param $content
     * @throws \KamranAhmed\Smasher\Exceptions\InvalidPathException
     * @return int
     */
    public function saveFileContent($content)
    {
        if (!file_exists(dirname($this->path))) {
            mkdir(dirname($this->path), 0777, true);
        } elseif (is_dir($this->path)) {
            throw new InvalidPathException("You can only write content to file");
        }

        return file_put_contents($this->path, $content);
    }

    /**
     * Gets the path which is being operated
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the path to operate on
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Creates the path based upon the type of it
     * @param array $detail
     */
    public function createItem($detail = [])
    {
        // Default options
        $defaults = ['@type' => 'dir'];
        $detail   = array_merge($defaults, $detail);

        $old = umask(0);

        $type = $detail['@type'];

        if ($type === 'dir' && !file_exists($this->path)) {
            mkdir($this->path, 0777, true);
        } elseif ($type === 'file') {
            $content = $detail['@content'];

            $handle = fopen($this->path, "wb");
            fwrite($handle, $content);
            fclose($handle);

        } elseif ($type === 'link') {
            $target = $detail['@destination'];
            $link   = $this->path;

            symlink($target, $link);
        }

        umask($old);
    }

    /**
     * Gets the detail about the currently set path
     *
     * @return array
     */
    public function getDetail()
    {
        $pathDetail = [];

        $pathDetail['@name']          = $this->getName();
        $pathDetail['@path']          = $this->path;
        $pathDetail['@type']          = $this->getType();
        $pathDetail['@size']          = $this->getSize();
        $pathDetail['@mode']          = $this->getMode();
        $pathDetail['@owner']         = $this->getOwner();
        $pathDetail['@last_modified'] = $this->getLastModified();
        $pathDetail['@group']         = $this->getGroup();

        if ($pathDetail['@type'] === 'link') {
            // Save the destination of this symlink
            $pathDetail['@destination'] = $this->getRealPath();
        } elseif ($pathDetail['@type'] === 'file') {
            // If it was a file, put the contents
            $pathDetail['@content'] = $this->getFileContent();
        }

        return $pathDetail;
    }

    /**
     * Gets the name of the currently set path
     *
     * @return mixed
     */
    public function getName()
    {
        $parts = explode('/', $this->path);

        return array_pop($parts);
    }

    /**
     * Gets the type of path i.e. whether it is file, link or dir
     *
     * @return string
     */
    public function getType()
    {
        if (is_file($this->path)) {
            return 'file';
        } elseif (is_link($this->path)) {
            return 'link';
        } elseif (is_dir($this->path)) {
            return 'dir';
        }

        return "Unknown";
    }

    /**
     * Returns the size of the path in bytes
     *
     * @return int
     */
    public function getSize()
    {
        return filesize($this->path);
    }

    /**
     * Returns the permission that the path has
     *
     * @return string
     */
    public function getMode()
    {
        return substr(sprintf('%o', fileperms($this->path)), -4);
    }

    /**
     * Returns the detail of the path owner
     *
     * @return array
     */
    public function getOwner()
    {
        return posix_getpwuid(fileowner($this->path));
    }

    /**
     * Gets the last date when the path was modified
     *
     * @return string
     */
    public function getLastModified()
    {
        return gmdate('Y-m-d H:i:s', filemtime($this->path));
    }

    /**
     * Returns the detail of the path group
     *
     * @return array
     */
    public function getGroup()
    {
        return posix_getgrgid(filegroup($this->path));
    }

    /**
     * Gets the real path for the path
     *
     * @return string
     */
    public function getRealPath()
    {
        return realpath($this->path);
    }

    /**
     * If the set path is file, it returns the contents of the file
     *
     * @return string
     */
    public function getFileContent()
    {
        return file_get_contents($this->path);
    }
}

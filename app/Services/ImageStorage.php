<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image as ImageManager;
use Intervention\Image\Image;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ImageStorage
{
    /**
     * The base path where images are stored.
     * @param string
     */
    protected $base = 'uploads/reportback-items/';

    /**
     * Get image for the given post.
     *
     * @param Post $post
     * @return Image
     */
    public function get(Post $post)
    {
        $contents = Storage::get($post->getMediaPath());

        return ImageManager::make($contents);
    }

    /**
     * Save a new image for the given signup ID.
     *
     * @param string $signupId
     * @param File $file
     *
     * @return string - URL of stored image
     */
    public function put(string $signupId, File $file)
    {
        $extension = $file->guessExtension();
        $contents = file_get_contents($file->getPathname());

        // Make sure we're only uploading valid image types
        if (!in_array($extension, ['jpeg', 'jpg', 'png', 'gif'])) {
            throw new UnprocessableEntityHttpException(
                'Invalid file type. Upload a JPEG, PNG or GIF.',
            );
        }

        // Create a unique filename for this upload (since we don't know post ID yet).
        $path =
            $this->base .
            $signupId .
            '-' .
            md5($contents) .
            '-' .
            time() .
            '.' .
            $extension;

        return $this->write($path, $contents);
    }

    /**
     * Replace the image on an existing post.
     *
     * @param string $filename
     * @param Image $image
     *
     * @return string - URL of stored image
     */
    public function edit(Post $post, Image $image)
    {
        if (!$post->url) {
            throw new InvalidArgumentException(
                'Cannot edit an image that does not exist.',
            );
        }

        $path = $post->getMediaPath();
        $contents = (string) $image->encode();

        return $this->write($path, $contents);
    }

    /**
     * Write the image contents to the storage backend.
     *
     * @param string $extension
     * @param string $contents
     *
     * @return string - URL of stored image
     */
    protected function write(string $path, string $contents)
    {
        $success = Storage::put($path, $contents);

        if (!$success) {
            throw new HttpException(500, 'Unable to save image.');
        }

        return Storage::url($path);
    }

    /**
     * Delete the image for the given post.
     *
     * @param string $path
     * @return bool
     */
    public function delete(Post $post)
    {
        $path = $post->getMediaPath();

        // The delete() method always returns true because it doesn't seem to do anything with
        // any exception that is thrown while trying to delete and just returns true.
        // see: \Illuminate\Filesystem\FilesystemAdapter::delete().
        // So we check if the file exists first and then try to delete it.
        if (!Storage::exists($path)) {
            info('Could not find file when trying to delete.', [
                'path' => $path,
            ]);

            return false;
        }

        return Storage::delete($path);
    }
}

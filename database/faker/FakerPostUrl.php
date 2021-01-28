<?php

use App\Services\ImageStorage;
use Faker\Provider\Base;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FakerPostUrl extends Base
{
    /**
     * Return a Post URL.
     *
     * @return string
     */
    public function post_url()
    {
        $uploadPath = $this->generator->file(storage_path('fixtures'));
        $upload = new UploadedFile(
            $uploadPath,
            basename($uploadPath),
            'image/jpeg',
        );

        return app(ImageStorage::class)->put(
            $this->generator->unique()->randomNumber(5),
            $upload,
        );
    }
}

<?php


namespace App\Http\Controllers\Helpers;


use App\Models\Post;
use App\Models\PostFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileHelpers
{
    public static function saveFile(UploadedFile $file, Post $post) {
        $fileContent = $file->get();
        $encryptedContent = encrypt($fileContent);

        $postFile = (new PostFile)->fill([
            'name' => $file->getClientOriginalName(),
            'saved_name' => 'tmp'
        ]);

        $post->files()->save($postFile);

        $name = $postFile->refresh()->uuid . '.dat';
        Storage::put($name, $encryptedContent);

        $postFile->saved_name = $name;
        $postFile->save();

        return $postFile;
    }
}

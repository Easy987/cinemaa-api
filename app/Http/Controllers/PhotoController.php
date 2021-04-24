<?php

namespace App\Http\Controllers;

use App\Models\Movie\MoviePhoto;
use App\Models\UserProfilePicture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Intervention\Image\Exception\ImageException;
use Intervention\Image\Facades\Image;

class PhotoController extends Controller
{
    public function photo(Request $request, MoviePhoto $moviePhoto)
    {
        try {
            $cachedImage = Image::cache(function($image) use ($moviePhoto){
                $imagePath = storage_path() . '/images/movies/' . $moviePhoto->movie_id . '/photos/' . $moviePhoto->id . '.' . $moviePhoto->extension;

                if($moviePhoto->is_poster) {
                    $imagePath = storage_path() . '/images/movies/' . $moviePhoto->movie_id . '/poster.' . $moviePhoto->extension;
                }
                return $image->make($imagePath);
            },604800,false);

            return Response::make($cachedImage, 200, [ 'Content-Type' => 'image' ] )
                ->setMaxAge(604800)
                ->setPublic();
        } catch (ImageException $e) {
            return response($e->getMessage(), 404);
        }
    }

    public function userPhoto(Request $request, UserProfilePicture $userProfilePicture)
    {
        try {
            $cachedImage = Image::cache(function($image) use ($userProfilePicture){
                $imagePath = storage_path() . '/images/profiles/' . $userProfilePicture->user->id . '/' . $userProfilePicture->id . '.' . $userProfilePicture->extension;

                return $image->make($imagePath);
            },604800,false);

            return Response::make($cachedImage, 200, [ 'Content-Type' => 'image' ] )
                ->setMaxAge(604800)
                ->setPublic();
        } catch (ImageException $e) {
            return response('', 404);
        }
    }
}

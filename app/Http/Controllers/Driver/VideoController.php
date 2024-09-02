<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use App\HandleTrait;
use Illuminate\Support\Facades\Validator;



class VideoController extends Controller
{
    use HandleTrait;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            "ride_id"=> "required",
            'title' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'video' => 'required|mimes:mp4,mov,avi,flv', // 200MB max size
        ]);
        if ($validator->fails()){
            return $this->handleResponse(
                false,
                '',
                [$validator->errors()->first()],
                [],
                []
            );
        }

        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $path = $file->store('videos', 'public');

            $video = Video::create([
                'ride_id'=> $request->ride_id,
                'title' => $request->input('title'),
                'notes' => $request->input('notes'),
                'path' => $path,
            ]);
            return $this->handleResponse(
                true,
                "",
                [],
                [
                    "video" => $video
                ],
                []
            );
        }
        return $this->handleResponse(
            false,
            "Faild Uploading The Video",
            [],
            [],
            []
        );
    }

}

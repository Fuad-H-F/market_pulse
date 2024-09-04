<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HashTag;

class HashTagController extends Controller
{
    public function getHashTags()
    {
        return response()->json([
            'tags' => HashTag::all(),
            'status' => 200
        ], 200);
    }

    public function createHashTag(Request $request)
    {
        $data = json_decode($request->getContent());
        $tagId = $data->tagId;
        $tagName = $data->hashTag;

        if ($tagId) {
            $tag = HashTag::find($tagId);
            if ($tag) {
                $tag->update([
                    'Tag' => $tagName
                ]);
            } else {
                return response()->json([
                    'status' => 500
                ], 500);
            }
        } else {
            HashTag::create([
                'Tag' => $tagName
            ]);
        }

        return response()->json([
            'status' => 200
        ], 200);
    }
}

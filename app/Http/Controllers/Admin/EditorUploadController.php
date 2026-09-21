<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Receives images dropped or pasted into the rich text editor fields. */
class EditorUploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate(['upload' => ['required', 'image', 'max:5120']]);

        $path = $request->file('upload')->store('editor', 'public');

        return response()->json(['url' => '/storage/'.$path]);
    }
}

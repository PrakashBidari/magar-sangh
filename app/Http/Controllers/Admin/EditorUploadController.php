<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Receives images and PDF files dropped, pasted or picked in the rich text editor fields. */
class EditorUploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $isPdf = $request->file('upload')?->getClientOriginalExtension() === 'pdf'
            || $request->file('upload')?->getMimeType() === 'application/pdf';

        $request->validate(['upload' => $isPdf
            ? ['required', 'file', 'mimes:pdf', 'max:10240']
            : ['required', 'image', 'max:5120'],
        ]);

        $path = $request->file('upload')->store('editor', 'public');

        return response()->json(['url' => '/storage/'.$path]);
    }
}

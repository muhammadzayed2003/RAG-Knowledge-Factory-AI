<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\QdrantService;
use App\Services\RagIngestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentController extends Controller
{
    public function __construct(
        protected RagIngestionService $ragIngestionService,
        protected QdrantService $qdrantService
    ) {
    }

    public function index()
    {
        $documents =
            Document::latest()
                ->get();

        return response()->json([
            'documents' =>
                $documents,
        ]);
    }

    public function store(
        Request $request
    ) {
        $request->validate([
            'file' => [
                'required',
                'file',

                /*
                 * Laravel file size is in KB.
                 * 102400 KB = 100 MB.
                 */
                'max:102400',
            ],
        ]);

        $file =
            $request->file(
                'file'
            );

        $extension =
            strtolower(
                $file
                    ->getClientOriginalExtension()
            );

        $allowedExtensions = [
            'txt',
            'md',
            'csv',
            'json',
            'xml',
            'html',
            'htm',
            'pdf',
            'docx',
            'xlsx',
            'pptx',
            'jpg',
            'jpeg',
            'png',
            'webp',
        ];

        if (
            !in_array(
                $extension,
                $allowedExtensions,
                true
            )
        ) {
            throw ValidationException::withMessages([
                'file' => [
                    'Unsupported file type. Supported files: PDF, DOCX, TXT, MD, CSV, JSON, XML, HTML, XLSX, PPTX, JPG, JPEG, PNG and WEBP.',
                ],
            ]);
        }

        $storedName =
            Str::uuid()
                ->toString()
            .'.'
            .$extension;

        $filePath =
            $file->storeAs(
                'rag-documents',
                $storedName,
                'local'
            );

        $document =
            Document::create([
                'original_name' =>
                    $file
                        ->getClientOriginalName(),

                'stored_name' =>
                    $storedName,

                'file_path' =>
                    $filePath,

                'mime_type' =>
                    $file
                        ->getMimeType(),

                'file_size' =>
                    $file
                        ->getSize(),

                'chunk_count' =>
                    0,

                'status' =>
                    'pending',

                'error_message' =>
                    null,
            ]);

        try {
            $this->ragIngestionService
                ->ingest(
                    $document
                );

            $document->refresh();

            return response()->json([
                'message' =>
                    'File uploaded and indexed successfully.',

                'document' =>
                    $document,
            ], 201);

        } catch (\Throwable $exception) {
            $document->refresh();

            return response()->json([
                'message' =>
                    'File upload succeeded, but indexing failed.',

                'error' =>
                    $exception->getMessage(),

                'document' =>
                    $document,
            ], 500);
        }
    }

    public function destroy(
        Document $document
    ) {
        try {
            $this->qdrantService
                ->deleteByDocumentId(
                    $document->id
                );

            if (
                $document->file_path
                && Storage::disk('local')
                    ->exists(
                        $document->file_path
                    )
            ) {
                Storage::disk('local')
                    ->delete(
                        $document->file_path
                    );
            }

            $document->delete();

            return response()->json([
                'message' =>
                    'File and all related vectors deleted successfully.',
            ]);

        } catch (\Throwable $exception) {
            return response()->json([
                'message' =>
                    'File deletion failed.',

                'error' =>
                    $exception->getMessage(),
            ], 500);
        }
    }
}
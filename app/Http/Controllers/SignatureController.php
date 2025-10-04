<?php

namespace App\Http\Controllers;

use App\Services\SigningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Native\Laravel\Dialog;

class SignatureController extends Controller
{
    /**
     * Display the signature page
     */
    public function index()
    {
        return view('signature.index');
    }

    /**
     * Sign document and return file path
     */
    public function sign(
        Request $request,
        SigningService $signingService,
    ) {
        // Validate the uploaded file
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,txt|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('document');
            $signedFileName = 'signed_ ' . $file->getClientOriginalName();
            $signingService->sign($signedFileName, $file);

            return response()->json([
                'success' => true,
                'message' => 'Document signed successfully',
                'data' => [
                    'signed_file_path' => Storage::disk('signatures')->path($signedFileName),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Document signing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sign document: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Open signed file in file explorer
     */
    public function openFile(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
        ]);

        try {
            $filePath = $request->input('file_path');

            // Verify file exists
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found',
                ], 404);
            }

            // Get the directory path
            $directory = dirname($filePath);

            // Detect OS and open file explorer
            $os = PHP_OS_FAMILY;

            if ($os === 'Windows') {
                // Open Windows Explorer and select the file
                exec('explorer /select,"' . str_replace('/', '\\', $filePath) . '"');
            } elseif ($os === 'Darwin') {
                // macOS - Open Finder and select the file
                exec('open -R "' . $filePath . '"');
            } else {
                // Linux - Open file manager in the directory
                // Try different file managers
                $fileManagers = ['xdg-open', 'nautilus', 'dolphin', 'thunar', 'nemo'];

                foreach ($fileManagers as $manager) {
                    if (shell_exec("which $manager")) {
                        exec("$manager \"$directory\" &");
                        break;
                    }
                }
            }

            Log::info('File explorer opened', ['path' => $filePath]);

            return response()->json([
                'success' => true,
                'message' => 'File explorer opened successfully',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to open file explorer', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to open file explorer: ' . $e->getMessage(),
            ], 500);
        }
    }
}

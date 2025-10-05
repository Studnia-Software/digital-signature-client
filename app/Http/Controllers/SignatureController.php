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
        Request        $request,
        SigningService $signingService,
    )
    {
        // Validate the uploaded file
        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,txt|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('document');
            $result = $signingService->sign($file);

            $data = json_decode($result, true);
            $data['signed_file_path'] = $signingService->getSignedFile();

            return response()->json([
                'success' => true,
                'message' => 'Document signed successfully',
                'data' => $data
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

    /**
     * Display the verification page
     */
    public function verifyView()
    {
        return view('signature.verify');
    }

    /**
     * Verify a signed document
     */
    public function verifyDocument(Request $request)
    {
        $request->validate([
            'document' => 'required|file|max:10240',
        ]);

        try {
            $file = $request->file('document');
            $content = file_get_contents($file->getRealPath());

            // Check if file has signature header
            if (strpos($content, '=== DIGITALLY SIGNED DOCUMENT ===') !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'This is not a signed document or signature format is invalid',
                ], 400);
            }

            // Extract metadata from the signed document
            $metadata = $this->extractMetadata($content);

            if (!$metadata) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to extract signature metadata',
                ], 400);
            }

            // Extract original content
            $originalContentMarker = "--- ORIGINAL DOCUMENT CONTENT ---\n";
            $originalContentPos = strpos($content, $originalContentMarker);

            if ($originalContentPos === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to locate original document content',
                ], 400);
            }

            $originalContent = substr($content, $originalContentPos + strlen($originalContentMarker));

            // Verify hash
            $calculatedHash = hash('sha256', $originalContent);
            $isValid = ($calculatedHash === $metadata['signature_hash']);

            Log::info('Document verification performed', [
                'filename' => $file->getClientOriginalName(),
                'is_valid' => $isValid,
                'original_filename' => $metadata['original_filename'] ?? 'Unknown',
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'is_valid' => $isValid,
                    'message' => $isValid
                        ? 'The document signature is valid and has not been tampered with.'
                        : 'The document signature is invalid. The content may have been modified.',
                    'metadata' => $metadata,
                    'calculated_hash' => $calculatedHash,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Document verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extract metadata from signed document
     */
    private function extractMetadata($content)
    {
        $metadata = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            if (strpos($line, '===') !== false) {
                continue;
            }
            if (strpos($line, '---') !== false) {
                break;
            }

            if (strpos($line, ':') !== false) {
                list($key, $value) = array_map('trim', explode(':', $line, 2));

                // Convert key to snake_case
                $key = strtolower(str_replace(' ', '_', $key));
                $metadata[$key] = $value;
            }
        }

        return $metadata;
    }
}

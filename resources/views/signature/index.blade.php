{{-- resources/views/signature/index.blade.php --}}
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Digital Signature</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }

        @keyframes subtlePulse {
            0%, 100% {
                opacity: 0.4;
            }
            50% {
                opacity: 0.6;
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-2px);
        }

        .shimmer-border {
            position: relative;
            overflow: hidden;
        }

        .shimmer-border::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 2px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            background-size: 200% 100%;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .shimmer-border:hover::before {
            opacity: 1;
            animation: shimmer 2s linear infinite;
        }

        .subtle-glow {
            box-shadow: 0 0 40px rgba(255, 255, 255, 0.03);
        }

        .icon-pulse {
            animation: subtlePulse 3s ease-in-out infinite;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #1a1a1a;
        }

        ::-webkit-scrollbar-thumb {
            background: #404040;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #525252;
        }
    </style>
</head>
<body class="min-h-screen bg-[#0a0a0a] text-white antialiased">
<div class="container mx-auto px-6 py-16 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-2xl fade-in">
        <!-- Header -->
        <div class="text-center mb-12">
            <div class="inline-block mb-4">
                <div class="text-5xl icon-pulse">🔐</div>
            </div>
            <h1 class="text-4xl font-light mb-3 tracking-tight">
                Digital Signature
            </h1>
            <p class="text-gray-500 text-sm">
                Secure document authentication
            </p>
        </div>

        <!-- Navigation -->
        <div class="flex justify-center mb-8">
            <div class="inline-flex bg-[#111111] rounded-lg p-1 border border-[#1f1f1f]">
                <a href="{{ route('signature.index') }}" class="px-6 py-2 text-sm bg-[#1a1a1a] text-white rounded-md">
                    Sign
                </a>
                <a href="{{ route('signature.verify') }}" class="px-6 py-2 text-sm text-gray-500 hover:text-white transition-colors rounded-md">
                    Verify
                </a>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-[#111111] rounded-2xl p-8 border border-[#1f1f1f] shimmer-border subtle-glow">
            <!-- Drop Zone -->
            <div id="dropZone" class="hover-lift shimmer-border relative border-2 border-dashed border-[#2a2a2a] rounded-xl p-20 text-center cursor-pointer transition-all duration-300 hover:border-[#404040] hover:bg-[#151515] group">
                <div class="text-6xl mb-6 opacity-40 group-hover:opacity-60 transition-all duration-500 transform group-hover:scale-105">
                    📄
                </div>
                <h3 class="text-lg font-light text-gray-300 mb-2 group-hover:text-white transition-colors">
                    Drop file here
                </h3>
                <p class="text-sm text-gray-600 group-hover:text-gray-500 transition-colors">
                    or click to browse
                </p>
            </div>

            <input type="file" id="fileInput" class="hidden" accept=".pdf,.doc,.docx,.txt">

            <!-- File Info Card -->
            <div id="fileInfo" class="hidden mt-6 bg-[#151515] rounded-xl p-6 border border-[#1f1f1f] hover-lift">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4 flex-1 min-w-0">
                        <div class="text-3xl opacity-60">📎</div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-normal text-white truncate" id="fileName"></h4>
                            <p class="text-sm text-gray-600 mt-1" id="fileSize"></p>
                        </div>
                    </div>
                    <button type="button" id="removeFile" class="ml-4 px-4 py-2 text-sm text-gray-500 hover:text-white transition-all duration-300 hover:bg-[#1a1a1a] rounded-lg">
                        Remove
                    </button>
                </div>
            </div>

            <!-- Sign Button -->
            <button type="button" id="signButton" class="hidden w-full mt-6 px-6 py-4 bg-white text-black rounded-xl font-normal transition-all duration-300 hover-lift hover:bg-gray-100 hover:shadow-lg hover:shadow-white/5 disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:transform-none disabled:hover:bg-white disabled:hover:shadow-none">
                    <span class="flex items-center justify-center gap-2">
                        <span>Sign Document</span>
                        <span class="text-lg">→</span>
                    </span>
            </button>

            <!-- Open File Button -->
            <button type="button" id="openFileButton" class="hidden w-full mt-3 px-6 py-4 bg-[#1a1a1a] text-white rounded-xl font-normal transition-all duration-300 hover-lift hover:bg-[#222222] border border-[#2a2a2a]">
                    <span class="flex items-center justify-center gap-2">
                        <span>📂</span>
                        <span>Open in File Explorer</span>
                    </span>
            </button>

            <!-- Signature Details -->
            <div id="signatureDetails" class="hidden mt-6 bg-[#151515] rounded-xl p-6 border border-[#1f1f1f]">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-[#2a2a2a]">
                    <div class="text-3xl">🎫</div>
                    <h3 class="text-lg font-normal text-white">Signature Details</h3>
                </div>

                <div class="space-y-4">
                    <!-- Key Information -->
                    <div class="bg-[#1a1a1a] rounded-lg p-4">
                        <p class="text-xs text-gray-600 mb-2">Signing Key</p>
                        <p class="text-base text-white font-medium" id="keyName"></p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-xs text-gray-500" id="keyApprover"></span>
                            <span class="text-gray-700">•</span>
                            <span class="text-xs text-gray-500">Expires: <span id="keyExpiration"></span></span>
                        </div>
                    </div>

                    <!-- File Signature -->
                    <div>
                        <p class="text-xs text-gray-600 mb-2">File Signature</p>
                        <div class="bg-[#1a1a1a] rounded-lg p-3">
                            <p class="text-xs text-green-400 font-mono break-all leading-relaxed" id="fileSignature"></p>
                        </div>
                    </div>

                    <!-- Key Signature -->
                    <div>
                        <p class="text-xs text-gray-600 mb-2">Key Signature</p>
                        <div class="bg-[#1a1a1a] rounded-lg p-3">
                            <p class="text-xs text-purple-400 font-mono" id="keySignature"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Message -->
            <div id="statusMessage" class="hidden mt-6 rounded-xl p-4 text-sm"></div>
        </div>

        <!-- Footer -->
        <div class="mt-12 flex items-center justify-center gap-8 text-xs text-gray-700">
            <span>256-bit encryption</span>
            <span>•</span>
            <span>SHA-256</span>
            <span>•</span>
            <span>Secure</span>
        </div>
    </div>
</div>

<script>
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const removeFile = document.getElementById('removeFile');
    const signButton = document.getElementById('signButton');
    const openFileButton = document.getElementById('openFileButton');
    const statusMessage = document.getElementById('statusMessage');
    const signatureDetails = document.getElementById('signatureDetails');
    let selectedFile = null;
    let signedFilePath = null;

    // Click to browse
    dropZone.addEventListener('click', () => fileInput.click());

    // File input change
    fileInput.addEventListener('change', (e) => handleFile(e.target.files[0]));

    // Drag and drop handlers
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('border-[#404040]', 'bg-[#151515]');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-[#404040]', 'bg-[#151515]');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-[#404040]', 'bg-[#151515]');
        handleFile(e.dataTransfer.files[0]);
    });

    // Remove file handler
    removeFile.addEventListener('click', () => {
        selectedFile = null;
        signedFilePath = null;
        fileInput.value = '';
        fileInfo.classList.add('hidden');
        signButton.classList.add('hidden');
        openFileButton.classList.add('hidden');
        signatureDetails.classList.add('hidden');
        dropZone.classList.remove('hidden');
        hideStatus();
    });

    // Sign button handler
    signButton.addEventListener('click', async () => {
        if (!selectedFile) return;

        signButton.disabled = true;
        const originalContent = signButton.innerHTML;
        signButton.innerHTML = '<span class="flex items-center justify-center gap-2"><span class="inline-block animate-spin">⚙</span><span>Signing...</span></span>';

        showStatus('Processing document...', 'info');

        const formData = new FormData();
        formData.append('document', selectedFile);

        try {
            const response = await fetch('{{ route("signature.sign") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                console.log('Response data:', data); // Debug log
                signedFilePath = data.data.file_path || data.data.file_name;
                showStatus('✓ Document signed successfully', 'success');
                openFileButton.classList.remove('hidden');

                // Display signature details
                displaySignatureDetails(data.data);
            } else {
                showStatus('Error: ' + (data.message || 'Failed to sign document'), 'error');
            }
        } catch (error) {
            showStatus('Network error: ' + error.message, 'error');
        } finally {
            signButton.disabled = false;
            signButton.innerHTML = '<span class="flex items-center justify-center gap-2"><span>Sign Document</span><span class="text-lg">→</span></span>';
        }
    });

    // Open file in explorer handler
    openFileButton.addEventListener('click', async () => {
        if (!signedFilePath) return;

        try {
            const response = await fetch('{{ route("signature.open") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ file_path: signedFilePath })
            });

            const data = await response.json();

            if (response.ok) {
                showStatus('✓ File explorer opened', 'success');
            } else {
                showStatus('Error: ' + (data.message || 'Failed to open file explorer'), 'error');
            }
        } catch (error) {
            showStatus('Network error: ' + error.message, 'error');
        }
    });

    function handleFile(file) {
        if (!file) return;

        selectedFile = file;
        signedFilePath = null;
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);

        fileInfo.classList.remove('hidden');
        signButton.classList.remove('hidden');
        openFileButton.classList.add('hidden');
        dropZone.classList.add('hidden');
        hideStatus();
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    function showStatus(message, type) {
        const styles = {
            success: 'bg-green-950 text-green-400 border border-green-900',
            error: 'bg-red-950 text-red-400 border border-red-900',
            info: 'bg-[#151515] text-gray-400 border border-[#1f1f1f]'
        };

        statusMessage.textContent = message;
        statusMessage.className = `mt-6 rounded-xl p-4 text-sm ${styles[type]}`;
        statusMessage.classList.remove('hidden');
    }

    function hideStatus() {
        statusMessage.classList.add('hidden');
    }

    function displaySignatureDetails(data) {
        console.log('Displaying signature details:', data); // Debug log

        // Populate signature details with fallback values
        const keyName = data.key_name || 'N/A';
        const keyApprover = data.key_approver || 'N/A';
        const keyExpiration = data.key_expiration_date || 'N/A';
        const fileSignature = data.file_signature || data.signature_hash || 'N/A';
        const keySignature = data.key_signature || 'N/A';

        document.getElementById('keyName').textContent = keyName;
        document.getElementById('keyApprover').textContent = keyApprover;
        document.getElementById('keyExpiration').textContent = keyExpiration;
        document.getElementById('fileSignature').textContent = fileSignature;
        document.getElementById('keySignature').textContent = keySignature;

        console.log('Details populated'); // Debug log

        // Show the details section
        signatureDetails.classList.remove('hidden');
    }
</script>
</body>
</html>

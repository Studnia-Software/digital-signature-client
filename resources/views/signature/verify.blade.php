{{-- resources/views/signature/verify.blade.php --}}
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Signature</title>
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

        @keyframes checkmark {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
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

        .checkmark-animation {
            animation: checkmark 0.5s ease-out;
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
        <div class="text-center mb-16">
            <div class="inline-block mb-4">
                <div class="text-5xl icon-pulse">✓</div>
            </div>
            <h1 class="text-4xl font-light mb-3 tracking-tight">
                Verify Signature
            </h1>
            <p class="text-gray-500 text-sm">
                Upload a signed document to verify its authenticity
            </p>
        </div>

        <!-- Navigation -->
        <div class="flex justify-center mb-8">
            <div class="inline-flex bg-[#111111] rounded-lg p-1 border border-[#1f1f1f]">
                <a href="{{ route('signature.index') }}" class="px-6 py-2 text-sm text-gray-500 hover:text-white transition-colors rounded-md">
                    Sign
                </a>
                <a href="{{ route('signature.verify') }}" class="px-6 py-2 text-sm bg-[#1a1a1a] text-white rounded-md">
                    Verify
                </a>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-[#111111] rounded-2xl p-8 border border-[#1f1f1f] shimmer-border subtle-glow">
            <!-- Drop Zone -->
            <div id="dropZone" class="hover-lift shimmer-border relative border-2 border-dashed border-[#2a2a2a] rounded-xl p-20 text-center cursor-pointer transition-all duration-300 hover:border-[#404040] hover:bg-[#151515] group">
                <div class="text-6xl mb-6 opacity-40 group-hover:opacity-60 transition-all duration-500 transform group-hover:scale-105">
                    🔍
                </div>
                <h3 class="text-lg font-light text-gray-300 mb-2 group-hover:text-white transition-colors">
                    Drop signed file here
                </h3>
                <p class="text-sm text-gray-600 group-hover:text-gray-500 transition-colors">
                    or click to browse
                </p>
            </div>

            <input type="file" id="fileInput" class="hidden">

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

            <!-- Verify Button -->
            <button type="button" id="verifyButton" class="hidden w-full mt-6 px-6 py-4 bg-white text-black rounded-xl font-normal transition-all duration-300 hover-lift hover:bg-gray-100 hover:shadow-lg hover:shadow-white/5 disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:transform-none disabled:hover:bg-white disabled:hover:shadow-none">
                    <span class="flex items-center justify-center gap-2">
                        <span>Verify Signature</span>
                        <span class="text-lg">→</span>
                    </span>
            </button>

            <!-- Verification Result -->
            <div id="verificationResult" class="hidden mt-6"></div>
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
    const verifyButton = document.getElementById('verifyButton');
    const verificationResult = document.getElementById('verificationResult');
    let selectedFile = null;

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
        fileInput.value = '';
        fileInfo.classList.add('hidden');
        verifyButton.classList.add('hidden');
        verificationResult.classList.add('hidden');
        dropZone.classList.remove('hidden');
    });

    // Verify button handler
    verifyButton.addEventListener('click', async () => {
        if (!selectedFile) return;

        verifyButton.disabled = true;
        verifyButton.innerHTML = '<span class="flex items-center justify-center gap-2"><span class="inline-block animate-spin">⚙</span><span>Verifying...</span></span>';

        const formData = new FormData();
        formData.append('document', selectedFile);

        try {
            const response = await fetch('{{ route("signature.verify.check") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showVerificationResult(data.data);
            } else {
                showError(data.message || 'Verification failed');
            }
        } catch (error) {
            showError('Network error: ' + error.message);
        } finally {
            verifyButton.disabled = false;
            verifyButton.innerHTML = '<span class="flex items-center justify-center gap-2"><span>Verify Signature</span><span class="text-lg">→</span></span>';
        }
    });

    function handleFile(file) {
        if (!file) return;

        selectedFile = file;
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);

        fileInfo.classList.remove('hidden');
        verifyButton.classList.remove('hidden');
        verificationResult.classList.add('hidden');
        dropZone.classList.add('hidden');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    function showVerificationResult(data) {
        const isValid = data.is_valid;
        const iconClass = isValid ? 'checkmark-animation' : '';

        let html = `
                <div class="bg-[#151515] rounded-xl p-6 border ${isValid ? 'border-green-900' : 'border-red-900'}">
                    <div class="flex items-start gap-4 mb-6">
                        <div class="text-5xl ${iconClass}">${isValid ? '✓' : '✕'}</div>
                        <div class="flex-1">
                            <h3 class="text-xl font-normal ${isValid ? 'text-green-400' : 'text-red-400'} mb-2">
                                ${isValid ? 'Signature Valid' : 'Signature Invalid'}
                            </h3>
                            <p class="text-sm text-gray-500">
                                ${data.message}
                            </p>
                        </div>
                    </div>

                    ${isValid ? `
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Original Filename</p>
                                <p class="text-sm text-white">${data.metadata.original_filename || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">File Size</p>
                                <p class="text-sm text-white">${data.metadata.file_size || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Signed At</p>
                                <p class="text-sm text-white">${data.metadata.signed_at || 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-600 mb-1">Algorithm</p>
                                <p class="text-sm text-white">${data.metadata.signature_algorithm || 'N/A'}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs text-gray-600 mb-1">Certificate Serial</p>
                            <p class="text-xs text-white font-mono break-all">${data.metadata.certificate_serial || 'N/A'}</p>
                        </div>

                        <div>
                            <p class="text-xs text-gray-600 mb-1">SHA-256 Hash</p>
                            <p class="text-xs text-white font-mono break-all">${data.metadata.signature_hash || 'N/A'}</p>
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;

        verificationResult.innerHTML = html;
        verificationResult.classList.remove('hidden');
    }

    function showError(message) {
        verificationResult.innerHTML = `
                <div class="bg-[#151515] rounded-xl p-6 border border-red-900">
                    <div class="flex items-start gap-4">
                        <div class="text-4xl">⚠️</div>
                        <div>
                            <h3 class="text-lg font-normal text-red-400 mb-2">Error</h3>
                            <p class="text-sm text-gray-500">${message}</p>
                        </div>
                    </div>
                </div>
            `;
        verificationResult.classList.remove('hidden');
    }
</script>
</body>
</html>

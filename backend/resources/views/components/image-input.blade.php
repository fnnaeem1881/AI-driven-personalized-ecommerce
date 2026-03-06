@props(['name', 'value' => '', 'label' => 'Image', 'required' => false])
<div x-data="imageInput('{{ $value }}')" class="image-input-wrap">
    {{-- Mode toggle --}}
    <div class="flex gap-2 mb-2">
        <button type="button" @click="mode='url'"
            class="text-xs px-3 py-1.5 rounded-lg font-medium transition-all"
            :class="mode==='url' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
            URL
        </button>
        <button type="button" @click="mode='upload'"
            class="text-xs px-3 py-1.5 rounded-lg font-medium transition-all"
            :class="mode==='upload' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
            Upload File
        </button>
    </div>

    {{-- Hidden field stores relative path (for uploads) or full URL (for external URLs) --}}
    <input type="hidden" name="{{ $name }}" :value="storedValue">

    {{-- URL input mode --}}
    <div x-show="mode === 'url'">
        <input type="url" x-model="url" @input="storedValue = url"
            class="form-input" placeholder="https://example.com/image.jpg"
            {{ $required ? 'required' : '' }}>
    </div>

    {{-- File upload mode --}}
    <div x-show="mode === 'upload'" class="space-y-2">
        <input type="file" accept="image/*" @change="uploadFile($event)"
            class="form-input text-sm py-2 cursor-pointer"
            :disabled="uploading">
        <div x-show="uploading" class="flex items-center gap-2 text-xs text-blue-600">
            <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Uploading...
        </div>
        <div x-show="uploadError" class="text-xs text-red-500" x-text="uploadError"></div>
    </div>

    {{-- Preview --}}
    <div x-show="url" class="mt-2">
        <img :src="url" alt="Preview"
            class="h-24 w-auto max-w-xs rounded-lg border border-gray-200 object-cover bg-gray-50"
            x-on:error="this.style.opacity='0.3'">
        <p class="text-xs text-gray-400 mt-1 truncate max-w-xs" x-text="storedValue"></p>
    </div>
</div>

<script>
if (typeof imageInput === 'undefined') {
    function imageInput(initialValue) {
        // If it's a local storage URL (contains /storage/), extract the relative path.
        // This also migrates old localhost-hardcoded URLs to portable relative paths.
        const toStoredValue = v => {
            if (!v) return '';
            if (v.includes('/storage/')) return v.split('/storage/').pop();
            return v; // external URL — store as-is
        };
        // Convert a stored relative path to a previewable URL for the img tag
        const toPreviewUrl = v => v && !v.startsWith('http') ? '/storage/' + v : (v || '');

        const stored = toStoredValue(initialValue);
        return {
            mode: 'url',
            // storedValue: what gets saved to DB (relative path for uploads, full URL for external)
            storedValue: stored,
            // url: what shows in the preview img and URL text input
            url: toPreviewUrl(stored),
            uploading: false,
            uploadError: '',
            uploadFile(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.uploading = true;
                this.uploadError = '';
                const fd = new FormData();
                fd.append('image', file);
                fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                fetch('/admin/upload-image', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            this.storedValue = data.path; // relative path → portable across domains
                            this.url = data.url;          // full URL → used for preview only
                            this.mode = 'url';
                        } else {
                            this.uploadError = 'Upload failed. Try again.';
                        }
                        this.uploading = false;
                    })
                    .catch(() => {
                        this.uploadError = 'Upload failed. Check file size (<5MB).';
                        this.uploading = false;
                    });
            }
        };
    }
}
</script>

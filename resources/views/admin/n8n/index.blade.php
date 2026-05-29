@extends('admin.layout')

@section('admin_content')
<div style="margin-bottom: 40px;">
    <h1 style="font-size: 2rem; font-weight: 700;">n8n Listing</h1>
    <p style="color: var(--text-muted);">Kirim pesan teks dan file lampiran sekaligus ke webhook n8n secara aman.</p>
</div>

<!-- Glass panel for n8n Send Form -->
<div class="glass" style="padding: 30px; border-radius: var(--radius);">
    <h2 style="font-size: 1.2rem; font-weight: 700; margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-share-nodes" style="color: var(--primary);"></i> Kirim Data ke n8n
    </h2>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 25px;">
        Gunakan form di bawah ini untuk mengirim pesan teks dan melampirkan beberapa file sekaligus.
    </p>

    <form id="n8nForm" enctype="multipart/form-data">
        @csrf
        <div style="margin-bottom: 20px;">
            <label for="n8nText" style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">Pesan Teks <span style="color: var(--accent);">*</span> (Minimal 50 huruf)</label>
            <textarea id="n8nText" name="text" rows="6" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border); font-family: inherit; font-size: 0.95rem; resize: vertical; outline: none; transition: border-color 0.2s;" placeholder="Tuliskan pesan teks yang ingin dikirim (minimal 50 huruf)..." minlength="50" required></textarea>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 0.9rem;">File Lampiran Gambar <span style="color: var(--accent);">*</span> (Minimal 1 gambar)</label>
            <div id="dropzone" style="border: 2px dashed var(--border); border-radius: 8px; padding: 40px 20px; text-align: center; background: rgba(241, 245, 249, 0.5); cursor: pointer; transition: all 0.2s ease;">
                <input type="file" id="n8nFiles" name="files[]" accept="image/*" multiple style="display: none;">
                <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: 12px; transition: color 0.2s;"></i>
                <p style="font-weight: 600; font-size: 0.95rem; margin-bottom: 4px;">Tarik & Lepaskan File Gambar di Sini</p>
                <p style="color: var(--text-muted); font-size: 0.8rem;">atau <span style="color: var(--primary); font-weight: 600;">Pilih File Gambar</span> dari komputer Anda</p>
                <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 8px;">Hanya format gambar (JPEG, PNG, JPG, WEBP). Maksimal 10MB per gambar.</p>
            </div>
            
            <!-- File List Container -->
            <div id="fileList" style="margin-top: 15px; display: flex; flex-direction: column; gap: 8px;"></div>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <button type="submit" id="n8nSubmitBtn" class="btn btn-primary" style="padding: 10px 24px; font-size: 0.9rem; border-radius: 8px;">
                <span id="btnText">Kirim ke n8n</span>
                <span id="btnSpinner" style="display: none;"><i class="fa-solid fa-spinner fa-spin" style="margin-right: 5px;"></i> Mengirim...</span>
            </button>
            <button type="button" id="n8nResetBtn" class="btn btn-secondary" style="padding: 10px 20px; font-size: 0.9rem; border-radius: 8px; background: transparent; border: 1px solid var(--border);">
                Reset
            </button>
        </div>
    </form>

    <!-- Inline Status Messages -->
    <div id="n8nStatusContainer" style="margin-top: 25px; display: none;"></div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('n8nForm');
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('n8nFiles');
    const fileList = document.getElementById('fileList');
    const submitBtn = document.getElementById('n8nSubmitBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');
    const resetBtn = document.getElementById('n8nResetBtn');
    const statusContainer = document.getElementById('n8nStatusContainer');

    // Keep track of files to submit
    let selectedFiles = [];

    // Trigger file selection when clicking dropzone
    dropzone.addEventListener('click', () => fileInput.click());

    // Stylings for Drag & Drop
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.borderColor = 'var(--primary)';
            dropzone.style.background = 'rgba(29, 78, 216, 0.05)';
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.borderColor = 'var(--border)';
            dropzone.style.background = 'rgba(241, 245, 249, 0.5)';
        }, false);
    });

    // Handle dropped files
    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    });

    // Handle selected files
    fileInput.addEventListener('change', (e) => {
        handleFiles(e.target.files);
    });

    // Process selected files
    function handleFiles(files) {
        let nonImages = [];
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            // Filter: only allow images
            if (!file.type.startsWith('image/')) {
                nonImages.push(file.name);
                continue;
            }
            
            // Prevent duplicate files (by name and size)
            const exists = selectedFiles.some(f => f.name === file.name && f.size === file.size);
            if (!exists) {
                selectedFiles.push(file);
            }
        }

        if (nonImages.length > 0) {
            showStatus(false, 'File diabaikan karena bukan gambar: ' + nonImages.join(', '));
        }
        updateFileList();
    }

    // Format file size
    function formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Update File List UI (XSS Safe - Using textContent and appendChild)
    function updateFileList() {
        fileList.replaceChildren(); // Safely clear all child nodes

        if (selectedFiles.length === 0) {
            return;
        }

        selectedFiles.forEach((file, index) => {
            const row = document.createElement('div');
            row.style.display = 'flex';
            row.style.alignItems = 'center';
            row.style.justifyContent = 'space-between';
            row.style.background = '#f8fafc';
            row.style.padding = '8px 12px';
            row.style.borderRadius = '6px';
            row.style.border = '1px solid var(--border)';
            row.style.fontSize = '0.85rem';

            // File Details Container
            const details = document.createElement('div');
            details.style.display = 'flex';
            details.style.alignItems = 'center';
            details.style.gap = '8px';
            details.style.minWidth = '0';

            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-file';
            icon.style.color = 'var(--text-muted)';
            
            const nameSpan = document.createElement('span');
            nameSpan.textContent = file.name;
            nameSpan.style.fontWeight = '500';
            nameSpan.style.whiteSpace = 'nowrap';
            nameSpan.style.overflow = 'hidden';
            nameSpan.style.textOverflow = 'ellipsis';
            nameSpan.style.maxWidth = '250px';

            const sizeSpan = document.createElement('span');
            sizeSpan.textContent = `(${formatBytes(file.size)})`;
            sizeSpan.style.color = 'var(--text-muted)';

            details.appendChild(icon);
            details.appendChild(nameSpan);
            details.appendChild(sizeSpan);

            // Delete button
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.style.background = 'none';
            deleteBtn.style.border = 'none';
            deleteBtn.style.color = 'var(--accent)';
            deleteBtn.style.cursor = 'pointer';
            deleteBtn.style.padding = '4px';
            deleteBtn.style.display = 'flex';
            deleteBtn.style.alignItems = 'center';
            
            const deleteIcon = document.createElement('i');
            deleteIcon.className = 'fa-solid fa-trash-can';
            deleteBtn.appendChild(deleteIcon);

            deleteBtn.addEventListener('click', () => {
                selectedFiles.splice(index, 1);
                updateFileList();
            });

            row.appendChild(details);
            row.appendChild(deleteBtn);
            fileList.appendChild(row);
        });
    }

    // Submit form handler
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const textVal = document.getElementById('n8nText').value.trim();
        
        // Client side validation
        if (textVal.length < 50) {
            showStatus(false, 'Pesan teks wajib diisi dan minimal terdiri dari 50 karakter.');
            return;
        }

        if (selectedFiles.length === 0) {
            showStatus(false, 'Anda wajib melampirkan minimal satu gambar.');
            return;
        }

        // Set Loading State
        submitBtn.disabled = true;
        resetBtn.disabled = true;
        btnText.style.display = 'none';
        btnSpinner.style.display = 'inline-block';
        statusContainer.style.display = 'none';
        statusContainer.replaceChildren();

        // Build FormData
        const formData = new FormData();
        formData.append('text', textVal);
        
        selectedFiles.forEach(file => {
            formData.append('files[]', file);
        });

        // Add CSRF Token
        const csrfToken = form.querySelector('input[name="_token"]').value;

        fetch('{{ route("admin.send-to-n8n") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            showStatus(true, data.message || 'Data berhasil dikirim ke n8n!');
            resetForm();
        })
        .catch(err => {
            console.error('Submission error:', err);
            const errMsg = err.message || 'Terjadi kesalahan saat mengirim data ke n8n.';
            showStatus(false, errMsg);
        })
        .finally(() => {
            // Restore Button State
            submitBtn.disabled = false;
            resetBtn.disabled = false;
            btnText.style.display = 'inline-block';
            btnSpinner.style.display = 'none';
        });
    });

    // Reset button handler
    resetBtn.addEventListener('click', () => {
        resetForm();
        statusContainer.style.display = 'none';
        statusContainer.replaceChildren();
    });

    function resetForm() {
        form.reset();
        selectedFiles = [];
        updateFileList();
    }

    // Show status message (XSS Safe)
    function showStatus(isSuccess, message) {
        statusContainer.replaceChildren(); // Clear children

        const alertDiv = document.createElement('div');
        alertDiv.style.padding = '15px';
        alertDiv.style.borderRadius = '8px';
        alertDiv.style.display = 'flex';
        alertDiv.style.alignItems = 'center';
        alertDiv.style.gap = '12px';
        alertDiv.style.fontSize = '0.9rem';
        alertDiv.style.border = '1px solid';

        const icon = document.createElement('i');
        icon.style.fontSize = '1.1rem';

        const textDiv = document.createElement('div');
        textDiv.textContent = message;
        textDiv.style.fontWeight = '500';

        if (isSuccess) {
            alertDiv.style.background = 'rgba(34, 197, 94, 0.15)';
            alertDiv.style.color = '#15803d';
            alertDiv.style.borderColor = '#bbf7d0';
            icon.className = 'fa-solid fa-circle-check';
            icon.style.color = '#16a34a';
        } else {
            alertDiv.style.background = 'rgba(239, 68, 68, 0.15)';
            alertDiv.style.color = '#b91c1c';
            alertDiv.style.borderColor = '#fecaca';
            icon.className = 'fa-solid fa-circle-exclamation';
            icon.style.color = '#dc2626';
        }

        alertDiv.appendChild(icon);
        alertDiv.appendChild(textDiv);
        statusContainer.appendChild(alertDiv);
        statusContainer.style.display = 'block';
    }
});
</script>
@endsection

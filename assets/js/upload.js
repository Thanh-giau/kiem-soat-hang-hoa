/**
 * AJAX Excel Upload & Smart Processing (Crash-Proof)
 */

let currentUploadData = null;

// Safe DOM Helper functions (Prevents any "Cannot set properties of null" error)
function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
}

function setDisplay(id, displayStyle) {
    const el = document.getElementById(id);
    if (el) el.style.display = displayStyle;
}

function setHTML(id, htmlContent) {
    const el = document.getElementById(id);
    if (el) el.innerHTML = htmlContent;
}

document.addEventListener('DOMContentLoaded', () => {
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('excel_file');

    if (dropzone && fileInput) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropzone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            }, false);
        });

        dropzone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect(files[0]);
            }
        });

        dropzone.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                handleFileSelect(fileInput.files[0]);
            }
        });
    }
});

function handleFileSelect(file) {
    const fileNameEl = document.getElementById('selected-file-name');
    if (fileNameEl) {
        fileNameEl.textContent = file.name + " (" + (file.size / 1024).toFixed(1) + " KB)";
        fileNameEl.style.display = 'block';
    }
    setDisplay('btn-analyze', 'inline-flex');
}

function analyzeFile() {
    const fileInput = document.getElementById('excel_file');
    const ngayBanEl = document.getElementById('ngay_ban');
    const ngayBan = ngayBanEl ? ngayBanEl.value : '';

    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        alert('Vui lòng chọn file Excel (.xlsx, .xls hoặc .csv)!');
        return;
    }

    if (!ngayBan) {
        alert('Vui lòng chọn ngày áp dụng số bán!');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'analyze');
    formData.append('excel_file', fileInput.files[0]);
    formData.append('ngay_ban', ngayBan);

    showLoading(true);

    fetch('xu_ly.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showLoading(false);
        if (!data.success) {
            alert('Lỗi xử lý file: ' + data.message);
            return;
        }

        currentUploadData = data;
        renderAnalysisResult(data);
    })
    .catch(err => {
        showLoading(false);
        alert('Đã xảy ra lỗi kết nối: ' + err.message);
    });
}

function renderAnalysisResult(data) {
    if (!data || !data.data) {
        alert('Dữ liệu phân tích không hợp lệ từ máy chủ!');
        return;
    }

    // 1. Chuyển đổi vùng hiển thị
    setDisplay('upload-area', 'none');
    setDisplay('analysis-result', 'block');

    const kiemKeList = data.data.kiem_ke || [];
    const boQuaList = data.data.bo_qua || [];
    const moiList = data.data.san_pham_moi || [];

    // 2. Cập nhật số liệu thống kê an toàn
    setText('count-kiem-ke', kiemKeList.length);
    setText('count-bo-qua', boQuaList.length);
    setText('count-moi', moiList.length);

    // 3. Xử lý cảnh báo trùng lặp
    if (data.duplicate) {
        setDisplay('duplicate-warning', 'flex');
        setText('duplicate-message', data.duplicate_message || 'Dữ liệu ngày này đã tồn tại!');
    } else {
        setDisplay('duplicate-warning', 'none');
    }

    // 4. Xử lý danh sách sản phẩm mới
    if (moiList.length > 0) {
        setDisplay('new-items-alert', 'block');
        
        let htmlNew = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; background: #fff; padding: 12px 16px; border-radius: var(--border-radius); border: 1px solid var(--border-color); flex-wrap: wrap; gap: 10px;">
                <div>
                    <strong style="color: var(--danger); font-size: 15px;">Phát hiện ${moiList.length} món mới chưa có trong hệ thống</strong>
                    <div style="font-size: 12px; color: var(--text-secondary);">Hệ thống đã nhận diện sẵn phân nhóm Bánh / Đồ ăn / Nước suối vs Đồ pha chế.</div>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-sm btn-primary" onclick="resolveAllNew('auto')">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> ⚡ Tự Động Phân Loại Toàn Bộ
                    </button>
                    <button type="button" class="btn btn-sm btn-success" onclick="resolveAllNew('all_inventory')">
                        <i class="fa-solid fa-check-double"></i> Chọn Tất Cả Cần Kiểm Kê
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="resolveAllNew('all_skip')">
                        <i class="fa-solid fa-forward"></i> Chọn Tất Cả Bỏ Qua
                    </button>
                </div>
            </div>
            <div style="max-height: 380px; overflow-y: auto; padding-right: 4px;">
        `;

        moiList.forEach(item => {
            const isAutoKiemKe = item.auto_kiem_ke === 1;
            const badgeTag = isAutoKiemKe 
                ? '<span class="badge badge-success">🍰 Gợi ý: Cần kiểm kê</span>' 
                : '<span class="badge badge-secondary">☕ Gợi ý: Bỏ qua</span>';

            htmlNew += `
                <div class="card" style="margin-bottom: 10px; padding: 12px 16px; border-left: 4px solid ${isAutoKiemKe ? 'var(--success)' : 'var(--secondary)'};">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <strong style="font-size: 15px; color: var(--text-primary);">${escapeHtml(item.ma_sp)}</strong> - 
                            <span style="font-weight: 600;">${escapeHtml(item.ten_sp)}</span> 
                            <span class="badge badge-secondary" style="margin-left: 6px;">${escapeHtml(item.nhom_sp || 'Khác')}</span>
                            ${badgeTag}
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                Số bán trong file: <strong style="color: var(--primary);">${item.so_luong}</strong>
                            </div>
                        </div>
                        <div style="display: flex; gap: 6px;">
                            <button type="button" class="btn btn-sm ${isAutoKiemKe ? 'btn-success' : 'btn-secondary'}" onclick="resolveNewItem('${escapeJs(item.ma_sp)}', 1)">
                                <i class="fa-solid fa-check"></i> Cần Kiểm Kê
                            </button>
                            <button type="button" class="btn btn-sm ${!isAutoKiemKe ? 'btn-warning' : 'btn-secondary'}" onclick="resolveNewItem('${escapeJs(item.ma_sp)}', 0)">
                                <i class="fa-solid fa-xmark"></i> Bỏ Qua
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="resolveNewItem('${escapeJs(item.ma_sp)}', -1)">
                                <i class="fa-solid fa-ban"></i> Loại Bỏ
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        htmlNew += `</div>`;
        setHTML('new-items-container', htmlNew);
    } else {
        setDisplay('new-items-alert', 'none');
    }

    // 5. Render bảng sản phẩm kiểm kê
    let htmlKK = '';
    kiemKeList.forEach(item => {
        let dinhLuongNote = '';
        if (item.ghi_chu_dinh_luong && item.ghi_chu_dinh_luong.length > 0) {
            dinhLuongNote = `
                <div style="font-size: 11px; color: #0284c7; background: #f0f9ff; padding: 2px 8px; border-radius: 4px; display: inline-block; margin-top: 4px; border: 1px solid #bae6fd;">
                    <i class="fa-solid fa-diagram-project"></i> ${escapeHtml(item.ghi_chu_dinh_luong.join('; '))}
                </div>
            `;
        }

        htmlKK += `
            <tr>
                <td><strong style="color: var(--primary);">${escapeHtml(item.ma_sp)}</strong></td>
                <td>
                    <div style="font-weight: 600;">${escapeHtml(item.ten_sp)}</div>
                    ${dinhLuongNote}
                </td>
                <td><span class="badge badge-secondary">${escapeHtml(item.nhom_sp || 'Khác')}</span></td>
                <td style="text-align: right; font-weight: 700; color: var(--primary); font-size: 15px;">${item.so_luong}</td>
                <td style="text-align: center;"><span class="badge badge-success">✅ Lưu & Kiểm kê</span></td>
            </tr>
        `;
    });
    setHTML('tbody-kiem-ke', htmlKK || '<tr><td colspan="5" style="text-align:center; padding: 20px; color: var(--text-muted);">Không có sản phẩm nào thuộc diện kiểm kê.</td></tr>');

    // 6. Render bảng sản phẩm bỏ qua
    let htmlBQ = '';
    boQuaList.forEach(item => {
        let actionBadge = '<span class="badge badge-secondary">❌ Tự động bỏ qua</span>';
        if (item.is_recipe_dish) {
            actionBadge = '<span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 600;"><i class="fa-solid fa-calculator"></i> Đã bóc tách NVL</span>';
        }

        htmlBQ += `
            <tr>
                <td><strong>${escapeHtml(item.ma_sp)}</strong></td>
                <td>${escapeHtml(item.ten_sp)}</td>
                <td><span class="badge badge-secondary">${escapeHtml(item.nhom_sp || 'Khác')}</span></td>
                <td style="text-align: right;">${item.so_luong}</td>
                <td style="text-align: center;">${actionBadge}</td>
            </tr>
        `;
    });
    setHTML('tbody-bo-qua', htmlBQ || '<tr><td colspan="5" style="text-align:center; padding: 20px; color: var(--text-muted);">Không có sản phẩm bỏ qua.</td></tr>');
}

function resolveNewItem(maSP, choice) {
    if (!currentUploadData) return;
    
    fetch('xu_ly.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'resolve_new_item',
            ma_sp: maSP,
            choice: choice,
            session_key: currentUploadData.session_key
        })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            currentUploadData = res.updated_data;
            renderAnalysisResult(res.updated_data);
        } else {
            alert('Lỗi: ' + res.message);
        }
    });
}

function resolveAllNew(mode) {
    if (!currentUploadData) return;

    showLoading(true);

    fetch('xu_ly.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'resolve_all_new',
            mode: mode,
            session_key: currentUploadData.session_key
        })
    })
    .then(res => res.json())
    .then(res => {
        showLoading(false);
        if (res.success) {
            currentUploadData = res.updated_data;
            renderAnalysisResult(res.updated_data);
        } else {
            alert('Lỗi: ' + res.message);
        }
    })
    .catch(err => {
        showLoading(false);
        alert('Lỗi xử lý hàng loạt: ' + err.message);
    });
}

function commitSave(ghiDe = false) {
    if (!currentUploadData) return;

    showLoading(true);

    fetch('xu_ly.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'commit_save',
            session_key: currentUploadData.session_key,
            ghi_de: ghiDe ? '1' : '0'
        })
    })
    .then(res => res.json())
    .then(data => {
        showLoading(false);
        if (data.success) {
            alert(data.message);
            window.location.href = 'lich_su.php';
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(err => {
        showLoading(false);
        alert('Lỗi lưu số bán: ' + err.message);
    });
}

function resetUpload() {
    currentUploadData = null;
    setDisplay('upload-area', 'block');
    setDisplay('analysis-result', 'none');
    const fileInput = document.getElementById('excel_file');
    if (fileInput) fileInput.value = '';
    setDisplay('selected-file-name', 'none');
    setDisplay('btn-analyze', 'none');
}

function showLoading(show) {
    let loader = document.getElementById('app-loading');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'app-loading';
        loader.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.6);z-index:9999;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;font-weight:600;';
        loader.innerHTML = '<div style="background:#1e293b;padding:20px 30px;border-radius:12px;display:flex;align-items:center;gap:12px;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i> Đang đọc file và đối chiếu cơ sở dữ liệu...</div>';
        document.body.appendChild(loader);
    }
    loader.style.display = show ? 'flex' : 'none';
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function escapeJs(text) {
    if (!text) return '';
    return String(text).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

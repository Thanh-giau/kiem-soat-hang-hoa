        </main>
    </div>
</div>
<?php
$connInfo = function_exists('layThongTinKetNoiMobile') ? layThongTinKetNoiMobile() : [
    'online_url' => 'https://statistical-fit-warrant-mainly.trycloudflare.com',
    'lan_url' => 'http://192.168.1.26:8000',
    'lan_ip' => '192.168.1.26'
];
$onlineUrl = $connInfo['online_url'];
$lanUrl = $connInfo['lan_url'];
$qrOnline = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($onlineUrl);
$qrLan = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($lanUrl);
?>
<!-- Modal Hướng Dẫn Kết Nối Điện Thoại & Máy Tính Bảng -->
<div class="modal-overlay" id="modal-mobile-qr">
    <div class="modal-card" style="max-width: 480px; text-align: center;">
        <div class="modal-header" style="background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-bottom: 1px solid #c7d2fe;">
            <div class="modal-title" style="display: flex; align-items: center; gap: 8px; color: #3730a3; font-weight: 800;">
                <i class="fa-solid fa-mobile-screen-button" style="color: var(--primary);"></i>
                <span>Sử Dụng Trên Điện Thoại & Tablet</span>
            </div>
            <button type="button" onclick="closeModal('modal-mobile-qr')" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b; line-height: 1;">&times;</button>
        </div>
        
        <div class="modal-body" style="padding: 20px;">
            <!-- Tabs chuyển đổi Online (4G/Ngoài Quán) vs Wi-Fi Tại Quán -->
            <div style="display: flex; background: #f1f5f9; padding: 4px; border-radius: 10px; margin-bottom: 16px;">
                <button type="button" id="tab-btn-online" class="btn btn-sm" onclick="switchMobileTab('online')" style="flex: 1; font-weight: 700; border-radius: 8px; background: #fff; color: var(--primary); box-shadow: 0 2px 4px rgba(0,0,0,0.06); border: none;">
                    🌐 Dùng 4G / Ngoài Quán
                </button>
                <button type="button" id="tab-btn-lan" class="btn btn-sm" onclick="switchMobileTab('lan')" style="flex: 1; font-weight: 600; border-radius: 8px; background: transparent; color: var(--text-secondary); border: none;">
                    📶 Cùng Wi-Fi Quán (LAN)
                </button>
            </div>

            <!-- Tab 1: Dùng 4G / Ngoài Quán (Cloudflare Tunnel) -->
            <div id="mobile-tab-online">
                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px;">
                    Mở <strong>Camera</strong> hoặc <strong>Zalo</strong> quét mã bên dưới để truy cập từ bất kỳ đâu (4G/5G hoặc Wi-Fi nhà):
                </div>

                <div style="background: #ffffff; padding: 10px; border-radius: 12px; display: inline-block; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid var(--border-color); margin-bottom: 14px;">
                    <img src="<?= $qrOnline ?>" alt="Mã QR Online" style="width: 180px; height: 180px; display: block; border-radius: 6px;">
                </div>

                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 14px; font-size: 13px; margin-bottom: 14px; text-align: left;">
                    <div style="color: var(--text-secondary); font-size: 11.5px; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
                        <span>Link Online Toàn Cầu (Cloudflare):</span>
                        <span class="badge badge-success" style="font-size: 10px;">🟢 Đang Hoạt Động</span>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <input type="text" id="link-online-text" value="<?= htmlspecialchars($onlineUrl) ?>" readonly class="form-control" style="font-size: 13px; font-weight: 700; color: var(--primary); background: #fff; padding: 6px 10px;">
                        <button type="button" class="btn btn-primary btn-sm" onclick="copyMobileLink('link-online-text', this)" style="white-space: nowrap;">
                            <i class="fa-solid fa-copy"></i> Sao chép
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Wi-Fi Quán (LAN) -->
            <div id="mobile-tab-lan" style="display: none;">
                <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 12px;">
                    Khi điện thoại đang <strong>kết nối chung Wi-Fi của quán</strong>, quét mã hoặc truy cập link nội bộ cực nhanh:
                </div>

                <div style="background: #ffffff; padding: 10px; border-radius: 12px; display: inline-block; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid var(--border-color); margin-bottom: 14px;">
                    <img src="<?= $qrLan ?>" alt="Mã QR Wi-Fi Quán" style="width: 180px; height: 180px; display: block; border-radius: 6px;">
                </div>

                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 14px; font-size: 13px; margin-bottom: 14px; text-align: left;">
                    <div style="color: var(--text-secondary); font-size: 11.5px; margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
                        <span>Link Mạng Wi-Fi Nội Bộ (LAN):</span>
                        <span class="badge badge-primary" style="font-size: 10px;">📶 Siêu Nhanh</span>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <input type="text" id="link-lan-text" value="<?= htmlspecialchars($lanUrl) ?>" readonly class="form-control" style="font-size: 13px; font-weight: 700; color: #0284c7; background: #fff; padding: 6px 10px;">
                        <button type="button" class="btn btn-primary btn-sm" onclick="copyMobileLink('link-lan-text', this)" style="white-space: nowrap;">
                            <i class="fa-solid fa-copy"></i> Sao chép
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mẹo ghim ra màn hình chính -->
            <div style="font-size: 12px; color: var(--text-secondary); line-height: 1.5; text-align: left; background: #fffbeb; padding: 10px 14px; border-radius: 8px; border: 1px solid #fde68a;">
                💡 <strong>Cách ghim dùng như App:</strong> Khi đã vào web trên điện thoại:
                <ul style="margin: 4px 0 0 16px; padding: 0;">
                    <li><strong>iPhone (Safari):</strong> Bấm nút <i class="fa-solid fa-arrow-up-from-bracket"></i> (Chia sẻ) &rarr; chọn <strong>"Thêm vào MH chính"</strong>.</li>
                    <li><strong>Android (Chrome):</strong> Bấm nút <strong>⋮</strong> (3 chấm góc phải) &rarr; chọn <strong>"Cài đặt ứng dụng"</strong> hoặc <strong>"Thêm vào màn hình chính"</strong>.</li>
                </ul>
            </div>
        </div>

        <div class="modal-footer" style="justify-content: center; background: #f8fafc;">
            <button type="button" class="btn btn-primary" onclick="closeModal('modal-mobile-qr')">
                <i class="fa-solid fa-check"></i> Đã Hiểu
            </button>
        </div>
    </div>
</div>

<script>
function switchMobileTab(tab) {
    const tabOnline = document.getElementById('mobile-tab-online');
    const tabLan = document.getElementById('mobile-tab-lan');
    const btnOnline = document.getElementById('tab-btn-online');
    const btnLan = document.getElementById('tab-btn-lan');

    if (tab === 'online') {
        tabOnline.style.display = 'block';
        tabLan.style.display = 'none';
        btnOnline.style.background = '#fff';
        btnOnline.style.color = 'var(--primary)';
        btnOnline.style.boxShadow = '0 2px 4px rgba(0,0,0,0.06)';
        btnLan.style.background = 'transparent';
        btnLan.style.color = 'var(--text-secondary)';
        btnLan.style.boxShadow = 'none';
    } else {
        tabOnline.style.display = 'none';
        tabLan.style.display = 'block';
        btnLan.style.background = '#fff';
        btnLan.style.color = 'var(--primary)';
        btnLan.style.boxShadow = '0 2px 4px rgba(0,0,0,0.06)';
        btnOnline.style.background = 'transparent';
        btnOnline.style.color = 'var(--text-secondary)';
        btnOnline.style.boxShadow = 'none';
    }
}

function copyMobileLink(inputId, btnElement) {
    const input = document.getElementById(inputId);
    if (input) {
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(() => {
            const oldHtml = btnElement.innerHTML;
            btnElement.innerHTML = '<i class="fa-solid fa-check"></i> Đã chép!';
            btnElement.classList.remove('btn-primary');
            btnElement.classList.add('btn-success');
            setTimeout(() => {
                btnElement.innerHTML = oldHtml;
                btnElement.classList.remove('btn-success');
                btnElement.classList.add('btn-primary');
            }, 2000);
        }).catch(() => {
            alert('Đã chọn link: ' + input.value);
        });
    }
}
</script>

<!-- Core JavaScript -->
<script src="<?= BASE_URL ?>/assets/js/main.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/assets/js/searchable-select.js?v=<?= time() ?>"></script>
<?php if (isset($extraJs)): ?>
    <?php foreach ($extraJs as $js): ?>
        <script src="<?= BASE_URL ?>/assets/js/<?= $js ?>?v=<?= time() ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>

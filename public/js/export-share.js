/**
 * Export & Share Features
 * PDF print, CSV export, social sharing, copy link
 */

// ==================== PRINT REPORT AS PDF ====================
function printReport() {
    window.print();
}

// ==================== EXPORT TO CSV ====================
function exportToCSV(data, filename) {
    if (!data || !data.length) {
        toast.warning('No data to export');
        return;
    }

    const headers = Object.keys(data[0]);
    const csvContent = [
        headers.join(','),
        ...data.map(row => headers.map(h => {
            let val = row[h] ?? '';
            // Escape commas and quotes
            if (typeof val === 'string' && (val.includes(',') || val.includes('"'))) {
                val = '"' + val.replace(/"/g, '""') + '"';
            }
            return val;
        }).join(','))
    ].join('\n');

    const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename + '.csv';
    link.click();
    URL.revokeObjectURL(link.href);

    toast.success('CSV exported successfully!');
}

// ==================== SHARE REPORT ====================
function shareReport(reportId, reportTitle) {
    const url = window.location.origin + '/reports/' + reportId;
    const text = `Check out this lost & found report: ${reportTitle}`;

    if (navigator.share) {
        navigator.share({
            title: reportTitle,
            text: text,
            url: url
        }).catch(() => {});
    } else {
        // Fallback: copy to clipboard
        copyToClipboard(url);
    }
}

// ==================== COPY TO CLIPBOARD ====================
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        toast.success('Link copied to clipboard!');
    }).catch(() => {
        // Fallback
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        toast.success('Link copied to clipboard!');
    });
}

// ==================== SHARE VIA SOCIAL ====================
function shareViaFacebook(url) {
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url), '_blank', 'width=600,height=400');
}

function shareViaTwitter(url, text) {
    window.open('https://twitter.com/intent/tweet?text=' + encodeURIComponent(text) + '&url=' + encodeURIComponent(url), '_blank', 'width=600,height=400');
}

function shareViaEmail(subject, body) {
    window.location.href = 'mailto:?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(body);
}

// ==================== SHARE MODAL ====================
function showShareModal(reportId, reportTitle) {
    const url = window.location.origin + '/reports/' + reportId;
    const text = `Check out this lost & found report: ${reportTitle}`;

    let modal = document.getElementById('share-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'share-modal';
        modal.className = 'shortcuts-modal';
        modal.innerHTML = `
            <div class="shortcuts-content" style="max-width:400px">
                <div class="shortcuts-title">
                    <i class="bi bi-share"></i> Share Report
                </div>
                <div style="display:flex;flex-direction:column;gap:0.75rem">
                    <button onclick="copyToClipboard('${url}')" class="share-option">
                        <i class="bi bi-link-45deg" style="color:#0041C7"></i>
                        <span>Copy Link</span>
                    </button>
                    <button onclick="shareViaFacebook('${url}')" class="share-option">
                        <i class="bi bi-facebook" style="color:#1877F2"></i>
                        <span>Facebook</span>
                    </button>
                    <button onclick="shareViaTwitter('${url}', '${text}')" class="share-option">
                        <i class="bi bi-twitter-x" style="color:#000"></i>
                        <span>Twitter / X</span>
                    </button>
                    <button onclick="shareViaEmail('Report #${reportId}', '${text}\\n\\n${url}')" class="share-option">
                        <i class="bi bi-envelope" style="color:#EA4335"></i>
                        <span>Email</span>
                    </button>
                </div>
                <div style="margin-top:1.5rem;text-align:center">
                    <button onclick="document.getElementById('share-modal').classList.remove('active')" 
                            style="padding:0.5rem 1.5rem;background:#e2e8f0;color:#475569;border:none;border-radius:8px;cursor:pointer;font-weight:600">
                        Close
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.remove('active');
        });
    }
    modal.classList.add('active');
}

// Add share option styles
const shareStyles = document.createElement('style');
shareStyles.textContent = `
    .share-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.875rem;
        font-weight: 500;
        color: #1e293b;
        font-family: inherit;
    }
    .share-option:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateX(4px);
    }
    .share-option i {
        font-size: 1.25rem;
        width: 24px;
        text-align: center;
    }
    @media print {
        .main-navbar, .main-sidebar, .sidebar-overlay, .skip-link, 
        .dark-mode-toggle, .btn, form, .share-btn, #toast-container { 
            display: none !important; 
        }
        .content-wrap { padding: 0 !important; margin: 0 !important; }
        .container { max-width: 100% !important; }
        body { padding-top: 0 !important; }
    }
`;
document.head.appendChild(shareStyles);

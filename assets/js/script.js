// Edutrack Student Management System - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeSidebar();
    initializeTooltips();
    initializeFormValidation();
    initializeCharts();
    initializeDarkMode();
    initializeSearch();
    initializeDataTables();
});

// Sidebar Toggle
function initializeSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mobileToggle = document.querySelector('.sidebar-toggle.mobile-toggle');
    const desktopToggle = document.querySelector('.sidebar-toggle.desktop-toggle');
    if (!sidebar) return;

    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true' && window.innerWidth > 768) sidebar.classList.add('collapsed');

    desktopToggle?.addEventListener('click', e => {
        e.preventDefault();
        sidebar.classList.toggle('collapsed');
        if (window.innerWidth > 768) localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    });

    mobileToggle?.addEventListener('click', e => {
        e.preventDefault();
        sidebar.classList.toggle('show');
        document.body.classList.toggle('sidebar-open', sidebar.classList.contains('show'));
    });

    document.addEventListener('click', e => {
        if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !e.target.closest('.sidebar-toggle')) {
            sidebar.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('show');
            document.body.classList.remove('sidebar-open');
            if (localStorage.getItem('sidebarCollapsed') === 'true') sidebar.classList.add('collapsed');
        } else sidebar.classList.remove('collapsed');
    });
}

// Bootstrap Tooltips
function initializeTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
}

// Form Validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', e => {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });

        form.querySelectorAll('input[required], select[required], textarea[required]').forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => input.classList.contains('is-invalid') && validateField(input));
        });
    });
}

function validateField(field) {
    field.classList.remove('is-valid', 'is-invalid');
    field.classList.add(field.checkValidity() ? 'is-valid' : 'is-invalid');
}

// Charts (Chart.js)
function initializeCharts() {
    const statsChart = document.getElementById('statisticsChart');
    if (statsChart && typeof Chart !== 'undefined') {
        new Chart(statsChart.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Students Enrolled',
                    data: [12, 19, 15, 25, 22, 30],
                    borderColor: 'rgb(74, 107, 255)',
                    backgroundColor: 'rgba(74, 107, 255, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    }

    const attendanceChart = document.getElementById('attendanceChart');
    if (attendanceChart && typeof Chart !== 'undefined') {
        new Chart(attendanceChart.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent', 'Late'],
                datasets: [{
                    data: [85, 10, 5],
                    backgroundColor: ['rgb(74, 107, 255)', '#dc3545', '#ffc107'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }
}

// DataTables
function initializeDataTables() {
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        $('.data-table').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[0, 'asc']],
            language: { search: "Search records:", lengthMenu: "Show _MENU_ entries", info: "Showing _START_ to _END_ of _TOTAL_ entries" }
        });
    }
}

// Toast Notifications
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    container.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// Confirmation Modal
function showConfirmModal(title, message, onConfirm) {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `<div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">${title}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">${message}</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-danger" id="confirmBtn">Confirm</button></div></div></div>`;
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    modal.querySelector('#confirmBtn').addEventListener('click', () => { onConfirm(); bsModal.hide(); });
    modal.addEventListener('hidden.bs.modal', () => modal.remove());
}

// Loading state helper
function showLoading(element) {
    const originalText = element.textContent;
    element.disabled = true;
    element.innerHTML = '<span class="loading"></span> Loading...';
    return () => { element.disabled = false; element.textContent = originalText; };
}

// AJAX Helper
function makeRequest(url, options = {}) {
    return fetch(url, Object.assign({
        method: 'GET',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }, options))
    .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
    .catch(err => { console.error(err); showToast('An error occurred', 'danger'); throw err; });
}

// Form Helpers
function serializeForm(form) {
    const data = {};
    new FormData(form).forEach((v, k) => data[k] = v);
    return data;
}
function resetForm(form) {
    form.reset();
    form.classList.remove('was-validated');
    form.querySelectorAll('.is-valid,.is-invalid').forEach(i => i.classList.remove('is-valid','is-invalid'));
}

// Search
function initializeSearch() {
    const input = document.querySelector('.search-input');
    const items = document.querySelectorAll('.searchable-item');
    if (!input || !items.length) return;
    input.addEventListener('input', () => {
        const q = input.value.toLowerCase().trim();
        items.forEach(i => i.style.display = i.textContent.toLowerCase().includes(q) ? '' : 'none');
    });
}

// Export table to CSV
function exportTableToCSV(tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId); if (!table) return;
    const rows = Array.from(table.querySelectorAll('tr')).map(r => Array.from(r.querySelectorAll('td,th')).map(c => `"${c.textContent.replace(/"/g,'""')}"`).join(','));
    const blob = new Blob([rows.join('\n')], { type:'text/csv' });
    const link = document.createElement('a'); link.href = URL.createObjectURL(blob); link.download = filename; link.click(); URL.revokeObjectURL(link.href);
}

// Auto-save form
function initializeAutoSave(selector, interval = 30000) {
    const form = document.querySelector(selector); if (!form) return;
    setInterval(() => localStorage.setItem(`autosave_${form.id}`, JSON.stringify(serializeForm(form))), interval);
    const saved = localStorage.getItem(`autosave_${form.id}`);
    if (saved) Object.entries(JSON.parse(saved)).forEach(([k,v]) => { const f = form.querySelector(`[name="${k}"]`); if(f) f.value = v; });
}

// Service Worker registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => fetch('/sw.js',{method:'HEAD'}).then(r => r.ok && navigator.serviceWorker.register('/sw.js')).catch(()=>{}));
}

// Global error handling
window.addEventListener('error', e => { if(e.error) console.error('Global error:', e.error); });
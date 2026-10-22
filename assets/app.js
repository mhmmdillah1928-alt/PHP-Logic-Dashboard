// JavaScript bersama untuk semua halaman
(function () {
    'use strict';

    // Membuat elemen dengan teks (aman dari injeksi HTML)
    function el(tag, className, text) {
        var node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function showPopup(data) {
        // Siapkan dialog berdasarkan data status dari halaman PHP.
        var icons = { success: '\u2713', error: '!', info: 'i' };
        var type = icons[data.type] ? data.type : 'info';
        var opener = document.activeElement;

        var overlay = el('div', 'popup-overlay');
        var box = el('div', 'popup-box popup-' + type);
        box.setAttribute('role', 'dialog');
        box.setAttribute('aria-modal', 'true');
        box.setAttribute('aria-labelledby', 'popup-title');

        box.appendChild(el('div', 'popup-icon', icons[type]));
        var title = el('h2', 'popup-title', data.title || '');
        title.id = 'popup-title';
        box.appendChild(title);
        if (data.message) box.appendChild(el('p', 'popup-message', data.message));

        // Tambahkan rincian hasil sebagai teks biasa agar konten tidak menjadi HTML.
        if (Array.isArray(data.items) && data.items.length) {
            var list = el('ul', 'popup-items');
            data.items.forEach(function (item) {
                var li = el('li');
                li.appendChild(el('span', '', item[0]));
                li.appendChild(el('strong', '', item[1]));
                list.appendChild(li);
            });
            box.appendChild(list);
        }

        var btn = el('button', 'popup-close', 'Tutup');
        btn.type = 'button';
        box.appendChild(btn);
        overlay.appendChild(box);

        // Tutup popup melalui tombol, Escape, atau klik pada latar.
        function close() {
            document.removeEventListener('keydown', onKey);
            overlay.classList.remove('show');
            document.body.classList.remove('popup-open');
            setTimeout(function () { overlay.remove(); }, 200);
            if (opener && opener.focus) opener.focus();
        }
        function onKey(ev) {
            if (ev.key === 'Escape') close();
            if (ev.key === 'Tab') { ev.preventDefault(); btn.focus(); }
        }

        btn.addEventListener('click', close);
        overlay.addEventListener('click', function (ev) { if (ev.target === overlay) close(); });
        document.addEventListener('keydown', onKey);

        document.body.appendChild(overlay);
        document.body.classList.add('popup-open');
        requestAnimationFrame(function () { overlay.classList.add('show'); btn.focus(); });
    }

    // Baca payload JSON yang dirender PHP; halaman tanpa payload tidak menampilkan popup.
    document.addEventListener('DOMContentLoaded', function () {
        var holder = document.getElementById('popup-data');
        if (!holder) return;
        try {
            showPopup(JSON.parse(holder.dataset.popup));
        } catch (err) {
            console.error('Data popup tidak valid', err);
        }
    });
})();

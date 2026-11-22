/*
 * Show/hide toggle for password fields. The button is rendered hidden by
 * the field components (resources/views/components/tombol-sandi.blade.php)
 * and only shown here, so without JavaScript the field stays a plain
 * password input. Each button controls only the input named in aria-controls.
 */
const LABEL = {
    tampilkan: 'Tampilkan kata sandi',
    sembunyikan: 'Sembunyikan kata sandi',
};

function pasang(tombol) {
    const input = document.getElementById(tombol.getAttribute('aria-controls'));

    if (!input) {
        return;
    }

    const ikonTampilkan = tombol.querySelector('[data-ikon="tampilkan"]');
    const ikonSembunyikan = tombol.querySelector('[data-ikon="sembunyikan"]');

    // Keep focus in the input when the button is pressed with a pointer.
    tombol.addEventListener('pointerdown', (event) => event.preventDefault());

    tombol.addEventListener('click', () => {
        const awal = input.selectionStart;
        const akhir = input.selectionEnd;
        const terlihat = input.type === 'password';

        input.type = terlihat ? 'text' : 'password';
        tombol.setAttribute('aria-pressed', String(terlihat));
        tombol.setAttribute('aria-label', terlihat ? LABEL.sembunyikan : LABEL.tampilkan);
        ikonTampilkan.hidden = terlihat;
        ikonSembunyikan.hidden = !terlihat;

        // Back to the input with the caret (or selection) where it was.
        input.focus();
        if (awal !== null && akhir !== null) {
            input.setSelectionRange(awal, akhir);
        }
    });

    tombol.hidden = false;
}

export function pasangTombolSandi(akar = document) {
    akar.querySelectorAll('[data-tampilkan-sandi]').forEach(pasang);
}

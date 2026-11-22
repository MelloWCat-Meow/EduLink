/*
 * The landing page needs no JavaScript: the mobile menu and FAQ are
 * <details> elements and every animation is CSS. Behaviour that forms
 * need is added here, always as an enhancement on top of working HTML.
 */
import { pasangTombolSandi } from './kata-sandi';

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => pasangTombolSandi());
} else {
    pasangTombolSandi();
}

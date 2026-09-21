import './bootstrap';

const BASE = document.querySelector('base')?.href || '/';

function go(p) {
    location.href = BASE + p.replace(/^\//, '');
}

function toggleMobile() {
    document.getElementById('mobileSheet').classList.toggle('hidden');
}

function openLogout() {
    document.getElementById('logoutModal').classList.remove('hidden');
}

function closeLogout() {
    document.getElementById('logoutModal').classList.add('hidden');
}

function openProfile() {
    document.getElementById('profileModal').classList.remove('hidden');
}

function closeProfile() {
    document.getElementById('profileModal').classList.add('hidden');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeLogout(); closeProfile(); }
    if (e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
        e.preventDefault();
        const s = document.querySelector('#sidebar + * input, header input');
        if (s) s.focus();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    document.body.classList.add('page-enter');

    const main = document.querySelector('main');
    if (main) {
        let container = main;
        if (main.children.length === 1) container = main.firstElementChild;
        const io = new IntersectionObserver(ents => {
            ents.forEach(en => {
                if (!en.isIntersecting) return;
                const el = en.target;
                el.classList.add('rv');
                el.style.animationDelay = ((el.dataset.i % 6) * 90) + 'ms';
                io.unobserve(el);
            });
        }, { threshold: 0.05, rootMargin: '0px 0px -24px' });
        container.querySelectorAll(':scope > *').forEach((el, i) => {
            el.dataset.i = i;
            io.observe(el);
        });
    }

    document.querySelectorAll('[data-pop]').forEach((el, i) => {
        el.style.animationDelay = (120 + i * 130) + 'ms';
        el.classList.add('num-pop');
    });

    document.querySelectorAll('#sidebar nav > a').forEach((a, i) => {
        a.classList.add('nav-slide');
        a.style.animationDelay = (80 + i * 55) + 'ms';
    });
});

window.go = go;
window.toggleMobile = toggleMobile;
window.openLogout = openLogout;
window.closeLogout = closeLogout;
window.openProfile = openProfile;
window.closeProfile = closeProfile;
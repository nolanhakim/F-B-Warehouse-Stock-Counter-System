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

function toggleChat() {
    const panel = document.getElementById('chatPanel');
    if (!panel) return;
    panel.classList.toggle('hidden');
    if (!panel.classList.contains('hidden')) {
        const log = document.getElementById('chatLog');
        log.scrollTop = log.scrollHeight;
        document.getElementById('chatInput')?.focus();
    }
}

function chatRender(text, who) {
    const log = document.getElementById('chatLog');
    const wrap = document.createElement('div');
    wrap.className = 'flex gap-2';
    const bubble = document.createElement('div');
    bubble.className = who === 'user'
        ? 'ml-auto max-w-[85%] bg-brand text-white rounded-lg px-3 py-2'
        : 'max-w-[85%] bg-white border border-slate-200 rounded-lg px-3 py-2 shadow-sm';
    const safe = document.createElement('span');
    safe.textContent = text;
    bubble.innerHTML = safe.innerHTML.replace(/\*\*(.+?)\*\*/g, '<b>$1</b>').replace(/\n/g, '<br>');
    bubble.style.whiteSpace = 'pre-wrap';
    wrap.appendChild(bubble);
    log.appendChild(wrap);
    log.scrollTop = log.scrollHeight;
    return wrap;
}

async function askQuick(btn) {
    const form = document.getElementById('chatForm');
    document.getElementById('chatInput').value = btn.dataset.q;
    form.requestSubmit();
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('chatForm');
    if (!form) return;

    const input = document.getElementById('chatInput');
    const send = document.getElementById('chatSend');
    const history = [];

    form.addEventListener('submit', async e => {
        e.preventDefault();
        const msg = input.value.trim();
        if (!msg || send.disabled) return;
        input.value = '';
        chatRender(msg, 'user');
        history.push({ role: 'user', content: msg });

        const think = chatRender('…', 'bot');
        send.disabled = true;

        try {
            const res = await fetch(BASE + 'chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                },
                body: JSON.stringify({ message: msg, history: history.slice(-10, -1) }),
            });
            const data = await res.json();
            think.remove();
            const reply = data.reply || 'Maaf, tidak ada jawaban.';
            chatRender(reply, 'bot');
            history.push({ role: 'assistant', content: reply });
        } catch (err) {
            think.remove();
            chatRender('Gagal kirim: ' + err.message, 'bot');
        } finally {
            send.disabled = false;
            input.focus();
        }
    });
});

window.toggleChat = toggleChat;
window.askQuick = askQuick;
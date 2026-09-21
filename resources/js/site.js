import Swiper from 'swiper';
import { Autoplay, Navigation, Pagination } from 'swiper/modules';
import GLightbox from 'glightbox';

const PAGINATION_SCROLL_KEY = 'paginationScrollY';

if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}
restorePaginationScroll();

document.addEventListener('DOMContentLoaded', () => {
    initNav();
    initLanguageToggle();
    initGalleryTabs();
    initLightbox();
    initSwipers();
    initPaginationScrollPreservation();
});

// Re-init after Livewire morphs the DOM (component updates)
document.addEventListener('livewire:navigated', () => {
    initSwipers();
    initLightbox();
});

document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.updated', () => {
        initLightbox();
    });
});

function initNav() {
    const hamburger = document.getElementById('nav-hamburger');
    const menu = document.getElementById('nav-menu');

    if (hamburger && menu) {
        hamburger.addEventListener('click', () => {
            const isOpen = menu.classList.toggle('flex');
            menu.classList.toggle('hidden');
            hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    document.querySelectorAll('.nav-dropdown').forEach((dropdown) => {
        const toggle = dropdown.querySelector('.nav-dropdown-toggle');
        const submenu = dropdown.querySelector('.nav-dropdown-menu');

        if (!toggle || !submenu) return;

        toggle.addEventListener('click', (e) => {
            e.stopPropagation();

            const isMobile = window.matchMedia('(max-width: 767px)').matches;

            if (isMobile) {
                submenu.classList.toggle('hidden');
                submenu.classList.toggle('flex');
            } else {
                const isHidden = submenu.classList.contains('hidden');
                closeAllDropdowns();
                if (isHidden) {
                    submenu.classList.remove('hidden');
                    submenu.classList.add('md:flex');
                }
            }
        });
    });

    document.addEventListener('click', closeAllDropdowns);

    function closeAllDropdowns() {
        document.querySelectorAll('.nav-dropdown-menu').forEach((m) => {
            m.classList.add('hidden');
            m.classList.remove('flex', 'md:flex');
        });
    }
}

function initLanguageToggle() {
    const toggle = document.getElementById('lang-toggle');
    if (!toggle) return;

    const apply = (lang) => {
        document.documentElement.setAttribute('data-lang', lang);
        document.querySelectorAll('[data-lang-option]').forEach((el) => {
            const active = el.dataset.langOption === lang;
            el.classList.toggle('lang-active', active);
            el.classList.toggle('text-white/70', !active);
        });
        document.querySelectorAll('[data-np][data-en]').forEach((el) => {
            el.textContent = lang === 'np' ? el.dataset.np : el.dataset.en;
        });
    };

    const saved = localStorage.getItem('lang') || 'np';
    apply(saved);

    toggle.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-lang') || 'np';
        const next = current === 'np' ? 'en' : 'np';
        localStorage.setItem('lang', next);
        apply(next);
    });
}

function initGalleryTabs() {
    document.querySelectorAll('.gallery-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            const container = tab.closest('div').parentElement;
            const target = tab.dataset.tab;

            container.querySelectorAll('.gallery-tab').forEach((t) => t.classList.remove('active-tab', 'bg-maroon', 'text-white'));
            tab.classList.add('active-tab', 'bg-maroon', 'text-white');

            container.querySelectorAll('.gallery-tab-panel').forEach((panel) => {
                if (panel.dataset.panel === target) {
                    panel.classList.remove('hidden');
                    panel.classList.add('grid');
                } else {
                    panel.classList.add('hidden');
                    panel.classList.remove('grid');
                }
            });
        });
    });
}

let lightboxInstance = null;

function initLightbox() {
    document.querySelectorAll('.glightbox-img').forEach((el) => {
        if (!el.dataset.type) el.dataset.type = 'image';
        el.classList.add('glightbox');
    });
    document.querySelectorAll('.glightbox-video').forEach((el) => {
        if (!el.dataset.type) el.dataset.type = 'video';
        el.classList.add('glightbox');
    });

    if (lightboxInstance) lightboxInstance.destroy();
    lightboxInstance = GLightbox({ selector: '.glightbox', touchNavigation: true, loop: true });
}

function initSwipers() {
    const heroEl = document.querySelector('.hero-swiper');
    if (heroEl && !heroEl.dataset.swiperInit) {
        heroEl.dataset.swiperInit = '1';
        new Swiper(heroEl, {
            modules: [Autoplay, Pagination],
            loop: heroEl.querySelectorAll('.swiper-slide').length > 1,
            autoplay: { delay: 5000, disableOnInteraction: false },
            pagination: { el: '.swiper-pagination', clickable: true },
        });
    }

    const presidentsEl = document.querySelector('.presidents-swiper');
    if (presidentsEl && !presidentsEl.dataset.swiperInit) {
        presidentsEl.dataset.swiperInit = '1';
        new Swiper(presidentsEl, {
            modules: [Navigation, Autoplay],
            slidesPerView: 2,
            spaceBetween: 16,
            loop: true,
            autoplay: { delay: 3500, disableOnInteraction: false },
            breakpoints: {
                640: { slidesPerView: 3 },
                1024: { slidesPerView: 5 },
            },
        });
    }
}

function initDashboardSidebar() {
    const toggle = document.getElementById('dashboard-sidebar-toggle');
    const sidebar = document.getElementById('dashboard-sidebar');
    const overlay = document.getElementById('dashboard-overlay');

    if (!toggle || !sidebar || !overlay) return;

    const open = () => {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('opacity-0', 'pointer-events-none');
    };
    const close = () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('opacity-0', 'pointer-events-none');
    };

    toggle.addEventListener('click', open);
    overlay.addEventListener('click', close);
    document.addEventListener('keydown', (e) => e.key === 'Escape' && close());

    // Collapsible menu groups (animated with CSS, see .nav-group in app.css)
    sidebar.querySelectorAll('.nav-group').forEach((group) => {
        const button = group.querySelector('.nav-group-toggle');
        button.addEventListener('click', () => {
            const isOpen = group.classList.toggle('is-open');
            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });

    // Bring the current page into view in a long menu
    sidebar.querySelector('.dash-sublink.is-active, .dash-link.is-active')?.scrollIntoView({ block: 'nearest' });
}

document.addEventListener('DOMContentLoaded', initDashboardSidebar);

// Keep the scroll position when navigating standard (non-Livewire) pagination links,
// instead of letting the browser snap back to the top of the new page.
function initPaginationScrollPreservation() {
    document.addEventListener('click', (e) => {
        const link = e.target.closest('nav[aria-label="Pagination Navigation"] a[href]');
        if (link) {
            sessionStorage.setItem(PAGINATION_SCROLL_KEY, String(window.scrollY));
        }
    });
}

function restorePaginationScroll() {
    const saved = sessionStorage.getItem(PAGINATION_SCROLL_KEY);
    if (saved === null) return;

    sessionStorage.removeItem(PAGINATION_SCROLL_KEY);
    const y = parseInt(saved, 10);
    window.scrollTo(0, y);
    requestAnimationFrame(() => window.scrollTo(0, y));
}

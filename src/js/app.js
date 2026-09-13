import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';
import '../styles/app.scss';

// The sticky header occupies normal document space above the hero.
const header = document.querySelector('.site-header');
if (header) {
  const updateHeaderHeight = () => {
    document.documentElement.style.setProperty('--site-header-height', `${header.getBoundingClientRect().height}px`);
  };
  updateHeaderHeight();
  if ('ResizeObserver' in window) {
    new ResizeObserver(updateHeaderHeight).observe(header);
  } else {
    window.addEventListener('resize', updateHeaderHeight, { passive: true });
  }
}

Alpine.plugin(collapse);
Alpine.plugin(intersect);

const blockModules = import.meta.glob('../../blocks/*/view.js', { eager: true });

Object.values(blockModules).forEach((module) => {
  if (typeof module.default === 'function') {
    module.default(Alpine);
  }
});

window.Alpine = Alpine;
Alpine.start();

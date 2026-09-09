import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';
import '../styles/app.scss';

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

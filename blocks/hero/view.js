export default function registerHero(Alpine) {
  Alpine.data('heroBlock', () => ({
    ready: false,
    shown: false,
    init() { this.ready = true; },
    reveal() { this.shown = true; },
  }));
}

export default function registerCards(Alpine) {
  Alpine.data('cardsBlock', () => ({
    ready: false,
    shown: false,
    init() { this.ready = true; },
    reveal() { this.shown = true; },
  }));
}

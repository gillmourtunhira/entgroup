export default function registerDoctors(Alpine) {
  Alpine.data('doctorsBlock', () => ({
    ready: false,
    shown: false,
    init() { this.ready = true; },
    reveal() { this.shown = true; },
  }));
}

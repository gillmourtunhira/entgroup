export default function registerHero(Alpine) {
  Alpine.data('heroBlock', (count = 1, autoplay = false) => ({
    active: 0,
    count,
    autoplay,
    paused: false,
    hovered: false,
    focused: false,
    reducedMotion: false,
    timer: null,
    motionQuery: null,
    motionListener: null,
    init() {
      this.motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
      this.reducedMotion = this.motionQuery.matches;
      this.motionListener = (event) => {
        this.reducedMotion = event.matches;
        this.sync();
      };
      this.motionQuery.addEventListener('change', this.motionListener);
      this.sync();
    },
    sync() {
      this.stop();
      if (!this.autoplay || this.count < 2 || this.paused || this.hovered
        || this.focused || this.reducedMotion || document.hidden) return;
      this.timer = window.setInterval(() => {
        this.active = (this.active + 1) % this.count;
      }, 6000);
    },
    stop() {
      if (this.timer !== null) window.clearInterval(this.timer);
      this.timer = null;
    },
    go(index) {
      this.active = (index + this.count) % this.count;
      this.sync();
    },
    togglePlayback() {
      this.paused = !this.paused;
      this.sync();
    },
    destroy() {
      this.stop();
      this.motionQuery?.removeEventListener('change', this.motionListener);
    },
  }));
}

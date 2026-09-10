export default function registerTestimonials(Alpine) {
  Alpine.data('testimonials', () => ({
    atStart: true,
    atEnd: true,
    hasOverflow: false,
    observer: null,
    init() {
      this.$nextTick(() => {
        const track = this.$refs.track;
        if (!track) return;
        this.sync();
        this.observer = new ResizeObserver(() => this.sync());
        this.observer.observe(track);
        for (const card of track.children) this.observer.observe(card);
      });
    },
    sync() {
      const track = this.$refs.track;
      if (!track) return;
      const max = Math.max(0, track.scrollWidth - track.clientWidth);
      this.hasOverflow = max > 2;
      this.atStart = track.scrollLeft <= 2;
      this.atEnd = track.scrollLeft >= max - 2;
    },
    scrollTo(left) {
      const track = this.$refs.track;
      if (!track) return;
      const max = Math.max(0, track.scrollWidth - track.clientWidth);
      track.scrollTo({
        left: Math.min(max, Math.max(0, left)),
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
      });
    },
    step(direction) {
      const track = this.$refs.track;
      if (!track?.firstElementChild) return;
      const width = track.firstElementChild.getBoundingClientRect().width;
      const gap = parseFloat(window.getComputedStyle(track).columnGap) || 0;
      this.scrollTo(track.scrollLeft + direction * (width + gap));
    },
    next() { this.step(1); },
    prev() { this.step(-1); },
    start() { this.scrollTo(0); },
    end() { this.scrollTo(this.$refs.track?.scrollWidth || 0); },
    destroy() { this.observer?.disconnect(); },
  }));
}

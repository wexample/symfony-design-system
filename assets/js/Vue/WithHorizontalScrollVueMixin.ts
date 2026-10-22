export default {
  mounted() {
    this._onHorizontalScrollWheel = (event: WheelEvent) => {
      if (event.ctrlKey && event.deltaY !== 0) {
        event.preventDefault();
        // Down the wheel is toward the end of the line, which scrolls to the
        // left in a page read from the right.
        const sign = getComputedStyle(this.$el).direction === 'rtl' ? -1 : 1;
        this.$el.scrollLeft += event.deltaY * sign;
      }
    };
    this.$el.addEventListener('wheel', this._onHorizontalScrollWheel, { passive: false });
  },

  beforeUnmount() {
    this.$el.removeEventListener('wheel', this._onHorizontalScrollWheel);
  },
};

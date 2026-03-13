export default {
  mounted() {
    this._onHorizontalScrollWheel = (event: WheelEvent) => {
      if (event.deltaY !== 0 && event.deltaX === 0) {
        event.preventDefault();
        this.$el.scrollLeft += event.deltaY;
      }
    };
    this.$el.addEventListener('wheel', this._onHorizontalScrollWheel, { passive: false });
  },

  beforeUnmount() {
    this.$el.removeEventListener('wheel', this._onHorizontalScrollWheel);
  },
};

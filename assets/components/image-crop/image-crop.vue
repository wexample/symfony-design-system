<script>
// What part of an image to keep — a portrait, a signature — chosen over the
// image itself. The area is said in the image's own pixels, whatever size it
// is shown at: that is what a server crops by, and what `crop()` cuts here
// when the cut is to be made before sending.
//
// `aspect` holds the area to a ratio (1 for a portrait); left null, the area
// is free, which a signature needs.
const CORNERS = ['nw', 'ne', 'sw', 'se'];

// How much of the image the area takes when it first appears.
const INITIAL_SHARE = 0.8;

// The smallest area, in pixels of the image as shown: a frame the pointer can
// still take hold of.
const MIN_SHOWN = 24;

// One press of an arrow, in pixels of the image as shown.
const KEYBOARD_STEP = 8;

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-image-crop-image-crop',

  emits: ['update:modelValue'],

  props: {
    // The image, as a url — one of the page, or `URL.createObjectURL()` of a
    // file just picked.
    src: {
      type: String,
      required: true
    },
    // Width over height the area keeps, or null for a free one.
    aspect: {
      type: Number,
      default: null
    },
    // Drawn as a circle — the area is still a square, which is what is cut.
    round: {
      type: Boolean,
      default: false
    },
    // The area to open on, in the image's pixels: { x, y, width, height }.
    modelValue: {
      type: Object,
      default: null
    }
  },

  data() {
    return {
      ready: false,
      // The image's own size, and the size it is shown at.
      natural: { width: 0, height: 0 },
      shown: { width: 0, height: 0 },
      // The area, in the image's pixels.
      area: { x: 0, y: 0, width: 0, height: 0 },
      drag: null,
      corners: CORNERS
    };
  },

  computed: {
    scale() {
      return this.natural.width ? this.shown.width / this.natural.width : 1;
    },

    areaStyle() {
      return {
        left: `${this.area.x * this.scale}px`,
        top: `${this.area.y * this.scale}px`,
        width: `${this.area.width * this.scale}px`,
        height: `${this.area.height * this.scale}px`
      };
    },

    valueText() {
      const { x, y, width, height } = this.rounded();

      return `${width} × ${height} @ ${x}, ${y}`;
    }
  },

  watch: {
    src() {
      this.ready = false;
    },

    aspect() {
      if (this.ready) {
        this.area = this.initialArea();
        this.emit();
      }
    }
  },

  mounted() {
    // Shown smaller or larger as the page changes: the area is kept in the
    // image's pixels, so only the scale it is drawn at moves.
    this.resizeObserver = new ResizeObserver(() => this.measure());
    this.resizeObserver.observe(this.$refs.stage);
  },

  beforeUnmount() {
    this.resizeObserver?.disconnect();
  },

  methods: {
    onLoad() {
      const image = this.$refs.image;
      this.natural = { width: image.naturalWidth, height: image.naturalHeight };
      this.measure();
      this.area = this.modelValue ? this.clampArea({ ...this.modelValue }) : this.initialArea();
      this.ready = true;
      this.emit();
    },

    measure() {
      const image = this.$refs.image;

      if (image) {
        this.shown = { width: image.clientWidth, height: image.clientHeight };
      }
    },

    // The largest area the ratio allows within the given share of the image,
    // centred.
    initialArea() {
      let width = this.natural.width * INITIAL_SHARE;
      let height = this.natural.height * INITIAL_SHARE;

      if (this.aspect) {
        if (width / height > this.aspect) {
          width = height * this.aspect;
        } else {
          height = width / this.aspect;
        }
      }

      return {
        x: (this.natural.width - width) / 2,
        y: (this.natural.height - height) / 2,
        width,
        height
      };
    },

    minSize() {
      return MIN_SHOWN / this.scale;
    },

    // Kept inside the image, and no smaller than a frame can be held by.
    clampArea(area) {
      const min = this.minSize();
      area.width = Math.min(Math.max(area.width, min), this.natural.width);
      area.height = Math.min(Math.max(area.height, min), this.natural.height);
      area.x = Math.min(Math.max(area.x, 0), this.natural.width - area.width);
      area.y = Math.min(Math.max(area.y, 0), this.natural.height - area.height);

      return area;
    },

    startDrag(event, mode) {
      if (event.button !== 0) {
        return;
      }

      event.preventDefault();
      event.currentTarget.setPointerCapture(event.pointerId);

      this.drag = {
        mode,
        target: event.currentTarget,
        pointerX: event.clientX,
        pointerY: event.clientY,
        area: { ...this.area }
      };

      this.drag.target.addEventListener('pointermove', this.onDragMove);
      this.drag.target.addEventListener('pointerup', this.onDragEnd);
      this.drag.target.addEventListener('pointercancel', this.onDragEnd);
    },

    onDragMove(event) {
      const { mode, pointerX, pointerY, area } = this.drag;
      const dx = (event.clientX - pointerX) / this.scale;
      const dy = (event.clientY - pointerY) / this.scale;

      if (mode === 'move') {
        this.area = this.clampArea({ ...area, x: area.x + dx, y: area.y + dy });
        return;
      }

      this.area = this.resized(area, mode, dx, dy);
    },

    onDragEnd() {
      const target = this.drag.target;
      target.removeEventListener('pointermove', this.onDragMove);
      target.removeEventListener('pointerup', this.onDragEnd);
      target.removeEventListener('pointercancel', this.onDragEnd);
      this.drag = null;
      this.emit();
    },

    // The area pulled by a corner, the opposite one holding still: the ratio
    // kept from the width, and the whole kept inside the image.
    resized(area, corner, dx, dy) {
      const west = corner.includes('w');
      const north = corner.includes('n');
      // The corner that does not move.
      const anchorX = west ? area.x + area.width : area.x;
      const anchorY = north ? area.y + area.height : area.y;
      // How far it may go: to the image's edges on the pulled sides.
      const roomX = west ? anchorX : this.natural.width - anchorX;
      const roomY = north ? anchorY : this.natural.height - anchorY;
      const min = this.minSize();

      let width = Math.min(Math.max(area.width + (west ? -dx : dx), min), roomX);
      let height = Math.min(Math.max(area.height + (north ? -dy : dy), min), roomY);

      if (this.aspect) {
        height = width / this.aspect;

        if (height > roomY) {
          height = roomY;
          width = height * this.aspect;
        }
      }

      return {
        x: west ? anchorX - width : anchorX,
        y: north ? anchorY - height : anchorY,
        width,
        height
      };
    },

    onKey(event) {
      const step = KEYBOARD_STEP / this.scale;
      const moves = {
        ArrowLeft: [-step, 0],
        ArrowRight: [step, 0],
        ArrowUp: [0, -step],
        ArrowDown: [0, step]
      };
      const move = moves[event.key];

      if (!move) {
        return;
      }

      event.preventDefault();
      const [dx, dy] = move;

      this.area = event.shiftKey
        ? this.resized({ ...this.area }, 'se', dx, dy)
        : this.clampArea({ ...this.area, x: this.area.x + dx, y: this.area.y + dy });
      this.emit();
    },

    rounded() {
      return {
        x: Math.round(this.area.x),
        y: Math.round(this.area.y),
        width: Math.round(this.area.width),
        height: Math.round(this.area.height)
      };
    },

    emit() {
      this.$emit('update:modelValue', this.rounded());
    },

    // The area cut out of the image, as a file: for a page sending the cut
    // rather than the original and where to cut it. `maxWidth` shrinks a
    // large cut; the type and quality are the browser's for `toBlob()`.
    crop({ type = 'image/png', quality = 0.92, maxWidth = null } = {}) {
      const { x, y, width, height } = this.rounded();
      const ratio = maxWidth && width > maxWidth ? maxWidth / width : 1;
      const canvas = document.createElement('canvas');
      canvas.width = Math.round(width * ratio);
      canvas.height = Math.round(height * ratio);
      canvas.getContext('2d').drawImage(this.$refs.image, x, y, width, height, 0, 0, canvas.width, canvas.height);

      return new Promise((resolve) => canvas.toBlob(resolve, type, quality));
    }
  }
};
</script>

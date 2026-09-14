<script>
import IconService from '@wexample/symfony-loader/js/Services/IconService';

// The twin of partials/status-icon.html.twig: same types, same glyphs. A running
// state carries no glyph — the arc is what it has to say.
export const STATUS_ICON_GLYPHS = {
  success: 'ph:bold/check',
  error: 'ph:bold/x',
  warning: 'ph:bold/warning',
  running: null,
  pending: 'ph:bold/clock',
  paused: 'ph:bold/pause',
  play: 'ph:bold/play',
  skipped: 'ph:bold/caret-double-right',
  disabled: 'ph:bold/minus'
};

export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-status-icon',

  props: {
    // One of the names above. Anything else draws a bare ring, which is what an
    // unknown state honestly looks like.
    type: {
      type: String,
      default: 'pending'
    },
    // How much of the ring is drawn, 0 to 1. Null leaves it whole. Changing it
    // moves the arc rather than redrawing it, which is the whole point of the
    // component being here rather than in twig.
    progress: {
      type: Number,
      default: null
    },
    // Overrides what the type would put inside the ring.
    glyph: {
      type: String,
      default: null
    },
    size: {
      type: String,
      default: null
    },
    // Given one the circle is read out, without one it is decoration beside
    // something that already says the state.
    label: {
      type: String,
      default: null
    }
  },

  computed: {
    classes() {
      return [
        'status-icon',
        `status-icon--${this.type}`,
        this.size ? `status-icon--${this.size}` : null
      ];
    },

    style() {
      return this.progress === null
        ? null
        : { '--status-icon-progress': this.progress };
    },

    glyphName() {
      return this.glyph ?? STATUS_ICON_GLYPHS[this.type] ?? null;
    },

    glyphHtml() {
      return this.glyphName
        ? this.app.getServiceOrFail(IconService).icon(this.glyphName)
        : null;
    }
  }
};
</script>

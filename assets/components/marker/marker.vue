<script>
import IconService from '@wexample/symfony-loader/js/Services/IconService';
import { STATUS_ICON_GLYPHS } from '../status-icon/status-icon.vue';

// The twin of components/marker/marker.html.twig: same tones, same glyphs,
// same markup. The glyph map is the circle's, so the two forms of a state
// never drift apart — a running state has none there, and is named here, the
// capsule having no arc to draw it with.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-marker-marker',

  props: {
    // A state (`success`, `running`…), the neutral one for a name or a
    // version, or a category (`cat-mint`). The tone is the whole of what tells
    // one marker from another.
    tone: {
      type: String,
      default: 'neutral'
    },
    // In words.
    label: {
      type: String,
      default: null
    },
    // How many of it there are, which qualifies what the marker says rather
    // than being it.
    count: {
      default: null
    },
    // Read by the dozen, in a cell: gives up the room it takes standing alone.
    compact: {
      type: Boolean,
      default: false
    },
    // Overrides what a state would put inside, and the only way a tone with
    // no glyph of its own gets one.
    glyph: {
      type: String,
      default: null
    },
    // Said out loud where the glyph is all there is.
    title: {
      type: String,
      default: null
    }
  },

  computed: {
    classes() {
      return [
        'marker',
        `marker--${this.tone}`,
        this.compact ? 'marker--compact' : null
      ];
    },

    glyphName() {
      return this.glyph
        ?? STATUS_ICON_GLYPHS[this.tone]
        ?? (this.tone === 'running' ? 'ph:bold/circle-notch' : null);
    },

    glyphHtml() {
      return this.glyphName
        ? this.app.getServiceOrFail(IconService).icon(this.glyphName)
        : null;
    },

    hasLabel() {
      return this.label !== null && this.label !== '';
    },

    hasCount() {
      return this.count !== null && this.count !== '' && this.count !== undefined;
    }
  }
};
</script>

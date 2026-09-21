<script>
import IconService from '@wexample/symfony-loader/js/Services/IconService';
import { STATUS_ICON_GLYPHS } from '../status-icon/status-icon.vue';

// The twin of components/status/status.html.twig: same types, same glyphs, same
// markup. The glyph map is the circle's, so the two forms of a state never
// drift apart — a running state has none there, and is named here, the capsule
// having no arc to draw it with.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-status-status',

  props: {
    type: {
      type: String,
      default: 'info'
    },
    // What the state is, in words.
    label: {
      type: String,
      default: null
    },
    // How many of it there are: it qualifies the state rather than being it,
    // so it is drawn a step back.
    count: {
      default: null
    },
    // Read by the dozen, in a cell: gives up the room it takes standing alone.
    compact: {
      type: Boolean,
      default: false
    },
    // Overrides what the type would put inside.
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
        'status',
        `status--${this.type}`,
        this.compact ? 'status--compact' : null
      ];
    },

    glyphName() {
      return this.glyph
        ?? STATUS_ICON_GLYPHS[this.type]
        ?? (this.type === 'running' ? 'ph:bold/circle-notch' : null);
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

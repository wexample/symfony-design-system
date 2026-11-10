<script>
import Avatar from '../avatar/avatar.vue';
import Marker from '../marker/marker.vue';

const DOMAIN = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.people.';

// The twin of person-list.html.twig: the people of something, a row each —
// face and presence, name, role —, what is done to them at the row's end, the
// way to add one more under the list. It says what was asked (`remove`);
// its caller answers.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-person-list-person-list',

  components: {
    Avatar,
    DsMarker: Marker
  },

  emits: ['remove'],

  props: {
    // [{ id, label, src, emoji, presence, role, roleTone }].
    items: {
      type: Array,
      default: () => []
    },
    // A bin at each row's end, `remove` asked when pressed.
    removable: {
      type: Boolean,
      default: false
    },
    empty: {
      type: String,
      default: null
    }
  },

  computed: {
    emptyText() {
      return this.empty ?? this.trans(`${DOMAIN}empty`);
    }
  },

  methods: {
    removeText(item) {
      return this.trans(`${DOMAIN}remove`, { '%name%': item.label ?? '' });
    }
  }
};
</script>

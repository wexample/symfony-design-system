<script>
import { avatarGroupSplit, avatarInitials } from '../../js/Helper/AvatarGroupHelper';

// The twin of avatar-group.html.twig: the people of something as a pile of
// small faces, the rest counted as one more. A face is its picture (`src` —
// `avatar_url()` handed by the server for a drawn one) or its label's
// initials.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-avatar-group-avatar-group',

  props: {
    // [{ label, src? }]
    items: {
      type: Array,
      default: () => []
    },
    max: {
      type: Number,
      default: 4
    },
    size: {
      type: String,
      default: null
    },
    label: {
      type: String,
      default: null
    }
  },

  computed: {
    group() {
      return avatarGroupSplit(this.items, this.max);
    }
  },

  methods: {
    initials(label) {
      return avatarInitials(label);
    }
  }
};
</script>

<script>

import WithCssClassesVueMixin from "../../js/Vue/WithCssClassesVueMixin";

export default {
  mixins: [
    WithCssClassesVueMixin
  ],
  props: {
    entity: {
      type: Object,
      required: true
    }
  },
}

</script>

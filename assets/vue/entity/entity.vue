<script>

import WithCssClasses from "../../js/Vue/WithCssClasses";

export default {
  mixins: [
    WithCssClasses
  ],
  props: {
    entity: {
      type: Object,
      required: true
    }
  },
}

</script>

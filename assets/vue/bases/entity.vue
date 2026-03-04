<script>

import VueWrapper from "./vue-wrapper";
import WithCssClassesVueMixin from "../../js/Vue/WithCssClassesVueMixin";

export default {
  extends: VueWrapper,

  mixins: [
    WithCssClassesVueMixin,
    AbstractEntitySingleMixin
  ]
}

</script>

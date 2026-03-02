<script>

import Entity from "../bases/entity.vue";

export default {
  extends: Entity,
  methods: {
    getWrapperCssClassDeclarations() {
      return [
        'entity--display--bar'
      ];
    }
  }
}

</script>

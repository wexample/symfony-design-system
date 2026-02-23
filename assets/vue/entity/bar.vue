<script>

import Entity from "./entity.vue";

export default {
  extends: Entity,
  methods: {
    getWrapperCssClassDeclarations() {
      return [
        'entity',
        'entity--display--bar'
      ];
    }
  }
}

</script>

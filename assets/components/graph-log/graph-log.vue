<script>
import DateDisplay from '../date-display/date-display.vue';
import Marker from '../marker/marker.vue';
import { graphLogColumns, graphLogLayout, graphLogSvg } from '@wexample/js-graph-log/Helper/GraphLog';
import { graphLogLaneColor } from '../../js/Helper/GraphLogHelper';

// The twin of graph-log.html.twig: the same rows, the same lanes, drawn from
// the items as they change — a history growing at its head as commits come in.
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-components-graph-log-graph-log',

  components: {
    DateDisplay,
    // Registered as `capsule`: `marker` is an svg element, which vue refuses
    // as a component id.
    Capsule: Marker
  },

  props: {
    // [{ id, parents, title, href, refs: [{ label, tone }], code, meta, date }]
    items: {
      type: Array,
      default: () => []
    }
  },

  computed: {
    rows() {
      return graphLogLayout(this.items);
    },

    columns() {
      return graphLogColumns(this.rows);
    }
  },

  methods: {
    svg(index) {
      return graphLogSvg(this.rows[index], this.columns, graphLogLaneColor);
    }
  }
};
</script>

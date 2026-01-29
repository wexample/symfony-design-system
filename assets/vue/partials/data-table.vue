<script>
export default {
  template: '#vue-template-wexample-symfony-design-system-bundle-vue-partials-data-table',

  props: {
    rows: {
      type: Array,
      required: true,
      default: () => []
    },
    columns: {
      type: Array,
      default: () => []
    },
    app: {
      type: Object,
      default: null
    },
    showHeader: {
      type: Boolean,
      default: false
    }
  },

  methods: {
    hasCellActions(column) {
      return Boolean(column?.action || (Array.isArray(column?.actions) && column.actions.length));
    },

    getCellActions(row, column) {
      if (!this.app) {
        return [];
      }

      const actions = column?.actions
        ? (Array.isArray(column.actions) ? column.actions : [column.actions])
        : (column?.action ? [column.action] : []);

      if (!actions.length) {
        return [];
      }

      const iconService = this.app.getServiceOrFail('icon');
      const routingService = this.app.getServiceOrFail('routing');
      const defaultIcons = {
        show: 'ph:bold/eye',
        edit: 'ph:bold/pencil-simple',
      };

      return actions.map((action) => {
        const actionName = typeof action === 'string'
          ? action
          : (action?.name || action?.action);

        const iconName = typeof action === 'object'
          ? (action.icon || defaultIcons[actionName])
          : defaultIcons[actionName];

        const route = typeof action === 'object'
          ? action.route
          : undefined;

        const routeName = route || (column?.routePrefix && actionName
          ? `${column.routePrefix}_${actionName}`
          : undefined);

        const params = typeof action === 'object'
          ? action.params
          : column?.params;

        const parameters = typeof params === 'function'
          ? params(row, column, action)
          : (params ?? {});

        const href = routeName
          ? routingService.path(routeName, parameters)
          : '';

        return {
          href,
          icon: iconName ? iconService.icon(iconName) : '',
        };
      }).filter((entry) => entry.icon);
    },

    getCellIcon(row, column) {
      if (!column?.icon || !this.app) {
        return '';
      }

      const icon = typeof column.icon === 'function'
        ? column.icon(row, column)
        : column.icon;

      if (!icon) {
        return '';
      }

      const iconService = this.app.getServiceOrFail('icon');
      return iconService.icon(icon);
    },

    getColumnKey(column, index) {
      if (typeof column === 'string') {
        return column;
      }

      if (column?.key) {
        return column.key;
      }

      if (column?.action) {
        return `action-${column.action}`;
      }

      if (Array.isArray(column?.actions)) {
        return `actions-${column.actions.join('-')}`;
      }

      return `column-${index}`;
    },

    getColumnLabel(column) {
      if (typeof column === 'string') {
        return this.trans(`@vue::table.column.${column}.title`);
      }

      if (column?.label === false) {
        return '';
      }

      if (column?.label !== undefined && column?.label !== null) {
        return column.label;
      }

      if (column?.key) {
        return this.trans(`@vue::table.column.${column.key}.title`);
      }

      return column?.key ?? '';
    },

    getCellValue(row, column) {
      const key = this.getColumnKey(column);
      if (!row || !key) {
        return '';
      }

      if (key.includes('.')) {
        return key.split('.').reduce((value, part) => {
          if (value === null || value === undefined) {
            return '';
          }
          return value[part];
        }, row) ?? '';
      }

      const value = row[key] ?? '';

      if (typeof column?.format === 'function') {
        return column.format(value, row, column);
      }

      return value;
    },

    getCellHref(row, column) {
      const href = column?.href;
      if (!href) {
        return '';
      }

      if (typeof href === 'function') {
        return href(row, column);
      }

      if (typeof href === 'string') {
        return href;
      }

      if (typeof href === 'object' && href.route && this.app) {
        const parameters =
          typeof href.parameters === 'function'
            ? href.parameters(row, column)
            : href.parameters ?? {};

        const routingService = this.app.getServiceOrFail('routing');
        return routingService.path(href.route, parameters);
      }

      return '';
    },

    isHtmlCell(column) {
      return column?.html === true || column?.cell === 'html';
    }
  }
};
</script>

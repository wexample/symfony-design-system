<script>
import AbstractEntityTable from "../../table/abstract-entity-table/abstract-entity-table.vue";
import { numberFormat, numberFormatMoney } from "../../../../js/Helper/NumberHelper";
import {
  gridPasteRows,
  gridTableGuess,
  gridTableRead,
  gridTextToValue,
  gridValueToText
} from "../../../../js/Helper/GridHelper";

const T = 'WexampleSymfonyDesignSystemBundle.common.system::frontend.grid.';

// An entity table whose cells are written in place, through the entity's
// repository like everything else of the collection: a cell left changed
// updates its entity with that field alone, a row typed at the foot is
// created, a row removed is deleted. Rows also come many at once, from cells
// pasted out of a spreadsheet or a file read into a table: the reader names
// what each column holds, sees what will be read, and they are created in one
// request (`postEntities`).
//
// A column is written in when it has an `editor` — `text`, `number`, `money`,
// `select`, `checkbox` — (see GridHelper for `scale`, `currency`, `options`,
// `required`), and shows a figure worked out of the row when it has a
// `compute(values)`, the editor then only saying how it reads. `default` is
// what a new row starts with.
//
// A grid says what it is the collection of (`getEntityClass`), what a new row
// belongs to (`getNewEntityData`, its relations by id), and tells its parent
// each change saved (`changed`): what the rows add up to is the parent's.
export default {
  extends: AbstractEntityTable,

  template: "#vue-template-wexample-symfony-design-system-bundle-components-collection-grid-abstract-entity-grid-abstract-entity-grid",

  emits: ['changed'],

  data() {
    return {
      // One request for all the rows: a grid is read and written whole.
      pageLength: null,
      // The currency money columns are held in, unless a column names its own.
      currency: 'EUR',
      locale: null,
      creatable: true,
      deletable: true,
      // A table pasted or read from a file, turned into new rows.
      importable: true,
      // The row typed at the foot: each field's text as typed.
      draft: {},
      // What could not be read, by cell (`<row key>:<column>`), as typed.
      invalid: {},
      // '', `saving`, `saved` or `error`, said under the grid.
      saveState: '',
      saveMessage: '',
      // A table waiting for its columns (GridTable).
      table: null
    };
  },

  computed: {
    editorColumns() {
      return this.columns.filter((column) => column.editor && !column.compute);
    },

    // The columns a table's cells may feed.
    importColumns() {
      return this.editorColumns.filter((column) => column.importable !== false && column.editor !== 'checkbox');
    },

    // The row being typed, read as far as it can be: what its computed cells
    // show before it is sent.
    draftValues() {
      const values = {};

      for (const column of this.editorColumns) {
        const read = gridTextToValue(this.draft[column.key], this.resolveColumn(column), this.resolvedLocale);

        values[column.key] = read ? read.value : null;
      }

      return values;
    },

    tableRecords() {
      return this.table ? gridTableRead(this.table, this.importColumns.map((column) => this.resolveColumn(column)), this.resolvedLocale) : [];
    },

    tableReady() {
      return this.tableRecords.length > 0 && this.tableRecords.every((record) => record.invalid.length === 0);
    },

    resolvedLocale() {
      return this.locale || this.app?.layout?.vars?.locale || null;
    }
  },

  created() {
    this.resetDraft();
  },

  methods: {
    t(key, parameters = {}) {
      return this.trans(`${T}${key}`, parameters);
    },

    // What a new row belongs to, its relations by id: { invoice: id }.
    getNewEntityData() {
      return {};
    },

    // The tables of a file, rows of cell texts, for a grid that can read files:
    // the reading is the server's, which knows the formats.
    async readTableFile(file) {
      return null;
    },

    canReadTableFiles() {
      return false;
    },

    processColumns(rawColumns) {
      const columns = AbstractEntityTable.methods.processColumns.call(this, rawColumns).map((column) => (column.compute ? {
        ...column,
        format: (value, row) => this.computedText(row, column)
      } : column));

      if (!this.deletable) {
        return columns;
      }

      return [...columns, {
        key: 'grid-delete',
        label: false,
        width: 'xs',
        actions: [{ icon: 'ph:bold/trash', label: this.t('delete'), handler: (row) => this.deleteRow(row) }]
      }];
    },

    // A column as GridHelper reads it, its currency resolved.
    resolveColumn(column, row = null) {
      const currency = typeof column.currency === 'function' ? column.currency(row) : column.currency;

      return { ...column, currency: currency || this.currency };
    },

    cellSlot(column) {
      return `cell:${column.key}`;
    },

    cellId(row, column) {
      return `${row ? this.getEntityKey(row) : 'new'}:${column.key}`;
    },

    cellText(row, column) {
      const id = this.cellId(row, column);

      if (id in this.invalid) {
        return this.invalid[id];
      }

      return row
        ? gridValueToText(row[column.key], this.resolveColumn(column, row), this.resolvedLocale)
        : (this.draft[column.key] ?? '');
    },

    cellChecked(row, column) {
      return row ? Boolean(row[column.key]) : Boolean(this.draft[column.key]);
    },

    isCellInvalid(row, column) {
      return this.cellId(row, column) in this.invalid;
    },

    isFigureEditor(column) {
      return column.editor === 'number' || column.editor === 'money';
    },

    // A computed cell: worked out of the row's values, or of the row being typed.
    computedText(row, column) {
      const value = column.compute(row ?? this.draftValues);

      if (value === null || value === undefined || Number.isNaN(value)) {
        return '';
      }

      if (column.editor === 'money') {
        return numberFormatMoney(value, this.resolveColumn(column, row).currency, this.resolvedLocale);
      }

      if (column.editor === 'number') {
        return numberFormat(value / (column.scale ?? 1), column.digits ?? 2, this.resolvedLocale);
      }

      return String(value);
    },

    resetDraft() {
      this.draft = Object.fromEntries(this.editorColumns.map((column) => {
        const value = typeof column.default === 'function' ? column.default() : column.default;

        return [column.key, column.editor === 'checkbox'
          ? Boolean(value)
          : gridValueToText(value, this.resolveColumn(column), this.resolvedLocale)];
      }));

      for (const id of Object.keys(this.invalid).filter((key) => key.startsWith('new:'))) {
        delete this.invalid[id];
      }
    },

    // A cell of a saved row, left changed: read, then saved alone. What cannot
    // be read stays as typed, marked, and is not sent.
    async onCellChange(row, column, typed) {
      const id = this.cellId(row, column);
      const read = gridTextToValue(typed, this.resolveColumn(column, row), this.resolvedLocale);

      if (!read || (column.required && read.value === null)) {
        this.invalid[id] = typed;

        return;
      }

      delete this.invalid[id];

      if (read.value === row[column.key]) {
        return;
      }

      const EntityClass = this.getEntityClass();
      const saved = await this.saving(() => this.getEntityRepository().postEntityById({
        endpoint: 'update',
        id: row.id,
        entity: new EntityClass({ [column.key]: read.value })
      }));

      if (saved) {
        row.absorb(saved);
        this.$emit('changed');
      }
    },

    onDraftInput(column, typed) {
      this.draft[column.key] = typed;
      delete this.invalid[this.cellId(null, column)];
    },

    // Enter moves down the column, as a sheet does, and creates the row typed
    // at the foot; Escape gives the cell back what it held. Tab is the
    // browser's own: along the row, then to the next.
    onCellKeydown(row, column, event) {
      if (event.key === 'Escape') {
        event.preventDefault();

        if (row) {
          delete this.invalid[this.cellId(row, column)];
          event.target.value = this.cellText(row, column);
        } else {
          this.resetDraft();
        }

        event.target.blur();

        return;
      }

      if (event.key !== 'Enter' || event.shiftKey || event.isComposing) {
        return;
      }

      event.preventDefault();

      if (!row) {
        this.createDraft();

        return;
      }

      const cells = [...this.$el.querySelectorAll(`[data-grid-column="${column.key}"]`)];
      const next = cells[cells.indexOf(event.target) + 1];

      // Moving away is what saves it: the cell's change fires as it is left.
      if (next) {
        next.focus();
      } else {
        event.target.blur();
      }
    },

    focusDraft(key = null) {
      this.$nextTick(() => {
        const wanted = key ?? this.editorColumns[0]?.key;

        this.$el.querySelector(`[data-grid-new] [data-grid-column="${wanted}"]`)?.focus();
      });
    },

    async createDraft() {
      const values = {};
      let firstInvalid = null;

      for (const column of this.editorColumns) {
        const typed = this.draft[column.key];
        const read = column.editor === 'checkbox'
          ? { value: Boolean(typed) }
          : gridTextToValue(typed, this.resolveColumn(column), this.resolvedLocale);

        if (!read || (column.required && read.value === null)) {
          this.invalid[this.cellId(null, column)] = typed ?? '';
          firstInvalid = firstInvalid ?? column.key;
        } else if (read.value !== null) {
          values[column.key] = read.value;
        }
      }

      if (firstInvalid) {
        this.focusDraft(firstInvalid);

        return;
      }

      const EntityClass = this.getEntityClass();
      const created = await this.saving(() => this.getEntityRepository().createEntity(
        new EntityClass({ ...this.getNewEntityData(), ...values })
      ));

      if (!created) {
        return;
      }

      this.entities = [...this.entities, created];
      this.resetDraft();
      this.focusDraft();
      this.$emit('changed');
    },

    async deleteRow(row) {
      const deleted = await this.saving(() => this.getEntityRepository().deleteEntity({ identifier: row.id }));

      if (deleted) {
        this.entities = this.entities.filter((entity) => entity !== row);
        this.$emit('changed');
      }
    },

    // A request that writes, said under the grid while it runs and once it is
    // answered; what the server refused, in its words.
    async saving(request) {
      this.saveState = 'saving';
      this.saveMessage = '';

      try {
        const result = await request();
        this.saveState = 'saved';

        return result ?? true;
      } catch (error) {
        this.saveState = 'error';
        this.saveMessage = await this.errorMessage(error);

        return null;
      }
    },

    async errorMessage(error) {
      try {
        const body = await error?.response?.clone().json();

        return body?.message || error.message;
      } catch {
        return error?.message ?? '';
      }
    },

    saveStateText() {
      if (this.saveState === 'error') {
        return this.saveMessage || this.t('error');
      }

      return this.saveState ? this.t(this.saveState) : '';
    },

    // Cells out of a spreadsheet, pasted in the row typed at the foot: a table
    // to map rather than a text.
    onDraftPaste(event) {
      const rows = this.importable ? gridPasteRows(event.clipboardData?.getData('text/plain') ?? '') : null;

      if (rows) {
        event.preventDefault();
        this.openTable(rows);
      }
    },

    pickTableFile() {
      this.$refs.tableFile?.click();
    },

    async onTableFile(event) {
      const file = event.target.files?.[0];
      event.target.value = '';

      if (!file) {
        return;
      }

      const tables = await this.saving(() => this.readTableFile(file));
      const rows = Array.isArray(tables) ? tables.find((candidate) => candidate.length) : null;

      if (rows) {
        this.saveState = '';
        this.openTable(rows);
      } else if (tables) {
        this.saveState = 'error';
        this.saveMessage = this.t('import.no_table');
      }
    },

    openTable(rows) {
      this.table = gridTableGuess(rows, this.importColumns.map((column) => this.resolveColumn(column)), this.resolvedLocale);
    },

    isTableCellInvalid(rowIndex, columnIndex) {
      const key = this.table.columns[columnIndex];
      const record = this.tableRecords.find((candidate) => candidate.row === rowIndex);

      return Boolean(key && record?.invalid.includes(key));
    },

    // What no column feeds but a row needs: said once, above the table.
    tableMissing() {
      return this.importColumns
        .filter((column) => column.required && !this.table.columns.includes(column.key))
        .map((column) => column.label)
        .join(', ');
    },

    async importTable() {
      if (!this.tableReady) {
        return;
      }

      const EntityClass = this.getEntityClass();
      const created = await this.saving(() => this.getEntityRepository().postEntities({
        endpoint: 'import',
        entities: this.tableRecords.map((record) => new EntityClass({ ...this.getNewEntityData(), ...record.values }))
      }));

      if (created) {
        this.entities = [...this.entities, ...created];
        this.table = null;
        this.$emit('changed');
      }
    }
  }
};
</script>

type CssClassFlagMap = Record<string, boolean>;
type CssClassTuple = [string, boolean];
type CssClassFactory = () => CssClassDeclaration;
type CssClassDeclaration =
  | null
  | undefined
  | false
  | string
  | CssClassFlagMap
  | CssClassTuple
  | CssClassDeclaration[]
  | CssClassFactory;

function isPlainObject(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null && !Array.isArray(value);
}

function isCssClassTuple(value: unknown): value is CssClassTuple {
  if (!Array.isArray(value) || value.length !== 2) {
    return false;
  }

  return typeof value[0] === 'string' && typeof value[1] === 'boolean';
}

export default {
  computed: {
    wrapperCssClasses(): CssClassFlagMap {
      return this.buildWrapperClasses();
    }
  },

  methods: {
    getWrapperCssClassDeclarations(): CssClassDeclaration {
      return [];
    },

    buildWrapperClasses(): CssClassFlagMap {
      const declarations = this.getWrapperCssClassDeclarations();

      if (Array.isArray(declarations) && !isCssClassTuple(declarations)) {
        return this.buildCssClasses(...declarations);
      }

      return this.buildCssClasses(declarations);
    },

    buildCssClasses(...declarations: CssClassDeclaration[]): CssClassFlagMap {
      const classes: CssClassFlagMap = {};

      for (const declaration of declarations) {
        this.appendCssClassDeclaration(classes, declaration);
      }

      return classes;
    },

    appendCssClassDeclaration(
      classes: CssClassFlagMap,
      declaration: CssClassDeclaration
    ): void {
      if (declaration === null || declaration === undefined || declaration === false) {
        return;
      }

      if (typeof declaration === 'function') {
        this.appendCssClassDeclaration(classes, declaration());
        return;
      }

      if (typeof declaration === 'string') {
        if (declaration.trim() === '') {
          throw new Error('CSS class declaration cannot be an empty string.');
        }

        classes[declaration] = true;
        return;
      }

      if (Array.isArray(declaration)) {
        if (isCssClassTuple(declaration)) {
          const [name, enabled] = declaration;
          if (name.trim() === '') {
            throw new Error('CSS class tuple name cannot be empty.');
          }
          classes[name] = enabled;
          return;
        }

        for (const nestedDeclaration of declaration) {
          this.appendCssClassDeclaration(classes, nestedDeclaration as CssClassDeclaration);
        }

        return;
      }

      if (isPlainObject(declaration)) {
        for (const [name, enabled] of Object.entries(declaration)) {
          if (name.trim() === '') {
            throw new Error('CSS class object key cannot be empty.');
          }

          if (typeof enabled !== 'boolean') {
            throw new Error(`CSS class "${name}" must have a boolean value.`);
          }

          classes[name] = enabled;
        }

        return;
      }

      throw new Error(`Unsupported CSS class declaration type: ${typeof declaration}.`);
    },
  }
};

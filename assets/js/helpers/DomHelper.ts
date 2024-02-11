export class Attribute {
  public static HREF: string = 'href';

  public static ID: string = 'id';

  public static REL: string = 'rel';

  public static SRC: string = 'src';
}

export class AttributeValue {
  public static STYLESHEET: string = 'stylesheet';
}

export class InsertPosition {
  public static BEFORE_END: string = 'beforeend';
}

export class TagName {
  public static A: string = 'a';

  public static DIV: string = 'div';

  public static LINK: string = 'link';

  public static SCRIPT: string = 'script';
}

export function findPreviousNode(el) {
  // Search for previous non text node.
  do {
    el = el.previousSibling;
  } while (el && el.nodeType === Node.TEXT_NODE);
  return el;
}

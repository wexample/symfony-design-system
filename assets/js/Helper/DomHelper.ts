const NATURALLY_FOCUSABLE_TAGS: string[] = [
  'a',
  'button',
  'input',
  'select',
  'textarea',
  'summary'
];

const NON_TEXT_INPUT_TYPES: string[] = [
  'checkbox',
  'radio',
  'button',
  'submit'
];

export const isElement = (value: unknown): value is Element => {
  return value instanceof Element;
};

export const isHtmlElement = (value: unknown): value is HTMLElement => {
  return value instanceof HTMLElement;
};

export const getEventTargetElement = (event: KeyboardEvent | FocusEvent): Element | null => {
  return isElement(event.target) ? event.target : null;
};

export const isNaturallyFocusable = (element: Element): boolean => {
  const tagName = element.tagName.toLowerCase();
  return NATURALLY_FOCUSABLE_TAGS.includes(tagName);
};

export const isTextInputElement = (element: Element | null): boolean => {
  if (!isHtmlElement(element)) {
    return false;
  }

  const tagName = element.tagName.toLowerCase();
  if (tagName === 'textarea') {
    return true;
  }

  if (tagName === 'input') {
    const type = (element.getAttribute('type') || 'text').toLowerCase();
    return !NON_TEXT_INPUT_TYPES.includes(type);
  }

  return element.isContentEditable;
};

export const isSameElement = (
  currentElement: Element | null,
  expectedElement: Element | null
): boolean => {
  if (!currentElement || !expectedElement) {
    return false;
  }

  return currentElement === expectedElement;
};

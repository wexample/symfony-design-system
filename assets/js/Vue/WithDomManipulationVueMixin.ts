import {
  getEventTargetElement,
  isElement,
  isHtmlElement,
  isNaturallyFocusable,
  isSameElement,
  isTextInputElement
} from "../Helper/DomHelper";

export default {
  methods: {
    domIsElement(value: unknown): value is Element {
      return isElement(value);
    },

    domIsHtmlElement(value: unknown): value is HTMLElement {
      return isHtmlElement(value);
    },

    domGetEventTargetElement(event: KeyboardEvent | FocusEvent): Element | null {
      return getEventTargetElement(event);
    },

    domIsNaturallyFocusable(element: Element): boolean {
      return isNaturallyFocusable(element);
    },

    domIsTextInputElement(element: Element | null): boolean {
      return isTextInputElement(element);
    },

    domIsSameElement(currentElement: Element | null, expectedElement: Element | null): boolean {
      return isSameElement(currentElement, expectedElement);
    },
  }
};

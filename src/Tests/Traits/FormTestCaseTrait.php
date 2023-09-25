<?php

namespace Wexample\SymfonyDesignSystem\Tests\Traits;

use DOMElement;
use JsonException;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\DomCrawler\Form;
use Wexample\SymfonyHelpers\Helper\ClassHelper;
use Wexample\SymfonyHelpers\Helper\FormHelper;
use Wexample\SymfonyHelpers\Helper\VariableHelper;
use Wexample\SymfonyTesting\Traits\Application\HtmlDocumentTestCaseTrait;
use Wexample\SymfonyTesting\Traits\SessionTestCaseTrait;
use function class_exists;
use function is_array;
use function is_object;
use function json_decode;

trait FormTestCaseTrait
{
    use HtmlDocumentTestCaseTrait;
    use SessionTestCaseTrait;

    public function formFieldsValues(
        array $fields,
        Form|string $form,
        $submit = false
    ): array {
        if (is_string($form)) {
            $form = $this->formFind($form);
        }

        // Fill fields.
        foreach ($fields as $fieldName => $fieldValue) {
            $this->fieldSetValue(
                $fieldName,
                $fieldValue,
                $form
            );
        }

        if ($submit) {
            return $this->formSubmit($form);
        }

        return [];
    }

    public function formFind(string $formName): Form
    {
        $formName = $this->buildFormName($formName);

        $form = $this->getCurrentCrawler()->filter(
            'form[name="'.$formName.'"]'
        );
        $this->log(
            'Selected form '.$formName.' ('.$form->count().')'
        );
        $this->assertTrue(
            1 === $form->count(),
            '  There is '.$form->count().' form called '.$formName
        );

        return $form->form();
    }

    protected function buildFormName(string $formClassName): string
    {
        return class_exists($formClassName)
            ? ClassHelper::getTableizedName($formClassName)
            : $formClassName;
    }

    public function fieldSetValue(
        string $fieldName,
        mixed $value,
        Form $form
    ): void {
        $nodes = $this->formGetNode($fieldName, $form);
        $form->setValues([
            $this->buildFormFieldName($fieldName, $form) => $value,
        ]);

        // If we got multiple nodes then we have probably radio buttons or checkboxes
        if (is_array($nodes)) {
            $this->log(
                '  > Set multiple field value : '.$fieldName.' ('.$nodes[0]->tagName.') = '.$value
            );

            // TODO Add the checkboxes management
            foreach ($nodes as $node) {
                // for radio buttons
                if ($node->getAttribute('value') === (string) $value) {
                    $node->setAttribute('checked', 'checked');
                } else {
                    $node->removeAttribute('checked');
                }
            }
        } else {
            // Only one node ...
            if (!is_object($nodes)) {
                $this->error(
                    '  > Set field value : '.
                    $this->buildFormFieldName(
                        $fieldName,
                        $form
                    ).
                    ' not found'
                );
            } else {
                $this->log(
                    '  > Set field value : '.$fieldName.' ('.$nodes->tagName.') = '.$value
                );
            }

            $fieldType = $this->getFieldType($nodes);

            switch ($fieldType) {
                case 'checkbox':
                    if ($value) {
                        $nodes->setAttribute('checked', 'checked');
                    } else {
                        $nodes->setAttribute('checked', false);
                    }

                    break;
                case VariableHelper::EMAIL:
                case 'hidden':
                case 'number':
                case 'tel':
                case 'text':
                case 'url':
                    $this->formInputSetValue(
                        $fieldName,
                        (string) $value,
                        $form
                    );
                    break;
                case 'textarea':
                    $this->textareaSetValue(
                        $fieldName,
                        (string) $value,
                        $form
                    );
                    break;
                case 'select':
                    $this->selectSetSelectedValue(
                        '#'.$this->buildFormFieldId($fieldName, $form),
                        $value
                    );
                    break;
                default:
                    $this->error('Field not found '.$fieldName);
            }
        }
    }

    public function formGetNode(
        string $fieldName,
        Form $form
    ): array|DOMElement|null {
        $longSelector = $this->formFieldSelector($fieldName, $form);
        $nodes = $this->findOne('#'.$longSelector);

        // We don't have retrieved a correct form input, maybe we have to look inside the retrieved tag
        // (ex: in the new contest form, we have radio buttons or checkboxes that are in a wrapper div...
        // and the node contains that div)
        if ($nodes && 'div' === $nodes->tagName) {
            $nodes = (array) $this->crawler->filter(
                '#'.$longSelector.' input[id^='.$longSelector.']'
            )->getIterator();
        }

        return $nodes;
    }

    public function formFieldSelector(
        $fieldName,
        Form $form
    ): string {
        return $this->formGetName($form).'_'.$fieldName;
    }

    public function formGetName(Form $form): string
    {
        return $form->getNode()->getAttribute('name');
    }

    protected function buildFormFieldName(
        string $fieldName,
        Form $form
    ): string {
        return $this->formGetName($form).'['.$fieldName.']';
    }

    protected function getFieldType(DOMElement $fieldNode): string
    {
        if ('select' === $fieldNode->tagName) {
            return 'select';
        }

        if ('textarea' === $fieldNode->tagName) {
            return 'textarea';
        }

        return $fieldNode->getAttribute('type');
    }

    public function formInputSetValue(
        string $fieldShortName,
        $value,
        Form $form
    ): void {
        $this->formGetNode($fieldShortName, $form)
            ->setAttribute(
                'value',
                $value
            );
    }

    public function textareaSetValue(
        string $selector,
        $value,
        Form $form
    ): void {
        $this->formGetNodeOrFail($selector, $form)->textContent = $value;
    }

    public function formGetNodeOrFail(
        string $fieldName,
        Form $form
    ): array|DOMElement|null {
        $nodes = $this->formGetNode($fieldName, $form);

        if (!$nodes) {
            $this->error('Form node not found : '.$fieldName);
        }

        return $nodes;
    }

    public function selectSetSelectedValue(
        string $selectSelector,
        string $valueSelected
    ): void {
        $this->selectDeselect($selectSelector);

        $options = $this->selectGetOptionNodes($selectSelector);

        /** @var DOMElement $option */
        foreach ($options as $option) {
            if ($option->getAttribute('value') === $valueSelected) {
                $option->setAttribute(
                    'selected',
                    'selected'
                );
            }
        }
    }

    public function selectDeselect(string $selectSelector): void
    {
        $options = $this->selectGetOptionNodes($selectSelector);

        foreach ($options as $optionNode) {
            $optionNode->removeAttribute('selected');
        }
    }

    public function selectGetOptionNodes(string $selectSelector): array
    {
        $this->assertNodeExists(
            $selectSelector
        );

        $select = $this->getCurrentCrawler()->filter(
            $selectSelector.' option'
        );
        $length = $select->count();
        $options = [];

        for ($i = 0; $i < $length; ++$i) {
            $options[] = $select
                ->getNode($i);
        }

        return $options;
    }

    protected function buildFormFieldId(
        string $fieldName,
        Form $form
    ): string {
        return $this->formGetName($form).'_'.$fieldName;
    }

    public function formSubmit(
        Form $form,
        string $clickedButton = null,
        bool $shouldSucceed = true
    ): array {
        $formName = $this->formGetName($form);
        $this->log('Submitting form '.$formName);

        $isAjax = $this->nodeHasClass($form->getNode(), 'form-ajax');
        $errors = [];

        if ($isAjax) {
            $formName = $this->formGetName($form);
            $fieldsValues = $form->getPhpValues();

            foreach ($fieldsValues[$formName] as $fieldName => $originalValue) {
                $fieldsValues[$formName][$fieldName] = $this->formFieldGetNodeValue(
                    $fieldName,
                    $form
                );
            }

            $this->createGlobalClientWithSameSession();

            if ($clickedButton) {
                // Add clicked button field.
                $fieldsValues[$formName][$clickedButton] = true;
            }

            // Symfony 4 polyfill.
            // Symfony 5 will support headers on submit method.
            $this->client->request(
                $form->getMethod(),
                $form->getUri(),
                $fieldsValues,
                $form->getPhpFiles(),
                [
                    'HTTP_X-Requested-With' => 'XMLHttpRequest',
                ]
            );

            $this->log(
                'AJAX submit to  '.$this->client->getRequest()->getPathInfo()
            );

            $jsonData = $this->client->getResponse()->getContent();
            try {
                $json = json_decode(
                    $jsonData,
                    JSON_OBJECT_AS_ARRAY,
                    512,
                    JSON_THROW_ON_ERROR
                );
            } catch (JsonException $e) {
                $this->debugWrite($jsonData);
                $this->error('Bad form JSON response format : '.$e->getMessage());
            }

            if (isset($json['forms'][$formName]['errors'])) {
                $formErrors = $json['forms'][$formName]['errors'];

                // Field errors.
                foreach ($formErrors['fields'] as $fieldName => $fieldErrors) {
                    foreach ($fieldErrors as $fieldError) {
                        $errors[$fieldName][] = 'Form field error in '.$fieldName.' : '.$fieldError;
                    }
                }

                // Constraints error.
                foreach ($formErrors['constraints'] as $propertyPath => $propertyErrors) {
                    foreach ($propertyErrors as $errorMessage) {
                        $errors[$propertyPath][] = 'Form constraints error on "'
                            .$propertyPath.'" : '
                            .$errorMessage;
                    }
                }

                $formError = $formErrors['form'];
                if ($formError) {
                    $errors['form'][] = 'Form global error in '.$formName.' : '.$formError;
                }
            }
        } else {

            // Normal submit.
            $this->crawler = $this->client->submit($form);

            $this->log(
                'Normal submit to  '.$this->client->getRequest()
                    ->getPathInfo()
            );

            // Check errors.
            $formErrors = $this->find('.message-error');

            /** @var DOMElement $error */
            foreach ($formErrors as $error) {
                $errors['form'][] = $error->textContent;
            }
        }

        if ($shouldSucceed) {
            $this->assertStatusCodeIsNotError(
                'Form submission succeed.'
            );

            if ($countErrors = count($errors)) {
                foreach ($errors as $errorsGroup) {
                    foreach ($errorsGroup as $message) {
                        $this->error(
                            $message,
                            false
                        );
                    }
                }

                $this->error('Form has '.$countErrors.' errors');
            }
        }

        return $errors;
    }

    public function formFieldGetNodeValue(
        string $fieldName,
        Form $form
    ) {
        $document = $form->getNode()->ownerDocument;

        $fieldId = $this->buildFormFieldId(
            $fieldName,
            $form
        );

        $this->assertNodeExists('#'.$fieldId);

        $fieldNode = $document->getElementById(
            $fieldId
        );

        $fieldType = $this->getFieldType($fieldNode);

        switch ($fieldType) {
            case 'checkbox':
                return 'checked' === $fieldNode->getAttribute('checked');
            case VariableHelper::EMAIL:
            case 'hidden':
            case 'number':
            case 'tel':
            case 'text':
            case 'url':
                return $fieldNode->getAttribute('value');
            case 'select':
                return $this->selectGetSelectedValue('#'.$fieldId);
            case 'textarea':
                return $fieldNode->textContent;
            default:
                $this->error('Unable to get value of field type "'.$fieldType.'" for field #'.$fieldId);

                return null;
        }
    }

    protected function selectGetSelectedValue(string $selectSelector): ?string
    {
        $options = $this->selectGetOptionNodes($selectSelector);

        /** @var DOMElement $optionNode */
        foreach ($options as $optionNode) {
            if ('selected' === $optionNode->getAttribute('selected')) {
                return $optionNode->getAttribute('value');
            }
        }

        return null;
    }

    public function goToFormRoute(
        $formName,
        $args = [],
        $parameters = []
    ): Form {
        $this->goToRoute(
            FormHelper::buildRoute($formName),
            $args,
            $parameters
        );

        return $this->formFind(
            $this->buildFormName(
                $formName
            )
        );
    }

    public function selectSelectByPosition(
        string $selectSelector,
        int $position
    ): void {
        $select = $this
            ->crawler
            ->filter($selectSelector.' option');
        $this->selectDeselect($selectSelector);
        $node = $select
            ->getNode($position);
        $node?->setAttribute(
            'selected',
            'selected'
        );
    }

    public function selectInjectOption(
        string $selectSelector,
        $optionValue,
        Form $form,
        $selected = 'selected'
    ): void {
        $select = $this->formGetNode($selectSelector, $form);
        $option = $select->ownerDocument->createElement('option');
        $option->setAttribute(
            'value',
            $optionValue
        );
        $option->setAttribute(
            'selected',
            $selected
        );
        $select->appendChild($option);
    }

    public function formAddFile(
        $fieldName,
        $path,
        Form $form
    ) {
        $this
            ->getField($fieldName, $form)
            ->setValue($this->fileUploadPrepare($path));
    }

    public function getField(
        string $fieldName,
        Form $form
    ): FormField {
        return
            $form
                ->get(
                    $this->buildFormFieldName($fieldName, $form)
                );
    }
}

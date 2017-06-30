### How to use wkbe_page_properties module

Usecase: give editors ability to create fieldable entities and place them anywhere on page based on visibility rules.

1. Install module, it will create "wkbe_page_properties" entity type.
2. Add "field_pages" text field to entity (can be multivalue). This can be used to limit properties entity to specific pages.
3. Create block using code provided below.
4. Place block globally, in preprocessing, or in panels etc.
5. Create properties entities as needed (use field_pages and other visibility rules).

Code to get properties entity from context:

```
$context = \Drupal::getContainer()->get('wkbe_page_properties.context');
  return $context->getActiveEntity();
```
  
Example code to output properties entity:

```
if ($context_entity->field_intro_text) {
  return [
    '#theme' => 'azg_intro_text',
    '#intro_text' => check_markup($context_entity->field_intro_text->value, $context_entity->field_intro_text->format),
  ];
}
```
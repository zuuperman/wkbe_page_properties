<?php
/**
 * @file
 * Contains \Drupal\wkbe_page_properties\PagePropertiesContext
 */

namespace Drupal\wkbe_page_properties;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\content_translation\ContentTranslationManager;
use Drupal\Core\Condition\ConditionAccessResolverTrait;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\language\Entity\ContentLanguageSettings;

/**
 * Context service to provide the correct page properties for current page.
 */
class PagePropertiesContext {

  use ConditionAccessResolverTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The plugin context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * The context manager service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

  /**
   * @var RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * @var LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * @var PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * @var ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs the PagePropertiesContext instance.
   *
   * @param EntityTypeManagerInterface $entity_type_manager
   * @param LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The ContextHandler for applying contexts to conditions properly.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $context_repository
   *   The lazy context repository service.
   * @param RouteMatchInterface $route_match
   * @param ModuleHandlerInterface $module_handler
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, ContextHandlerInterface $context_handler, 
                              ContextRepositoryInterface $context_repository, RouteMatchInterface $route_match, PathMatcherInterface $path_matcher, ModuleHandlerInterface $module_handler) {
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->contextHandler = $context_handler;
    $this->contextRepository = $context_repository;
    $this->routeMatch = $route_match;
    $this->pathMatcher = $path_matcher;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Get the entity that is currently active.
   */
  public function getActiveEntity() {
    $query = \Drupal::entityQuery('page_properties_entity');

    // If translation is enabled, add language conditions.
    $langcode = NULL;
    if ($this->moduleHandler->moduleExists('content_translation')) {

      if (\Drupal::getContainer()->get('content_translation.manager')->isEnabled('page_properties_entity')) {
        $language = $this->languageManager->getCurrentLanguage();
        $langcode = $language->getId();
        $query->condition('langcode', $langcode);
      }
    }

    // $query->condition('status', 1); publishing status disabled for now.
    $pages_condition = $query->orConditionGroup()
      ->condition('field_pages.value', $this->routeMatch->getRouteObject()->getPath());

    $args = explode('/', $this->routeMatch->getRouteObject()->getPath());
    // Remove first and last arg.
    array_pop($args);
    array_shift($args);

    // Check for every arg, if the path/* or path* exists. (for example /user/*)
    $path_to_check = '';
    foreach ($args as $arg) {
      $path_to_check .= '/' . $arg;
      $pages_condition->condition('field_pages.value', $path_to_check . '*');
      $pages_condition->condition('field_pages.value', $path_to_check . '/*');
    }

    // Check front page
    if ($this->pathMatcher->isFrontPage()) {
      $pages_condition->condition('field_pages.value', $path_to_check . '<front>');
    }

    $query->condition($pages_condition);

    $entity_ids = $query->execute();
    $matching_entities = $this->entityTypeManager->getStorage('page_properties_entity')->loadMultiple($entity_ids);
    foreach ($matching_entities as $entity) {
      if ($this->entityMatchesCurrentRequest($entity)) {
        return empty($langcode) ? $entity : $entity->getTranslation($langcode);
      }
    }

    return NULL;
  }

  /**
   * Check if the given entity matches to the current request.
   * @param PagePropertiesEntityInterface $entity
   * @return bool
   */
  private function entityMatchesCurrentRequest(PagePropertiesEntityInterface $entity) {
    $conditions = [];
    foreach ($entity->getVisibilityConditions() as $condition_id => $condition) {
      if ($condition instanceof ContextAwarePluginInterface) {
        try {
          $contexts = $this->contextRepository->getRuntimeContexts(array_values($condition->getContextMapping()));
          $this->contextHandler->applyContextMapping($condition, $contexts);
        }
        // One of the required contexts did not match current entity.
        catch (ContextException $e) {
          return FALSE;
        }
      }
      $conditions[$condition_id] = $condition;
    }


    // Check if all conditions match.
    if ($this->resolveConditions($conditions, 'and') !== FALSE) {
      return TRUE;
    }
  }

}
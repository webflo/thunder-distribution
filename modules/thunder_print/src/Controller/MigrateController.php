<?php

namespace Drupal\thunder_print\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\replicate\Replicator;
use Drupal\workspace\Entity\WorkspaceInterface;
use Drupal\workspace\WorkspaceManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Clones a node.
 */
class MigrateController extends ControllerBase {

  protected $workspaceManager;

  protected $replicator;

  protected $request;

  /**
   * MigrateController constructor.
   *
   * @param \Drupal\workspace\WorkspaceManagerInterface $workspaceManager
   *   The workspace manager.
   * @param \Drupal\replicate\Replicator $replicator
   *   The replicator.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(WorkspaceManagerInterface $workspaceManager, Replicator $replicator, RequestStack $requestStack) {
    $this->workspaceManager = $workspaceManager;
    $this->replicator = $replicator;
    $this->request = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('workspace.manager'),
      $container->get('replicate.replicator'),
      $container->get('request_stack')
    );
  }

  /**
   * The controller call.
   */
  public function migrate(ContentEntityInterface $node) {

    $source = $this->workspaceManager->load('live');
    $target = $this->workspaceManager->load('print');

    // Before saving set the active workspace to the target.
    $this->workspaceManager->setActiveWorkspace($target);

    $clone = $this->replicator->replicateEntity($node);

    $clone->referenced_print_entity->target_id = $node->id();
    $clone->save();

    // Back to source workspace to set the referenced entity.
    $this->workspaceManager->setActiveWorkspace($source);

    $node->referenced_print_entity->target_id = $clone->id();
    $node->save();

    return $this->switchAndRedirect($clone->id(), $target);

  }

  /**
   * Switch to a node on different workspace.
   */
  public function switchWorkspace(ContentEntityInterface $node) {

    $active_workspace = $this->workspaceManager->getActiveWorkspace();
    $target = ($active_workspace == 'print') ? 'live' : 'print';

    if (!$node->referenced_print_entity->isEmpty()) {
      $target = $this->workspaceManager->load($target);
      return $this->switchAndRedirect($node->referenced_print_entity->target_id, $target);
    }
  }

  /**
   * Switch workspace and redirect.
   */
  private function switchAndRedirect($nodeId, WorkspaceInterface $workspace) {

    // Remove destination parameter.
    $query = $this->request->query;
    if ($query->has('destination')) {
      $query->remove('destination');
    }

    $this->workspaceManager->setActiveWorkspace($workspace);
    return $this->redirect('entity.node.edit_form', ['node' => $nodeId]);
  }

}

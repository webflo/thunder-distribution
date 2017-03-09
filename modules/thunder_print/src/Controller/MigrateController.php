<?php

namespace Drupal\thunder_print\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\replicate\Replicator;
use Drupal\workspace\WorkspaceManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Clones a node.
 */
class MigrateController extends ControllerBase {

  protected $workspaceManager;

  protected $replicator;

  /**
   * MigrateController constructor.
   *
   * @param \Drupal\workspace\WorkspaceManagerInterface $workspaceManager
   *   The workspace manager.
   * @param \Drupal\replicate\Replicator $replicator
   *   The replicator.
   */
  public function __construct(WorkspaceManagerInterface $workspaceManager, Replicator $replicator) {
    $this->workspaceManager = $workspaceManager;
    $this->replicator = $replicator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('workspace.manager'),
      $container->get('replicate.replicator')
    );
  }

  /**
   * The controller call.
   */
  public function migrate(ContentEntityInterface $node) {

    $target = $this->workspaceManager->load('print');

    // Before saving set the active workspace to the target.
    $this->workspaceManager->setActiveWorkspace($target);

    $clone = $this->replicator->replicateEntity($node);
    $clone->save();

    return $this->redirect('entity.node.edit_form', ['node' => $clone->id()]);
  }

}

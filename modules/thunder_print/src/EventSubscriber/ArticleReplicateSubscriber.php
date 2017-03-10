<?php

namespace Drupal\thunder_print\EventSubscriber;

use Drupal\replicate\Events\ReplicateEntityFieldEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ArticleReplicateSubscriber.
 */
class ArticleReplicateSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['replicate__entity_field__text_with_summary'][] = array('onBodyFieldReplication', 0);
    return $events;
  }

  /**
   * Remove stuff.
   */
  public function onBodyFieldReplication(ReplicateEntityFieldEvent $event) {

    if ($event->getEntity()->getEntityTypeId() == 'node' && $event->getEntity()->bundle() == 'article' && $event->getFieldItemList()->getName() == 'body') {

    }

  }

}

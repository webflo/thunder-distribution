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
    $events['replicate__entity_field__entity_reference_revisions'][] = array('onParagraphsFieldReplication', 0);
    return $events;
  }

  /**
   * Remove stuff.
   */
  public function onParagraphsFieldReplication(ReplicateEntityFieldEvent $event) {

    $whitelist = ['text', 'quote', 'image'];

    if ($event->getEntity()->getEntityTypeId() == 'node' && $event->getEntity()->bundle() == 'article' && $event->getFieldItemList()->getName() == 'field_paragraphs') {

      $event->getFieldItemList()->filter(function ($item) use ($whitelist) {
        return $item->get('entity')->getTarget() && in_array($item->get('entity')->getTarget()->getValue()->bundle(), $whitelist);
      });
    }

  }

}

<?php

namespace Drupal\commerce_cart_skip\Controller;

use Drupal\commerce_cart_skip\Entity\CommerceCartSkipRule;
use Drupal\commerce_cart_skip\Entity\CommerceCartSkipRuleInterface;
use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Provides a listing of Example.
 */
class CommerceCartSkipPurchasedController extends ControllerBase {

    function purchased(CommerceCartSkipRuleInterface $commerce_cart_skip_rule, OrderInterface $commerce_order) {

        $build = [];

        $text = $commerce_cart_skip_rule->get('ui')['message_bought'] ?? null;
        $link = $commerce_order->getCustomer() ? Url::fromRoute('entity.commerce_order.user_view', ['user' => $commerce_order->getCustomer()->id(), 'commerce_order' => $commerce_order->id()]) : null;
        $commerce_orderId = $link ? '<a href="'.$link->toString().'">'.$commerce_order->id().'</a>' : $commerce_order->id();

        $build['confirmation'] = [
            '#markup' => t(($text ? '<p>'.$text.'</p>': '') . '<p>Your order ID is '.$commerce_orderId.'.</p>')
        ];

        return $build;
    }
}
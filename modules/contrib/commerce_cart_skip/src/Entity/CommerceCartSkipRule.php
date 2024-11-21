<?php

namespace Drupal\commerce_cart_skip\Entity;

use Drupal\commerce_cart_skip\Entity\CommerceCartSkipRuleInterface;
use Drupal\commerce_product\Entity\ProductVariationType;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\example\ExampleInterface;

/**
 * Defines the CommerceCartSkipRule entity.
 *
 * @ConfigEntityType(
 *   id = "commerce_cart_skip_rule",
 *   label = @Translation("Commerce cart skip rule"),
 *   handlers = {
 *     "list_builder" = "Drupal\commerce_cart_skip\Controller\CommerceCartSkipRuleListBuilder",
 *     "form" = {
 *       "add" = "Drupal\commerce_cart_skip\Form\CommerceCartSkipRuleForm",
 *       "edit" = "Drupal\commerce_cart_skip\Form\CommerceCartSkipRuleForm",
 *       "delete" = "Drupal\commerce_cart_skip\Form\CommerceCartSkipRuleDeleteForm",
 *     }
 *   },
 *   config_prefix = "commerce_cart_skip_rule",
 *   admin_permission = "administer commerce cart skip rules",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "enabled",
 *     "allow_cancel",
 *     "conditions",
 *     "order_values",
 *     "ui",
 *   },
 *   links = {
 *     "edit-form" = "/admin/commerce/config/products/commerce_cart_skip/{example}",
 *     "delete-form" = "/admin/commerce/config/products/commerce_cart_skip/{example}/delete",
 *   }
 * )
 */
class CommerceCartSkipRule extends ConfigEntityBase implements CommerceCartSkipRuleInterface
{

  /**
   * The ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The label.
   *
   * @var string
   */
  protected $label;

  /**
   * @var boolean
   */
  protected $enabled;

  /**
   * @var boolean
   */
  protected $allow_cancel;

  /**
   * @var array
   */
  protected $conditions;

  /**
   * @var array
   */
  protected $order_values;

  /**
   * @var array
   */
  protected $ui;

  /**
   * {@inheritdoc}
   */
  public function getPrice()
  {
    if ($this->get('price') && !$this->get('price')->isEmpty()) {
      return $this->get('price')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCommerceProductVariationType()
  {
    if ($this->get('commerce_product_variation_type') && !$this->get('commerce_product_variation_type')->isEmpty()) {
      return ProductVariationType::load($this->commerce_product_variation_type);
    }
  }
}
